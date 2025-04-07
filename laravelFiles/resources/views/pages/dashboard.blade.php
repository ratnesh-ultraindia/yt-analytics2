<main style="padding: 3em 0;">
    <div class="container">
        <div id="yt-groups">
            @php
                $dateEnd = date("Y-m-d", strtotime("-3 days"));
                $dateStart = date("Y-m-d", strtotime("$dateEnd -30 days"));
            @endphp
            <h2>From {{$dateStart}} to {{$dateEnd}}</h2>
            
            <h2 style="margin: 1em 0;">YouTube Groups</h2>
            <p class="text-danger" id="youtubeGroupsError"></p>
            <select class="form-select" id="youtube_exec_group" name="youtube_exec_group">
                <option value="" selected>Select a Group</option>
                @foreach ($groups as $group)
                    <option value="{{$group->id}}">{{$group->group_name}}'s Group</option>
                @endforeach
            </select>
        </div>

        <div class="container-fluid d-none" id="yt-group-members">
            <h2 style="margin: 1em 0;">Group Members</h2>
            <p class="text-danger" id="youtubeGroups"></p>
            <div class="d-flex align-items-start">
                <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical"></div>
                <div class="tab-content" id="v-pills-tabContent">
                    <h4>Select a group member to view their analytics</h4>
                </div>
            </div>
        </div>
    </div>
</main>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    google.charts.load('current', {'packages':['corechart']});

    function drawChart(data, chartId) {
        if (data.length <= 1) {
            document.getElementById(chartId).innerHTML = "<p>No revenue data available</p>";
            return;
        }

        let dataTable = google.visualization.arrayToDataTable(data);
        let options = {
            title: "Revenue Trend",
            curveType: "function",
            legend: { position: "bottom" }
        };

        let chart = new google.visualization.LineChart(document.getElementById(chartId));
        chart.draw(dataTable, options);
    }

    $(document).ready(function () {
        $("#youtube_exec_group").on("change", function () {
            let groupId = $(this).val();
            $("#youtubeGroupsError").text("");

            if (!groupId) {
                $("#youtubeGroupsError").text("Select a group to proceed.");
                return;
            }

            $.ajax({
                type: "GET",
                url: `{{url('get-members-for-group')}}/${groupId}`,
                success: function (response) {
                    let responseObj = JSON.parse(response);
                    
                    if (!responseObj.success) {
                        $("#youtubeGroupsError").text("Group data not found");
                        return;
                    }

                    let members = responseObj.members;
                    let tabsHtml = "";
                    let contentHtml = "<h4>Select a group member to view their analytics</h4>";
                    
                    members.forEach((member, index) => {
                        let activeClass = index === 0 ? "active" : "";
                        tabsHtml += `<button class="nav-link fetch-member-channels-data ${activeClass}" 
                            memberId="${member.id}" id="v-pills-${member.id}-tab" 
                            data-bs-toggle="pill" data-bs-target="#v-pills-${member.id}" 
                            type="button" role="tab" aria-controls="v-pills-${member.id}" 
                            aria-selected="${index === 0 ? "true" : "false"}">${member.incharge}</button>`;
                    });

                    $("#v-pills-tab").html(tabsHtml);
                    $("#yt-group-members").removeClass("d-none");
                },
                error: function () {
                    $("#youtubeGroupsError").text("Failed to fetch members. API is not responding try again.");
                }
            });
        });

        $(document).on('click', '.fetch-member-channels-data', function () {
            $("#v-pills-tabContent").html("<h4>Loading...</h4>");

            let memberId = $(this).attr('memberId');
            $.ajax({
                type: "GET",
                url: `{{url('get-channels-for-member')}}/${memberId}`,
                success: function (response) {
                    let responseObj = JSON.parse(response);
                    let channelDataHtml = "";

                    Object.entries(responseObj).forEach(([channelId, channelData], index) => {
                        console.log(channelData);
                        let name = channelData.data[0]?.title || "Unknown Channel";
                        let revenueData = [["Date", "Revenue"]];

                        let views = channelData.data[0]?.views || 0;
                        let watchTime = channelData.data[0]?.watchTime || 0;
                        let revenue = channelData.data[0]?.revenue || 0;

                        console.log({ "views": views, "watchtime": watchTime, "revenue": revenue });

                        let discreteData = channelData.discreteReportObj || [];
                        discreteData.forEach((dataPoint) => {
                            let formattedDate = `${dataPoint.day}-${dataPoint.month}-${dataPoint.year}`;
                            revenueData.push([formattedDate, parseFloat(dataPoint.value)]);
                        });

                        let chartId = `chart-${channelId}`;
                        let activeClass = index === 0 ? "active show" : "";

                        channelDataHtml += `
                            <div class="container-fluid ${activeClass}" 
                                id="v-pills-${channelId}" role="tabpanel" aria-labelledby="v-pills-${channelId}-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4> ${name}</h4>
                                    </div>
                                    <div class="col-lg-4">
                                        <p class="metric-title">Views</p>
                                        <h5 class="metric-figure">${views.toLocaleString()}</h5>
                                    </div>
                                    <div class="col-lg-4">
                                        <p class="metric-title">Watch Time</p>
                                        <h5 class="metric-figure">${watchTime.toLocaleString()}</h5>
                                    </div>
                                    <div class="col-lg-4">
                                        <p class="metric-title">Revenue</p>
                                        <h5 class="metric-figure">$ ${revenue.toFixed(2)}</h5>
                                    </div>
                                    <div class="col-md-12">
                                        <h5>Revenue Over Time</h5>
                                        <div id="${chartId}" style="width: 100%; height: 400px;"></div>
                                    </div>
                                </div>
                            </div>`;

                        google.charts.setOnLoadCallback(() => drawChart(revenueData, chartId));
                    });
                    $("#v-pills-tabContent").html(channelDataHtml);
                },
                error: function () {
                    $("#v-pills-tabContent").html("<p class='text-danger'>Failed to fetch channels. API is not responding try again.</p>");
                }
            });
        });
    });
</script>
