<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/x-icon" href="{{asset('logo-512.ico')}}">
    <meta name="keywords" content="YouPip, Youtube không quảng cáo, Youtube">
    <meta name="description" content="Xem video không quảng cáo,xem video trong nền, chat cùng openai">
    <meta name="author" content="YouPiP">
    <meta property="og:url"                content="https://youpip.net" />
    <meta property="og:type"               content="Video" />
    <meta property="og:title"              content="#YouPip Xem video không quảng cáo" />
    <meta property="og:description"        content="Xem video không quảng cáo,xem video trong nền, chat cùng openai" />
    <meta property="og:image"              content="https://youpip.net/img/logo-512.jpg" />

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
    </div>
    <footer id="footer">
        <a href="https://play.google.com/store/apps/details?id=org.youpip.app&hl=vi&gl=US" target="_blank">
            <img src="{{asset('img-download.png')}}" height="100px">
        </a>
    </footer>
</div>

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
