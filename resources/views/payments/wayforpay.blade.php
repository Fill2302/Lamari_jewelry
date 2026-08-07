<!doctype html>
<html lang="uk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Перехід до оплати</title>
</head>
<body>
<p id="payment-status">Відкриваємо захищену оплату WayForPay…</p>
<button id="payment-retry" type="button" hidden>Спробувати ще раз</button>
<noscript>Для оплати потрібно увімкнути JavaScript і перезавантажити сторінку.</noscript>

<script id="widget-wfp-script" src="https://secure.wayforpay.com/server/pay-widget.js"></script>
<script>
    const paymentFields = @json($fields);
    const returnUrl = paymentFields.returnUrl;
    const status = document.getElementById('payment-status');
    const retry = document.getElementById('payment-retry');

    paymentFields.authorizationType = paymentFields.merchantAuthType;
    paymentFields.straightWidget = true;
    delete paymentFields.merchantAuthType;

    const redirectToStore = () => window.location.assign(returnUrl);
    const openPayment = () => {
        retry.hidden = true;
        status.textContent = 'Відкриваємо захищену оплату WayForPay…';

        if (typeof Wayforpay === 'undefined') {
            status.textContent = 'Не вдалося завантажити форму оплати.';
            retry.hidden = false;
            return;
        }

        const wayforpay = new Wayforpay();
        wayforpay.run(
            paymentFields,
            redirectToStore,
            () => {
                status.textContent = 'Оплату відхилено. Спробуйте ще раз.';
                retry.hidden = false;
            },
            redirectToStore,
        );
    };

    retry.addEventListener('click', openPayment);
    window.addEventListener('load', openPayment, { once: true });
</script>
</body>
</html>
