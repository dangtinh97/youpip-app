<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="{{asset('logo-512.ico')}}">
    <title>YouPiP</title>
    <style>
        body {
            background: #89122D;
            margin: 0 auto;
        }
        #app{
            position: absolute;
        }
        footer{
            position: fixed;
            bottom: 1px;
            width: 100%;
            text-align: center;
        }
        #slide{
            position: fixed;
            width: 100%;
        }
        #image{
            text-align: center;
            max-height: 100%;
            width: 100%;
        }
    </style>
</head>
<body>
<div id="app">
    <div id="slide">
        <img class="d-block w-100" id="image" src="{{asset('img/no-ads.jpg')}}" alt="First slide">
{{--        <h3>Tạo slideshow bằng HTML Và CSS</h3>--}}
{{--        <div id="slideshow">--}}
{{--            <div class="slide-wrapper">--}}
{{--                <div class="slide"><img src="{{asset('img/no-ads.jpg')}}"></div>--}}
{{--                <div class="slide"><img src="{{asset('img/youpip.jpg')}}"></div>--}}
{{--                <div class="slide"><img src="{{asset('img/pip.jpg')}}"></div>--}}
{{--                <div class="slide"><img src="{{asset('img/share-felling.jpg')}}"></div>--}}
{{--                <div class="slide"><img src="{{asset('img/chat.jpg')}}"></div>--}}
{{--                <div class="slide"><img src="https://images.pexels.com/photos/4484184/pexels-photo-4484184.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=750&w=1260"></div>--}}
{{--            </div>--}}
{{--        </div>--}}
    </div>
    <footer id="footer">
        <a href="https://play.google.com/store/apps/details?id=org.youpip.app&hl=vi&gl=US" target="_blank">
            <img src="{{asset('img-download.png')}}" height="100px">
        </a>
    </footer>
</div>
<script src="https://unpkg.com/@popperjs/core@2">

</script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        console.log("YouPiP")
        setTimeout(function () {
            console.log("YouPiP redirect")
            window.location.href = 'https://play.google.com/store/apps/details?id=org.youpip.app&hl=vi&gl=US'
        }, 2000)
    })
</script>
</body>
</html>
