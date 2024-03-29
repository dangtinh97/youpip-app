<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>GET VIEON</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <style>
        body{
            background: #efefef;
        }
        pre {
            background-color: ghostwhite;
            border: 1px solid silver;
            padding: 10px 20px;
            margin: 20px;
        }
        .json-key {
            color: brown;
        }
        .json-value {
            color: navy;
        }
        .json-string {
            color: olive;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="mt-5" id="list-channel">
{{--        <button data-url="https://vieon.vn/truyen-hinh-truc-tuyen/vtv1-hd/" class="btn btn-primary _get_vieon">VTV 1</button>--}}
{{--        <button data-url="https://vieon.vn/truyen-hinh-truc-tuyen/vtv2-hd/" class="btn btn-primary _get_vieon">VTV 2</button>--}}
{{--        <button data-url="https://vieon.vn/truyen-hinh-truc-tuyen/vtv3-hd/" class="btn btn-primary _get_vieon">VTV 3</button>--}}
    </div>
    <div>
        <button id="get-all" class="btn btn-danger">Lấy tất cả</button>
    </div>
    <pre id="resultCode"><code id=account></code></pre>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script
    src="https://code.jquery.com/jquery-3.6.4.js"
    integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E="
    crossorigin="anonymous"></script>
<script>
    document.addEventListener("DOMContentLoaded",function (){
        let urls = [];
        let pattern = /<script id="__NEXT_DATA__" type="application\/json">(.*?)<\//;
        $(this).on('click','._get_vieon',function (){
            let url = $(this).data('url')
            runUrl(url)
        })

        let runUrl =(url)=>{
            $('#resultCode > code').html(library.json.prettyPrint({}))
            $.ajax({
                url: url,
                type:"GET",
                dataType:'html',
                success:(response)=>{
                    let matches = response.match(pattern);
                    if(matches.length===2){
                        updateData(url,matches[1])
                    }
                    console.log(response)
                }
            })
        }

        let showJson = (json)=>{
            $('#resultCode > code').html(library.json.prettyPrint(json))
        }

        $(this).on('click','#get-all',async function (){
            await sleep(1);
            for (let i=0;i<urls.length;i++){
                runUrl(urls[i])
                await sleep(10);
            }
            showJson({
                status:200,
                content:'Xong tất cả.',
                data:{}
            })
        })

        let sleep = (second) =>{
            return new Promise((resolve)=>{
                setTimeout(()=>{
                    resolve(true)
                },second * 1000)
            })
        }

        let init =async ()=>{
            $.ajax({
                url:'{{route('api.vtv-vieon.list')}}'.replace('http:',window.location.protocol),
                type:"GET",
                dataType:"JSON",
                success:(response)=>{
                    let list = response.data.list;
                    list.forEach((item)=>{
                        let urlChannel =  `https://vieon.vn/${item.video_id}`;
                        urls.push(urlChannel)
                        $('#list-channel').append(`<button data-url="${urlChannel}" class="btn btn-primary m-1 _get_vieon">${item.chanel_name}</button>`)
                    })
                }
            })
        }

        init()

        let updateData = (url,json)=>{
            //api.vtv-vieon.update
            $.ajax({
                url:'{{route('api.vtv-vieon.update')}}'.replace('http:',window.location.protocol),
                type:"POST",
                dataType:"JSON",
                data:{
                    url:url,
                    json:json
                },
                success:(response)=>{
                    console.log(response)
                    $('#resultCode > code').html(library.json.prettyPrint(response))
                }
            })
        }

        if (!library)
            var library = {};

        library.json = {
            replacer: function(match, pIndent, pKey, pVal, pEnd) {
                var key = '<span class=json-key>';
                var val = '<span class=json-value>';
                var str = '<span class=json-string>';
                var r = pIndent || '';
                if (pKey)
                    r = r + key + pKey.replace(/[": ]/g, '') + '</span>: ';
                if (pVal)
                    r = r + (pVal[0] == '"' ? str : val) + pVal + '</span>';
                return r + (pEnd || '');
            },
            prettyPrint: function(obj) {
                var jsonLine = /^( *)("[\w]+": )?("[^"]*"|[\w.+-]*)?([,[{])?$/mg;
                return JSON.stringify(obj, null, 3)
                    .replace(/&/g, '&amp;').replace(/\\"/g, '&quot;')
                    .replace(/</g, '&lt;').replace(/>/g, '&gt;')
                    .replace(jsonLine, library.json.replacer);
            }
        };
    })
</script>
</body>
</html>
