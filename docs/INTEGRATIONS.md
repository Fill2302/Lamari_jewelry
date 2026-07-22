# Інтеграції

Усі провайдери підключаються через внутрішні контракти (`PaymentGateway`,
`ShippingProvider`, `Notifier`), щоб їх можна було замінити без переробки checkout.

## Потрібні для MVP

1. **Оплата — LiqPay або WayForPay (остаточний вибір після перевірки договору й
   тарифів).** Hosted checkout, Apple Pay/Google Pay за доступності, sandbox,
   підписані webhook-и, capture/refund і щоденна reconciliation. Карткові дані
   Lamari не зберігає.
2. **Нова пошта.** Пошук міст/відділень/поштоматів, розрахунок доставки,
   створення ЕН, tracking і синхронізація статусів. Передбачити ручну доставку й
   самовивіз як fallback.
3. **Email.** Транзакційний SMTP/API-провайдер із SPF, DKIM, DMARC: підтвердження
   замовлення, оплати, відправлення, скидання пароля.
4. **SMS/Viber — опційно в MVP.** Сповіщення про замовлення/відправлення через
   українського провайдера; не використовувати для маркетингу без згоди.
5. **Object storage + CDN.** S3-сумісне сховище для оригіналів і похідних
   зображень; приватний bucket, signed URLs для службових файлів.

## Маркетинг і вимірювання

- **Google Tag Manager + GA4 ecommerce:** `view_item_list`, `view_item`,
  `add_to_cart`, `begin_checkout`, `purchase`, `refund`; не дублювати purchase.
- **Google Search Console:** sitemap, canonical та контроль індексації.
- **Meta Pixel/Conversions API:** лише після згоди користувача й погодження
  політики приватності; deduplication через event id.
- **Merchant Center product feed:** другий етап після стабілізації каталогу.

На staging усі маркетингові теги вимкнені або працюють у debug/test property.

## Операційні

- **Sentry або GlitchTip:** backend/frontend errors, scrub PII.
- **Uptime/health monitoring:** `/up`, SSL expiry і queue heartbeat.
- **Backups:** зашифровані дампи PostgreSQL та versioned object storage;
  регулярна перевірка відновлення.
- **CI/CD:** GitHub Actions або GitLab CI — lint, static analysis, тести, build,
  deploy на staging; production job тільки manual + protected environment.

## Дані/доступи, потрібні від власника перед інтеграцією

- Юридична особа/ФОП, обраний платіжний провайдер і sandbox-доступ.
- API key Нової пошти та дані відправника.
- Підтверджений домен/піддомен для email і тестового середовища.
- Обрані email/SMS провайдери та згода на їх тарифи.
- GA4/GTM/Meta identifiers і затверджена cookie/privacy policy.

## Правила webhook-ів

- Валідація підпису до обробки, allowlist подій, raw payload у захищеному логі.
- Унікальний `external_id`, idempotent handler, черга з retry/backoff і DLQ.
- HTTP 2xx лише після безпечного прийняття; бізнес-обробка асинхронно.
- Адмін-екран для невдалих подій і ручного повтору без дублювання операцій.

