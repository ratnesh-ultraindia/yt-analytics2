<main class="page-content vh-100 grey-bg" id="dashboard" style="padding: 2em 0;">

    <div class="container">

        <img src="{{$channel_data["thumbnail"]}}">

        <h4>Get Data for {{$channel_data["title"]}}</h4>

        <form action="{{ url('get-channel-metrics') }}" class="analyticsForm" method="post">
            @csrf
            <div class="row mb-5">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="w-100" id="channelIdField">
                        <label for="" class="form-label">Channel Ids / Id separated by commas</label>
                        <input
                            type="text"
                            class="form-control"
                            name="channelId"
                            id="channelId" required />
                    </div>
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label for="" class="form-label">From Date</label>
                    <input
                        type="date"
                        class="form-control"
                        name="channelFromDate"
                        id="channelFromDate" required />
                </div>
                <div class="col-md-3 mb-3 mb-md-0">
                    <label for="" class="form-label">To Date</label>
                    <input
                        type="date"
                        class="form-control"
                        name="channelToDate"
                        id="channelToDate" required />
                </div>
                <div class="col-md-2 mb-3 mb-md-0">
                    <label for="" class="form-label w-100 d-md-block d-none">&nbsp;</label>
                    <button class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>
</main>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>


<script>
    // Function to populate the HTML with the response data
    function populateVideoData(response) {
        if (Array.isArray(response.data)) {
            response.data.forEach((video, index) => {
                // Create HTML elements for each video
                const videoContainer = document.createElement('div');
                videoContainer.className = 'video-data-container';
                videoContainer.innerHTML = `
                <h4 id="videoDataTitle-${index}">${video.title}</h4>
                <div class="row">
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h2 class="analytics-figure" id="viewsFigure-${index}">${video.views}</h2>
                                <p class="metric-title mb-0 small"><strong>Views</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h2 class="analytics-figure" id="watchTimeFigure-${index}">${video.watchTime}</h2>
                                <p class="metric-title mb-0 small"><strong>WatchTime (hours)</strong></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="card-body text-center">
                                <h2 class="analytics-figure" id="rpmFigure-${index}">${video.rpm}</h2>
                                <p class="metric-title mb-0 small"><strong>RPM</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                // Append the video data to a container in your HTML (e.g., a div with id="videoDataContainer")
                document.getElementById('videoDataContainer').appendChild(videoContainer);
            });
        } else {
            console.error('Error: Invalid response data');
        }
    }

    $(".analyticsForm").submit(function(e) {
        e.preventDefault();
        $("#HeadTitle").removeClass("d-none");
        $("#loader").removeClass("d-none");
        $("div#videoDataContainer").html();

        let action = $(this).attr("action");
        let method = "POST";
        let data = $(this).serialize();

        $.ajax({
            type: method,
            url: action,
            data: data,
            success: function(response) {
                console.log(response);
                let responseObj = JSON.parse(response);
                if (responseObj.status == "success") {
                    $("#loader").addClass("d-none");
                    $("#HeadTitle").removeClass("d-none");
                    $("#channel_growth").removeClass("d-none");
                    $("div#videoDataContainer").html("");
                    populateVideoData(responseObj);
                    if(responseObj.entity=="channel"){
                        if($("div#channel_growth").hasClass("d-none")){
                            $("div#channel_growth").removeClass("d-none");
                        }
                        drawChart(responseObj.discreteReportObj);
                    }else{
                        $("div#channel_growth").addClass("d-none");
                    }


                } else if (responseObj.status == "unauthorized") {
                    console.log(responseObj);
                    $("#loader").addClass("d-none");
                    $("#HeadTitle").addClass("d-none");

                } else {
                    // console.log(responseObj.message);
                    $("p#errorText").html(responseObj.message);
                    $("#loader").addClass("d-none");
                    $("#HeadTitle").addClass("d-none");
                    $("#channel_growth").addClass("d-none");
                }
            }
        });

    });
</script>


<script>
    google.charts.load('current', {
        'packages': ['corechart']
    });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart(discreteReportObj) {

        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Day');
        data.addColumn('number', 'RPM');

        // console.log(typeof(discreteReportObj));
        // console.log(discreteReportObj);
        // discreteReportObj.forEach(item => {
        //     console.log(`Day: ${item.day}, Month: ${item.month}, Year: ${item.year}, Value: ${item.value}`);
        // });

        const chartData = discreteReportObj.map(item => {
            return [`${item.day}/${item.month}`, item.value];
        });

        console.log(chartData);
        data.addRows(chartData);

        var options = {
            legend: 'none',
            width: '100%',
            height: '500',
            chartArea: {                
                width:'100%',
                left: 0,
                top:0
            },
            hAxis: {
                gridlines: {
                count: '31',
                color: '#000'
                },
                format: 'D/M/Y'
            },
            vAxis: {
                textPosition: 'none',
                gridlines: {
                    color: '#cfcfcf'
                },
                minValue: 0
            },
        };


        var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
        chart.draw(data, options);

        var button = document.getElementById('change');

        // button.onclick = function () {

        //   // If the format option matches, change it to the new option,
        //   // if not, reset it to the original format.
        //   options.hAxis.format === 'M/d/yy' ?
        //   options.hAxis.format = 'MMM dd, yyyy' :
        //   options.hAxis.format = 'M/d/yy';

        //   chart.draw(data, options);
        // };
    }
</script>
<style>
    .video-data-container h4 {
        font-size: 18px;
        margin-bottom: 15px;
        color: #000;
    }

    .video-data-container {
        margin-bottom: 30px;
    }

    #chart_div {
        /* pointer-events: none; */
    }
/* 
    .google-visualization-tooltip g+g {
        display: none !important;
    } */
</style>