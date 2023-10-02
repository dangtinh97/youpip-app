<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title','Call Audio')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="{{asset('/css/call.css')}}?v=1" type="text/css" rel="stylesheet">
    <link href="https://youpip.net/css/call.css?v=1" type="text/css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
{{--    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">--}}
</head>
<body>
<div>
    @yield('content')
</div>
@stack('script')
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>
<script>
    let timeCount = null;
    let time =0 ;
    function counterTime(valueId) {
        time =0;
        timeCount = setInterval(()=>{
            time+=1;

            let minute = Math.floor(time/60)

            if(minute<10){
                minute = `0${minute}`
            }

            let second = time - (minute*60)
            if(second<10){
                second = `0${second}`
            }

            $(valueId).html(`${minute}:${second}`)
        },1000)
    }
</script>
</body>
</html>
