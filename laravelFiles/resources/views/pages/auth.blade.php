<main style="padding: 3em 0;">
    <div class="container">
        <p class="text-danger" id="authError"></p>
        <form action="{{url('auth-exe')}}" id="authForm" method="post">
            @csrf
            <input type="text" name="secretcode" />
            <button type="submit" class="btn btn-success">Enter</button>
        </form>
    </div>
</main>

<script>

    $("form#authForm").submit(function (e) { 
        e.preventDefault();
        $.ajax({
            type: $(this).attr("method"),
            url: $(this).attr("action"),
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    window.location.replace('http://localhost/yt_analyticsv2/dashboard');
                } else {
                    $("p#authError").html("Access denied");
                }
            },
            error: function (xhr) {
                console.error("AJAX error:", xhr);
                $("p#authError").html("Request failed. Check console.");
            }
        });
    });

    

</script>