# Lamari MVP: запуск і handoff

## Локальний запуск

Вимоги: PHP 8.3+, Composer 2, Node.js 22+, PostgreSQL 16+, Redis 7+, FFmpeg і
`heif-convert` з HEVC-декодером. На Ubuntu 24.04 останні два HEIF-пакети:

```bash
sudo apt-get install libheif-examples libheif-plugin-libde265
```

Без `heif-convert` адмінка покаже помилку під час обробки HEIC/HEIF; JPG, PNG і
WebP продовжать оброблятися через GD.

```bash
cp .env.example .env
composer install
npm ci
php artisan key:generate
```

Створити PostgreSQL database/user `lamari`, заповнити `DB_PASSWORD` у локальному
`.env`, після чого:

```bash
php artisan migrate:fresh --seed
npm run build:ssr
php artisan serve
```

Для SSR у другому процесі:

```bash
php artisan inertia:start-ssr
```

Адмінпанель: `http://127.0.0.1:8000/admin`. Локальний seed-користувач:
`admin@lamari.test` / `password`. Цей обліковий запис існує тільки для dev/test і
має бути видалений до будь-якого зовнішнього розгортання.

Швидкий isolated test-run використовує in-memory SQLite відповідно до
`phpunit.xml`:

```bash
php artisan test
```

## Модулі MVP

- Catalog: категорії, товари, варіанти, серверні ціни, залишок/резерв.
- Catalog taxonomy: ієрархія розділів чинного Lamari (верхні розділи й
  підкатегорії матеріалів/типів).
- Product media: впорядкована змішана галерея фото та MP4/WebM відео з poster,
  alt, active state і керуванням через Filament.
- Cart: session cart для гостя.
- Cart drawer: auto-open після додавання, quantity controls, remove, subtotal і
  адаптивний off-canvas UX без переходу зі сторінки товару.
- Checkout: guest order у транзакції з фіксацією merchant/legal entity.
- Payments: `PaymentProvider`, fake adapter, підписаний idempotent callback.
- Merchant routing: кілька ФОП/merchant accounts і JSON-правила сум.
- Admin: Filament resources для товарів/варіантів і замовлень.
- SEO: SSR build, metadata, canonical, Open Graph, Product/Offer JSON-LD,
  sitemap і conservative robots rule для URL з фільтрами.
- QA: feature tests і Playwright screenshot smoke flow.

## Перевірені команди

- `npm run build:ssr` — успішно.
- `php artisan test` — 6 tests, 22 assertions, усі успішні.
- HTTP smoke — storefront `200`, admin redirect-to-login `302`.

## Невирішені питання перед наступним етапом

1. Юридичні назви/ІПН ФОПів і точні правила merchant routing (сума, категорія,
   чергування, ліміти обороту чи ручний пріоритет).
2. Контракт plata by mono, точний callback schema/signature і sandbox credentials.
3. Остаточні доставка, податкова/фіскальна схема та необхідність ПРРО.
4. Брендбук: логотип, шрифти, палітра, фотостиль і реальний каталог.
   Поточний seed містить структуру каталогу й один демонстраційний товар;
   повний контрольований імпорт чинного каталогу виконується окремим етапом.
5. Політики повернення, приватності, cookies та строки зберігання PII.
6. Правила індексації кожної комбінації фільтрів після затвердження фасетів.
7. Чи потрібні резерв timeout і автоматичне звільнення залишку через queue job.

## Поточний staging

Тестове середовище розгорнуто 2026-07-29 на окремому сервері. Воно використовує
Nginx, PHP-FPM, SQLite, fake payment і тестовий каталог. Доступ захищено HTTP
Basic Auth, а індексацію заборонено через `X-Robots-Tag` та `robots.txt`.
Облікові дані зберігаються лише в серверному `.env` і не входять до репозиторію.

Основний домен і production не змінювалися. Їх публікація потребує окремого
підтвердження власника.
