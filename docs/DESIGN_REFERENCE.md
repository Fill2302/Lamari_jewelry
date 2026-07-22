# Design reference і контентна міграція

## Напрям інтерфейсу

The Lace використовується лише як UX-референс: велика медіа-сітка, sticky purchase
panel, виразна типографіка, варіанти, характеристики та інформаційні секції.
Візуальна система, тексти й асортимент залишаються власними для Lamari.

- Reference: https://thelace.com.ua/
- Product reference: https://thelace.com.ua/shop/palto-zhaket-vovnianyyi-odnobortnyyi-warm-oatmeal/
- Source catalog: https://lamari.jewelry/shop/main

## Зафіксована структура Lamari

Sale; Літня колекція; Кольє; Чокери; Сережки; Ланцюжки; Браслети; Анклети;
Булавки; Каблучки; Комплекти. Підкатегорії з чинного сайту додані в seed як
ієрархічні категорії.

## Медіа товару

`product_media` підтримує `image` і `video`, порядок, poster, alt та visibility.
Filament дозволяє завантажувати JPEG/PNG/WebP і MP4/WebM. Вітрина відображає
змішану галерею; відео використовує native controls, muted/playsinline і poster.

## Повний імпорт

Перед повним імпортом потрібно узгодити одноразове перенесення чи повторювану
синхронізацію, зіставлення старих URL/ID/SKU, redirects, якість зображень і права
на кожен медіафайл. Імпорт не повинен публікувати або змінювати чинний сайт.
