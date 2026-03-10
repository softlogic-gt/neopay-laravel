<?php
namespace SoftlogicGT\NeoPayLaravel;

use Log;
use Carbon\Carbon;
use Illuminate\Http\Request;
use LVR\CreditCard\CardNumber;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Client\ConnectionException;
use SoftlogicGT\NeoPayLaravel\Jobs\SendReceipt;

class NeoPay
{
    protected $approvedInstallments = ['VC03', 'VC06', 'VC10', 'VC12', 'VC18', 'VC24'];
    protected $receipt              = [
        'email'   => null,
        'subject' => 'Comprobante de pago',
        'name'    => '',
        'cc'      => '',
    ];

    protected $params = [
        'MessageTypeId'             => '',
        'SystemsTraceNo'            => '',
        'ProcessingCode'            => '',
        'TimeLocalTrans'            => '',
        'DateLocalTrans'            => '',
        'PosEntryMode'              => '',
        'Nii'                       => '',
        'PosConditionCode'          => '',
        'AdditionalData'            => '',
        'OrderInformation'          => '',
        'FormatId'                  => '1',
        'Merchant'                  => [
            'TerminalId' => '',
            'CardAcqId'  => '',
        ],
        'Card'                      => [
            'Type'                  => '',
            'PrimaryAcctNum'        => '',
            'DateExpiration'        => '',
            'Cvv2'                  => '',
            'Track2Data'            => '',
            'CardTokenId'           => '',
            'UniqueCodeofBeneciary' => '',
        ],
        'Amount'                    => [
            'AmountTrans'       => '',
            'AmountDiscount'    => '',
            'RateDiscount'      => '',
            'AdditionalAmounts' => '',
            'TaxDetail'         => [],
        ],
        'PrivateUse60'              => [
            'BatchNumber' => '',
        ],
        'PrivateUse63'              => [
            'LodgingFolioNumber14' => '',
            'NationalCard25'       => '',
            'HostReferenceData31'  => '',
            'TaxAmount1'           => '',
        ],
        'TokenManagement'           => [
            'Type'         => '',
            'ActionMethod' => '',
        ],
        'Customer'                  => [
            'CustomerTokenId'    => '',
            'FirstName'          => '',
            'LastName'           => '',
            'TaxId'              => '',
            'IdentificationType' => '',
            'PersonalId'         => '',
            'Email'              => '',
            'PhoneNumber'        => '',
        ],
        'BillTo'                    => [
            'FirstName'          => '',
            'LastName'           => '',
            'Company'            => '',
            'AddressOne'         => '',
            'AddressTwo'         => '',
            'Locality'           => '',
            'AdministrativeArea' => '',
            'PostalCode'         => '',
            'Country'            => '',
            'Email'              => '',
            'PhoneNumber'        => '',
        ],
        'ShipTo'                    => [
            'DefaultSt'              => '',
            'FirstName'              => '',
            'LastName'               => '',
            'Company'                => '',
            'AddressOne'             => '',
            'AddressTwo'             => '',
            'Locality'               => '',
            'AdministrativeArea'     => '',
            'PostalCode'             => '',
            'Country'                => '',
            'Email'                  => '',
            'PhoneNumber'            => '',
            'ShippingAddressTokenId' => '',
        ],
        'PaymentInstrument'         => [
            'PaymentInstrumentTokenId' => '',
        ],
        'CustomerPaymentInstrument' => [
            'CustomerPaymentInstrumentTokenId' => '',
            'DefaultCpi'                       => '',
        ],
        'PayerAuthentication'       => [
            'Step'        => '',
            'ReferenceId' => '',
        ],
    ];

    protected $codes = [
        "00" => "Aprobada",
        "01" => "Refierase al emisor",
        "02" => "Refierase al emisor, condición especial",
        "03" => "comercio o proveedor de servicio no válida",
        "04" => "Recoger tarjeta",
        "05" => "Transaccion no aceptada",
        "06" => "Error",
        "07" => "Recoger tarjeta condicion especial (Otra a Robada/perdida)",
        "10" => "Aprobación Parcial",
        "11" => "Aprobación V.I.P.",
        "12" => "Transacción no válida",
        "13" => "Cantidad inválida",
        "14" => "número de cuenta no válido (no hay tal número)",
        "15" => "No existe el emisor",
        "17" => "Cancelacion del cliente",
        "19" => "Vuelva a introducir la transacción",
        "20" => "Respuesta Invalida",
        "21" => "Ninguna medida adoptada",
        "22" => "Sospecha de Mal funcionamiento",
        "25" => "No se puede localizar en el archivo de registro, o número de cuenta",
        "28" => "Archivo no está disponible temporalmente",
        "30" => "Error de formato",
        "31" => "Transaccion no soportada por el SWITCH",
        "41" => "Recoger tarjeta (tarjeta perdida)",
        "43" => "Recoger tarjeta(tarjeta robada)",
        "51" => "Insuficiencia de fondos",
        "52" => "Ninguna cuenta corriente",
        "53" => "Ninguna cuenta de ahorro",
        "54" => "La tarjeta ha caducado",
        "55" => "PIN incorrecto",
        "57" => "Transacción no permitido a los titulares de tarjetas",
        "58" => "Transacción no permitida a la terminal",
        "59" => "Sospechas de fraude",
        "61" => "La cantidad ha superado el límite",
        "62" => "Tarjeta restringida",
        "63" => "Violaciòn de seguridad",
        "65" => "Fuera de parametros transaccionales",
        "68" => "Respuesta recibida demasiado tarde",
        "75" => "Número permitido de intentos de entrada de PIN-superado",
        "76" => "No se puede localizar el mensaje anterior",
        "77" => "Mensaje anterior se encuentra una repetición o inversión, pero los datos de repet",
        "78" => "Bloqueado, primer uso",
        "80" => "Transacciones de Visa: no disponible emisor del crédito. La marca de distribuidor",
        "81" => "Error criptografico encontrado en el PIN",
        "82" => "CAM, dCVV, ICVV, o resultados negativos CVV",
        "83" => "No se puede verificar PIN",
        "85" => "No hay razón para rechazar una solicitud de verificación del número de cuenta",
        "89" => "Terminal inválida",
        "91" => "Emisor NO disponible",
        "92" => "Destino no se puede encontrar para el enrutamiento",
        "93" => "La transacción no se puede completar.  Solo se aceptan tarjetas locales Visa y Mastercard",
        "94" => "Transacción duplicada",
        "96" => "Mal funcionamiento del sistema, intente mas tarde",
        "N0" => "Fuerza CTPI",
        "se" => "Servicio de caja N3 no disponible",
        "N3" => "Servicio de caja no disponible",
        "N4" => "Solicitud de reembolso en efectivo excede el límite de emisor",
        "N7" => "CVV2 incorrecto",
        "Di" => "Sminución N7 para el fracaso CVV2",
        "P2" => "Información no válida emisor de la factura",
        "P5" => "Solicitud de PIN Cambiar / Desbloquear declinó",
        "P6" => "Inseguro PIN",
        "Au" => "Tenticación de tarjeta no Q1",
        "R0" => "Orden de Suspensión de Pago",
        "R1" => "Revocación de Autorización de Orden",
        "R3" => "Revocación de todas las autorizaciones de pedido",
        "XA" => "Avanzar al emisor",
        "XD" => "Avanzar al emisor",
        "Z3" => "No se puede ir en línea",
    ];

    public function __construct(array $config = [])
    {
        $this->params['Merchant']['TerminalId'] = config('neopay.terminal');
        $this->params['Merchant']['CardAcqId']  = config('neopay.affilliation');

        $receipt = $config['receipt'] ?? [];
        foreach (['email', 'subject', 'name', 'cc'] as $key) {
            if (isset($receipt[$key])) {
                $this->receipt[$key] = $receipt[$key];
            }
        }
    }

    public function tokenize($creditCard, $expirationMonth, $expirationYear, $name, $lastName, $address, $locality, $zipCode, $countryCode, $subdivisionCode, $email, $phone)
    {
        $expirationYear = (int) substr((string) $expirationYear, -2);
        $data           = compact(
            "creditCard", "expirationMonth", "expirationYear", "name", "lastName", "address",
            "locality", "zipCode", "countryCode", "subdivisionCode"
        );

        $rules = [
            'creditCard'      => ['required', new CardNumber],
            'expirationMonth' => 'required|numeric|lte:12|gte:1',
            'expirationYear'  => 'required|numeric|lte:99|gte:1',
            'name'            => 'required',
            'lastName'        => 'required',
            'address'         => 'required',
            'locality'        => 'required',
            'zipCode'         => 'required',
            'countryCode'     => 'required|string|size:2|uppercase',
            'subdivisionCode' => 'required|string|max:3|uppercase',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $month = str_pad($expirationMonth, 2, "0", STR_PAD_LEFT);
        $year  = str_pad($expirationYear, 2, "0", STR_PAD_LEFT);

        $payload = [
            'Card'            => [
                'Type'           => $this->getCCType($creditCard),
                'PrimaryAcctNum' => $creditCard,
                'DateExpiration' => $year . $month,
            ],

            'TokenManagement' => [
                'Type'         => 'PAYMENT_INSTRUMENT',
                'ActionMethod' => 'C',
            ],

            'BillTo'          => [
                'FirstName'          => $name,
                'LastName'           => $lastName,
                'AddressOne'         => $address,
                'Locality'           => $locality,
                'AdministrativeArea' => $subdivisionCode,
                'PostalCode'         => $zipCode,
                'Country'            => $countryCode,
                'Email'              => $email,
                'PhoneNumber'        => $phone,
            ],
        ];

        Log::info('---Tokenize---');

        $payload  = array_replace_recursive($this->params, $payload);
        $client   = $this->getClient();
        $response = $client->post('api/AuthorizationPaymentCommerce', $payload);
        $data     = $response->json();

        Log::info(json_encode($payload));
        Log::info(json_encode($data));

        if ($data['ResponseCode'] != '00') {
            abort(400, $data['PrivateUse63']['AlternateHostResponse22']);
        }

        return [
            'token' => $data['PaymentInstrument']['PaymentInstrumentTokenId'],
        ];
    }

    public function saleWithToken($paymentToken, $cvv, $amount, $externalId, $installments = null)
    {
        $data = compact("paymentToken", "cvv", "amount", "installments", "externalId");

        $rules = [
            'paymentToken' => ['required'],
            'cvv'          => ['required'],
            'amount'       => 'required|numeric',
            'externalId'   => ['required'],
            'installments' => ['nullable', Rule::in($this->approvedInstallments)],
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload = [
            'MessageTypeId'       => '0200',
            'ProcessingCode'      => '000000',
            'SystemsTraceNo'      => $this->getStrExternalId($externalId),
            'PosEntryMode'        => '012',
            'Nii'                 => '003',
            'PosConditionCode'    => '00',
            'FormatId'            => '1',
            'Card'                => [
                'Cvv2' => $cvv,
            ],
            'Amount'              => [
                'AmountTrans'    => (int) (round($amount, 2) * 100),
                'TaxInformation' => [],
            ],
            'PaymentInstrument'   => [
                'PaymentInstrumentTokenId' => $paymentToken,
            ],
            'PayerAuthentication' => [
                'Step'        => '1',
                'UrlCommerce' => config('neopay.redirect') . '?externalid=' . $externalId,
            ],
            'AdditionalData'      => $installments ?? '',
        ];

        Log::info('---Sale---');

        $payload  = array_replace_recursive($this->params, $payload);
        $client   = $this->getClient();
        $response = $client->post('api/AuthorizationPaymentCommerce', $payload);
        $data     = $response->json();

        Log::info(json_encode($payload));
        Log::info(json_encode($data));

        if ($data['ResponseCode'] != '00') {
            abort(400, $data['PrivateUse63']['AlternateHostResponse22']);
        }

        $params = [
            'action'      => $data['PayerAuthentication']['DeviceDataCollectionUrl'],
            'token'       => $data['PayerAuthentication']['AccessToken'],
            'referenceid' => $data['PayerAuthentication']['ReferenceId'],
            'externalid'  => $externalId,
        ];

        $html = view('neopay-laravel::step2', $params);

        if (request()->expectsJson()) {
            return $html->render();
        }

        return $html;
    }

    public function completeSale($referenceId, $externalId, $step)
    {
        $data = compact("referenceId", "externalId");

        $rules = [
            'referenceId' => 'required',
            'externalId'  => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload = [
            'MessageTypeId'       => '0200',
            'ProcessingCode'      => '000000',
            'SystemsTraceNo'      => $this->getStrExternalId($externalId),
            'PosEntryMode'        => '012',
            'Nii'                 => '003',
            'PosConditionCode'    => '00',
            'FormatId'            => '1',
            'PayerAuthentication' => [
                'Step'        => $step,
                'ReferenceId' => $referenceId,
            ],
        ];

        Log::info('---Complete Sale---');

        $payload = array_replace_recursive($this->params, $payload);
        $client  = $this->getClient();

        try {
            $response = $client->post('api/AuthorizationPaymentCommerce', $payload);
        } catch (ConnectionException $e) {
            Log::error('---Error Timeout---');

            Log::error($e->getMessage());
            $this->reversal($referenceId, $externalId, $step);
            abort(400, "El servicio no respondió en el tiempo establecido. Intentar nuevamente.");
        } catch (RequestException $e) {
            Log::error('---Error---');
            Log::error($e->response?->body());

            abort(400, "Error en el servicio. Intentar nuevamente.");
        }

        $data = $response->json();

        Log::info(json_encode($payload));
        Log::info(json_encode($data));

        if ($data['ResponseCode'] != '00') {
            if ($data['ResponseCode'] == '91') {
                $this->reversal($referenceId, $externalId, $step);
            }

            abort(400, $data['PrivateUse63']['AlternateHostResponse22']);
        }

        if (in_array($data['PayerAuthentication']['Step'], [3, 5])) {
            $this->sendReceipt($data);

            return [
                'response'    => $data['ResponseCode'],
                'authcode'    => $data['PrivateUse63']['AlternateHostResponse22'],
                'referenceid' => $data['PayerAuthentication']['ReferenceId'],
                'externalid'  => $externalId,
                'step'        => $data['PayerAuthentication']['Step'],
            ];
        }

        // Step 4
        $params = [
            'action'      => $data['PayerAuthentication']['DeviceDataCollectionUrl'],
            'token'       => $data['PayerAuthentication']['AccessToken'],
            'referenceid' => $data['PayerAuthentication']['ReferenceId'],
            'externalid'  => $externalId,
            'step'        => $step,
        ];

        $html = view('neopay-laravel::step4', $params);

        if (request()->expectsJson()) {
            return [
                'html'        => $html->render(),
                'referenceid' => $params['referenceid'],
                'externalid'  => $params['externalid'],
                'step'        => $data['PayerAuthentication']['Step'],
            ];
        }

        return $html;
    }

    public function reversal($referenceId, $externalId, $step)
    {
        $data = compact("referenceId", "externalId");

        $rules = [
            'referenceId' => 'required',
            'externalId'  => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload = [
            'MessageTypeId'       => '0400',
            'ProcessingCode'      => '000000',
            'SystemsTraceNo'      => $this->getStrExternalId($externalId),
            'PosEntryMode'        => '012',
            'Nii'                 => '003',
            'PosConditionCode'    => '00',
            'FormatId'            => '1',
            'PayerAuthentication' => [
                'Step'        => $step,
                'ReferenceId' => $referenceId,
            ],
        ];

        Log::info('---Reversal---');

        $payload  = array_replace_recursive($this->params, $payload);
        $client   = $this->getClient();
        $response = $client->post('api/AuthorizationPaymentCommerce', $payload);
        $data     = $response->json();

        Log::info(json_encode($payload));
        Log::info(json_encode($data));

        if ($data['ResponseCode'] != '00') {
            abort(400, $data['PrivateUse63']['AlternateHostResponse22']);
        }

        return response()->json('ok');
    }

    public function cancellation($externalId, $amount)
    {
        $data = compact("externalId", "amount");

        $rules = [
            'externalId' => 'required',
            'amount'     => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload = [
            'MessageTypeId'    => '0200',
            'ProcessingCode'   => '020000',
            'SystemsTraceNo'   => $this->getStrExternalId($externalId),
            'PosEntryMode'     => '012',
            'Nii'              => '003',
            'PosConditionCode' => '00',
            'FormatId'         => '1',
        ];

        Log::info('---Cancellation---');

        $payload  = array_replace_recursive($this->params, $payload);
        $client   = $this->getClient();
        $response = $client->post('api/AuthorizationPaymentCommerce', $payload);
        $data     = $response->json();

        Log::info(json_encode($payload));
        Log::info(json_encode($data));

        if ($data['ResponseCode'] != '00') {
            abort(400, $data['PrivateUse63']['AlternateHostResponse22']);
        }
        $amount = $amount * 100;
        $this->sendReceipt($data, $amount);

        return response()->json('ok');
    }

    public function deleteToken($token)
    {
        $data = compact('token');

        $rules = [
            'token' => 'required',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $payload = [
            'FormatId'          => '1',
            'TokenManagement'   => [
                'Type'         => 'PAYMENT_INSTRUMENT',
                'ActionMethod' => 'D',
            ],
            'PaymentInstrument' => [
                'PaymentInstrumentTokenId' => $token,
            ],
        ];

        Log::info('---Delete Tokenize---');

        $payload  = array_replace_recursive($this->params, $payload);
        $client   = $this->getClient();
        $response = $client->post('api/AuthorizationPaymentCommerce', $payload);
        $data     = $response->json();

        Log::info(json_encode($payload));
        Log::info(json_encode($data));

        if ($data['ResponseCode'] != '00') {
            abort(400, $data['PrivateUse63']['AlternateHostResponse22']);
        }

        return response()->json('ok');

    }

    public function response(Request $request)
    {
        $dataView = [
            'params' => $request->all(),
        ];

        return view('neopay-laravel::response', $dataView);
    }

    protected function getClient(): PendingRequest
    {
        $url    = config('neopay.test') ? 'https://epaytestvisanet.com.gt:4433/V3/' : 'https://epayvisanet.com.gt:4433/V3/';
        $client = Http::baseUrl($url)
            ->withHeaders([
                'PaymentgwIP'      => '190.111.1.198',
                'ShopperIP'        => '190.111.1.198',
                'MerchantServerIP' => '190.111.1.198',
                'MerchantUser'     => config('neopay.user'),
                'MerchantPasswd'   => config('neopay.password'),
            ])
            ->throw()
            ->timeout(60);

        return $client;
    }

    public function getCCType($cc): string
    {
        $firstDigit = $cc[0];

        if ($firstDigit === '4') {
            return '001';
        } elseif (preg_match('/^(5[1-5]|22[2-9]|2[3-6]|27[01]|2720)/', $cc)) {
            return '002';
        }
        abort(400, 'Solo se acepta Visa o Mastercard');

        return '000';
    }

    private function sendReceipt($response, $amount = null)
    {
        if ($this->receipt['email']) {
            $amount      = $amount ? $amount : $response['AmountTrans'];
            $receiptData = [
                'email'        => $this->receipt['email'],
                'subject'      => $this->receipt['subject'],
                'name'         => $this->receipt['name'],
                'cc'           => '####-####-####-' . substr($this->receipt['cc'], -4, 4),
                'date'         => Carbon::now(),
                'amount'       => $response['ProcessingCode'] != '020000' ? $amount : -$amount,
                'ref_number'   => $response['RetrievalRefNo'],
                'auth_number'  => $response['AuthIdResponse'],
                'audit_number' => $response['ProcessingCode'] != '020000' ? $response['PrivateUse63']['AlternateHostResponse22'] : $response['SystemsTraceNo'],
                'merchant'     => config('neopay.affilliation'),
            ];

            SendReceipt::dispatch($receiptData);
        }

        return $response;
    }

    private function getStrExternalId($externalId)
    {
        return str_pad(substr($externalId, -6, 6), 6, "0", STR_PAD_LEFT);
    }
}
