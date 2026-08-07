<!doctype html>
<html lang="uk">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Перехід до оплати</title></head>
<body>
<p>Переходимо до захищеної сторінки WayForPay…</p>
<form id="wayforpay" method="post" action="{{ $action }}" accept-charset="utf-8">
@foreach ($fields as $name => $value)
    @if (is_array($value))
        @foreach ($value as $item)<input type="hidden" name="{{ $name }}[]" value="{{ $item }}">@endforeach
    @else
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endif
@endforeach
<noscript><button type="submit">Перейти до оплати</button></noscript>
</form>
<script>document.getElementById('wayforpay').submit();</script>
</body>
</html>
