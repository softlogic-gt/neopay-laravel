<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>3DES Step 2</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <iframe name="ddc-iframe" title="3DS Device Data Collection" width="1" height="1" aria-hidden="true">
    </iframe>
    <form id="ddc-form" method="POST" target="ddc-iframe" action="{{ $action }}">
        <input type="hidden" name="JWT" value="{{ $token }}">
    </form>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ddcForm = document.getElementById('ddc-form');
            if (!ddcForm) {
                return;
            }
            
            ddcForm.submit();
            window.parent.postMessage(
                { type: 'NEOPAY_IFRAME_LOADED' },
                '*'
            );
        });

        // Listen for Cardinal response
        window.addEventListener('message', function(event) {
            if (event.origin !== 'https://centinelapistag.cardinalcommerce.com') {
                return;
            }

            let data;
            try {
                data = typeof event.data === 'string' ?
                    JSON.parse(event.data) :
                    event.data;
            } catch (e) {
                console.error('Invalid message data', e);
                window.parent.postMessage(
                    {
                        type: 'NEOPAY_ERROR',
                        error: e
                    },
                    '*'
                );
                return;
            }

            console.log('Merchant received a message:', data);

            if (data && data.Status) {
                 window.parent.postMessage(
                        {
                            type: 'NEOPAY_RESULT',
                            result: {
                                referenceId: "{{ $referenceid }}",
                                externalId: "{{ $externalid }}"
                            }
                        },
                        '*'
                    );
                return ;
            }

            window.parent.postMessage(
                {
                    type: 'NEOPAY_ERROR',
                    error: data
                },
                '*'
            );
        });
    </script>

</body>

</html>
