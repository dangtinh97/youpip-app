<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <title>ESP8266</title>
    <style>
        .form-switch{
            display: flex;
        }
        input.form-check-input{
            width: 100px!important;
            height: 50px;
        }
        .form-check-label{
            font-size: 1.8rem;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-left: 1rem;
        }

        #control-group{
            display: flex;
            align-items: center;
            flex-direction: column;
        }
    </style>
</head>
<body>
<div class="container">
    <p>
        Status: <strong id="status">Pending</strong>
    </p>

    <p>
        ESP8266: <strong id="device_status">Pending...</strong>
    </p>
    <div class="form-group" id="control-group">
        <div class="d-none">
            <button type="button" class="control_device btn btn-primary" disabled data-device="1" data-status=""> Thiết bị 1 </button>
            <button type="button" class="control_device btn btn-primary" disabled data-device="2" data-status=""> Thiết bị 2 </button>
            <button type="button" class="control_device btn btn-primary" disabled data-device="3" data-status=""> Thiết bị 3 </button>
            <button type="button" class="control_device btn btn-primary " disabled data-device="4" data-status=""> Thiết bị 4 </button>
        </div>

        <div class="form-check form-switch">
            <input class="control_sw form-check-input" type="checkbox" role="switch" id="btn-1" >
            <label class="form-check-label" for="btn-1"> Thiết bị 1 </label>
        </div>
        <div class="form-check form-switch">
            <input class="control_sw form-check-input"  type="checkbox" role="switch" id="btn-2" >
            <label class="form-check-label" for="btn-2"> Thiết bị 2 </label>
        </div>
        <div class="form-check form-switch">
            <input class="control_sw form-check-input" type="checkbox" role="switch" id="btn-3" >
            <label class="form-check-label" for="btn-3"> Thiết bị 3 </label>
        </div>
        <div class="form-check form-switch">
            <input class="control_sw form-check-input" type="checkbox" role="switch" id="btn-4" >
            <label class="form-check-label" for="btn-4"> Thiết bị 4 </label>
        </div>
    </div>
</div>
<script
    src="https://code.jquery.com/jquery-3.6.4.js"
    integrity="sha256-a9jBBRygX1Bh5lt8GZjXDzyOB+bWve9EiO7tROUtj/E="
    crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/web-socket-js/1.0.0/web_socket.min.js" integrity="sha512-jtr9/t8rtBf1Sv832XjG1kAtUECQCqFnTAJWccL8CSC82VGzkPPih8rjtOfiiRKgqLXpLA1H/uQ/nq2bkHGWTQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/socket.io/3.0.0/socket.io.js" integrity="sha512-FPJNGVqbetcAGvuJTpWqVuaOim5C5pyV+JaiAOxtBgsOWy0aiOLM9k5Nh7ikpSzUoz2Tb9Ue6zYWICDr9zZ5+g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    document.addEventListener("DOMContentLoaded",function (){
        $('input[id*=btn-]').attr('disabled',true).prop('checked', false)
        // var ws = new WebSocket('ws://' + 'localhost:8080' + '/control?code=PJKIZWUHVOVJNZH');
        var ws = new WebSocket('ws://' + 'youpip.net' + '/control?code=PJKIZWUHVOVJNZH');
        let esp8266Connected = false
        ws.addEventListener("open",()=>{
            $('#status').html('Open')
            ws.send("CONTROL_CONNECTED")
        })
        ws.addEventListener("message",(event)=>{
            const message = event.data
            console.log(message)
            if(message==="ESP8266_CONNECTED")
            {
                esp8266Connected = true
                $('#device_status').html('Connected!')
            }

            if (message.indexOf('DEVICE')!==-1){
                $('input[id*=btn-]').attr('disabled',false)
                $('.control_device').attr('disabled',false)
                let device = message.split('|')[1];
                let status = message.split('|')[2];
                $(`input[id=btn-${device}]`).prop('checked', status==='1')

                let button = $(`button[data-device=${device}]`)
                if(status==='1'){
                    button.removeClass('btn-outline-secondary')
                    button.addClass('btn-primary')
                }else {
                    button.removeClass('btn-primary')
                    button.addClass('btn-outline-secondary')
                }
                button.attr('data-status',status)
            }
        })

        $(this).on('change','.control_sw',function (){
            let device = $(this).attr('id').replace('btn-','');
            let status =$(this).is(':checked');
            ws.send(`EVENT_OUT-${device}_${status ? 'ON' : 'OFF'}`)
            $(this).attr('disabled',true)
        })

        $(this).on('click','.control_device',function (){
            let device = $(this).attr('data-device');
            let status = $(this).attr('data-status');
            ws.send(`EVENT_OUT-${device}_${status==='0'?'ON':'OFF'}`)
            $(this).attr('disabled',true)
        })
    })
</script>
</body>
</html>
