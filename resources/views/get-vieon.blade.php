<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>GET VIEON</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">

</head>
<body>
<div class="container">
    <div class="mt-5">
        <button data-url="https://vieon.vn/truyen-hinh-truc-tuyen/vtv1-hd/" class="btn btn-primary _get_vieon">VTV 1</button>
        <button data-url="https://vieon.vn/truyen-hinh-truc-tuyen/vtv2-hd/" class="btn btn-primary _get_vieon">VTV 2</button>
        <button data-url="https://vieon.vn/truyen-hinh-truc-tuyen/vtv3-hd/" class="btn btn-primary _get_vieon">VTV 3</button>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script
    src="https://code.jquery.com/jquery-3.6.4.js"
    integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E="
    crossorigin="anonymous"></script>
<script>
    document.addEventListener("DOMContentLoaded",function (){
        let pattern = /<script id="__NEXT_DATA__" type="application\/json">(.*?)<\//;
        $(this).on('click','._get_vieon',function (){
            let url = $(this).data('url')

            $.ajax({
                url: url,
                type:"GET",
                dataType:'html',
                success:(response)=>{
                    console.log(response.match(pattern)[1]);
                }
            })
        })
    })
</script>
</body>
</html>
