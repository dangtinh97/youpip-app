<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Eat lunch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
</head>
<body>
<div class="container mt-4 px-4">
    <form method="POST" action="{{str_replace("http://","https://",route('eat-lunch.api'))}}">
        <div>
            <p>Date: {{date('d/m/Y',time())}}</p>
        </div>
        <div class="form-group">
            <label>So tien</label>
            <input required type="number" class="form-control" name="total">
        </div>
        <div class="form-group mt-4">
            @foreach($users as $username => $name)
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="{{$username}}" name="user[]" id="flexCheckChecked{{$username}}" >
                    <label class="form-check-label" for="flexCheckChecked">
                        {{$name}} <p data-user="{{$username}}">0</p>
                    </label>
                </div>
            @endforeach
        </div>
        <div class="row mt-4">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>

</div>
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
<script>
    document.addEventListener("DOMContentLoaded",()=>{
      $('input[name=total]').on('change',changeMoney())
      $('input[type=checkbox]').on('change',changeMoney())
      function changeMoney(){
        console.log("change")
        const total  = $('input[name=total]').val();
        const inputs = $('input[type=checkbox]')
        for(let i=0;i< inputs.length;i++){
          const id = $(inputs[i]).attr('id')
          const p = $(`p[data-user=${id}]`).html(total/$('input[type=checkbox]').prop('checked'))
        }
      }
    })
</script>
</body>
</html>
