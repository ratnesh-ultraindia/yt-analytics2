<main>
    <div class="container">
        <h2 style="margin: 2em 0;">{{$incharge}}'s channels</h2>

        @foreach($channels as $channelId => $channel)

        <div class="row" style="margin-top: 2em;">
            <div class="col-lg-4 col-md-12 col-sm-12">

                <a href="{{url('channel-data/'.$channelId)}}">
                <img src="{{$channel["thumbnail"]}}" style="width:100px; height: 100px;">
                <h4>{{$channel["title"]}}</h4>
                </a>
            </div>
            
        </div>

        @endforeach

    </div>
</main>