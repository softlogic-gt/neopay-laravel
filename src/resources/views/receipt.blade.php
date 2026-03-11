@component('mail::message-custom')
<center>
<table  style="width:100%; border-collapse: collapse;">
<tbody>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Método de Pago</td>
<td>NeoNet</td>
</tr>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Fecha transacción</td>
<td>{{ $receiptData['date']->format('d-m-Y H:i') }}</td>
</tr>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Monto de la venta</td>
<td>Q. {{ number_format($receiptData['amount'] / 100,2) }}</td>
</tr>
@if (!empty($receiptData['installments']))
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Número de cuotas</td>
<td>{{ $receiptData['installments'] }}</td>
</tr>
@endif
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Nombre tarjeta</td>
<td>{{ $receiptData['name'] }}</td>
</tr>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>No. tarjeta</td>
<td>{{ $receiptData['cc'] }}</td>
</tr>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Número de referencia</td>
<td>{{ $receiptData['ref_number'] }}</td>
</tr>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Número de autorización</td>
<td>{{ $receiptData['auth_number'] }}</td>
</tr>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Afiliación</td>
<td>{{ $receiptData['merchant'] }}</td>
</tr>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td>Número de auditoría</td>
<td>{{ $receiptData['audit_number'] }}</td>
</tr>
<tr style="border-bottom: 1px solid #e3e3e3; color:#333">
<td colspan="2">(01) Pagado electrónicamente</td>
</tr>
</tbody>
</table>
</center>
@endcomponent
