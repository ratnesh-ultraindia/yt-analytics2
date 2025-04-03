<?php

namespace App\Http\Controllers;

use Google\Client;


use Google\Exception;
use Illuminate\Http\Request;
use Google\Service\YouTubeAnalytics;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class Analytics extends Controller
{


    function channel($channelId)
    {
        
        $dateEnd = date("Y-m-d", strtotime("-3 days"));
        $dateStart = date("Y-m-d", strtotime("$dateEnd -30 days"));
        $dateStartDiscrete = date("Y-m-d", strtotime("$dateEnd -90 days"));


        $clientSecretPath = storage_path('app/google/creds.json');
        $tokenPath = storage_path('app/google/token.json');

        $client = new Client();
        $client->setAuthConfig($clientSecretPath);
        $client->addScope([
            'https://www.googleapis.com/auth/youtube.readonly',
            'https://www.googleapis.com/auth/youtubepartner',
            'https://www.googleapis.com/auth/youtube',
            'https://www.googleapis.com/auth/yt-analytics-monetary.readonly'
        ]);

        // Check if the token file exists
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);

            // Check if the access token is expired
            if ($client->isAccessTokenExpired()) {
                // Check if a refresh token exists
                if (isset($accessToken['refresh_token'])) {
                    // Use the refresh token to fetch a new access token
                    $client->fetchAccessTokenWithRefreshToken($accessToken['refresh_token']);
                    $newAccessToken = $client->getAccessToken();

                    // Ensure the refresh token is saved along with the new access token
                    if (!isset($newAccessToken['refresh_token'])) {
                        $newAccessToken['refresh_token'] = $accessToken['refresh_token'];
                    }

                    // Save the updated access token (including refresh token) to the file
                    file_put_contents($tokenPath, json_encode($newAccessToken));
                } else {
                    // If there's no refresh token, return the authorization URL to the client
                    return response()->json([
                        "status" => "unauthorized",
                        "redirect_url" => $client->createAuthUrl()
                    ]);
                }
            }
        } else {
            // If no token is found, return the authorization URL to the client
            return response()->json([
                "status" => "unauthorized",
                "redirect_url" => $client->createAuthUrl()
            ]);
        }

        $youtubeAnalytics = new YouTubeAnalytics($client);
        $allChannelData = [];

        // echo "https://www.googleapis.com/youtube/v3/channels?id=" . $channelId . "&part=snippet&key=" . getenv('YT_API_KEY');

        // Fetch the channel data from the YouTube Data API
        $yDataJson = file_get_contents("https://www.googleapis.com/youtube/v3/channels?id=" . $channelId . "&part=snippet&key=" . getenv('YT_API_KEY'));
        $ytDataObj = json_decode($yDataJson, true);

        if (!isset($ytDataObj["items"][0])) {
            return response()->json([
                "status" => "failure",
                "message" => "Channel not found."
            ]);
        }

        $channelTitle = $ytDataObj["items"][0]["snippet"]["title"];

        try {
            // Fetch the analytics data from the YouTube Analytics API
            $reportResponseSum = $youtubeAnalytics->reports->query([
                'ids' => 'contentOwner==' . getenv("CONTENT_OWNER_ID"),
                'filters' => 'channel==' . $channelId,
                'startDate' => $dateStart,
                'endDate' => $dateEnd,
                'metrics' => 'views,estimatedMinutesWatched,estimatedRevenue'
            ]);
            
            $startDateDiscrete= date("Y-m-d",strtotime("-90 day"));

            $endDateDiscrete = date("Y-m-d",strtotime("-3 days"));
            


            $reportResponseDiscrete = $youtubeAnalytics->reports->query([
                'ids' => 'contentOwner==' . getenv("CONTENT_OWNER_ID"),
                'filters' => 'channel==' . $channelId,
                'startDate' => $startDateDiscrete,
                "dimensions" => "day",
                'endDate' => $endDateDiscrete,
                'metrics' => 'views,estimatedMinutesWatched,estimatedRevenue'
            ]);


            $discreteReportObj = [];


            if (!empty($reportResponseDiscrete->getRows())) {
                foreach ($reportResponseDiscrete->getRows() as $row) {
                    $dateParts = explode("-",$row[0]);
                    $year = $dateParts[0];
                    $month = $dateParts[1];
                    $day = $dateParts[2];

                    $views = $row[1];
                    $watchTime = $row[2];
                    $estimatedRevenue = $row[3];

                    // Calculate RPM
                    $rpm = ($views > 0) ? ($estimatedRevenue / $views) * 1000 : 0;
                    $hours = intdiv($watchTime, 60) . ':' . ($watchTime % 60);

                    $discreteReportObj[] = [
                        "day" => $day,
                        "month" => $month,
                        "year" => $year,
                        "value" => round($estimatedRevenue,2)
                    ];
                }
            }

            if (!empty($reportResponseSum->getRows())) {
                foreach ($reportResponseSum->getRows() as $row) {
                    $views = $row[0];
                    $watchTime = $row[1];
                    $estimatedRevenue = $row[2];

                    // Calculate RPM
                    $rpm = ($views > 0) ? ($estimatedRevenue / $views) * 1000 : 0;
                    $hours = intdiv($watchTime, 60) . ':' . ($watchTime % 60);

                    $channelData = [
                        "title" => $channelTitle,
                        "views" => $views,
                        "watchTime" => $hours,
                        "revenue" => round($estimatedRevenue, 2)
                    ];

                    $allChannelData[] = $channelData;
                }
            } else {
                return response()->json([
                    "status" => "failure",
                    "message" => "No data available for the specified parameters."
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                "status" => "failure",
                "message" => 'An error occurred: ' . $e->getMessage()
            ]);
        }

        return json_encode([
            "status" => "success",
            "data" => $allChannelData,
            "entity" => "channel",
            "discreteReportObj" => $discreteReportObj
        ]);
    }

    
}
