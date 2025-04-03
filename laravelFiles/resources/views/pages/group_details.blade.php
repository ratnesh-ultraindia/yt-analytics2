<main>
    <div class="container">
        <h2 style="margin: 2em 0;">{{$incharge}}'s members</h2>
        <div class="row" style="margin-top: 2em;">
            @foreach ($members as $member)


                <div class="col-lg-3 col-md-6 col-sm-12" >
                    <a href="{{url('member-details/'.$member->id)}}">
                    <div class="card info-card">
                        
                        <h5 class="card-title">{{$member->incharge}}'s channels</h5>

                    </div>
                    </a>

                </div>
            </a>
            @endforeach
        </div>
    </div>
</main>