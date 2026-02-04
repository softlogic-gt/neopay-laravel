<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>3DES Step 4</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <iframe
            id="step_up_iframe"
            style="border: none; margin-left: auto; margin-right: auto; display: block"
            height="800px"
            width="400px"
            name="stepUpIframe"
        >
    </iframe>
    <form id="step_up_form" name="stepup" method="POST" target="stepUpIframe" action="{{ $action }}">
        <input id="step_up_form_jwt_input" type="hidden" name="JWT" value="{{ $token }}" />
    </form>
    <script>
        window.onload = function() {
        var stepUpForm = document.querySelector('#step_up_form');
        if(stepUpForm)
            stepUpForm.submit();
        }
    </script>

</body>

</html>
