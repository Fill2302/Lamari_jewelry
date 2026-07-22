# Структура бази даних

PostgreSQL, UUID/ULID для публічних сутностей, `timestamptz`, суми у мінімальних
одиницях (`*_amount` integer/bigint), валюта ISO 4217. Видалення комерційних
документів заборонене: використовуються статуси й audit log.

## Доступ і клієнти

- `users`: id, name, email, phone, password, email_verified_at, phone_verified_at,
  locale, marketing_consent_at, last_login_at, timestamps, soft_delete.
- `addresses`: id, user_id nullable, type, recipient_name, phone, country_code,
  region, city, postal_code, line1, line2, delivery_data jsonb, is_default.
- `admin_users`: id, name, email, password, two_factor_secret, active,
  last_login_at, timestamps.
- `roles`, `permissions`, `role_user`, `permission_role`.
- `admin_activity_logs`: actor_id, action, subject_type/id, before/after jsonb,
  ip_hash, created_at.

## Каталог

- `categories`: id, parent_id, name, slug, description, image_id, position,
  is_active, seo_title, seo_description.
- `collections`: id, name, slug, description, image_id, starts_at, ends_at,
  is_active, position, seo fields.
- `products`: id, name, slug, description, care_text, status, brand, tax_code,
  default_variant_id nullable, published_at, seo fields, timestamps, soft_delete.
- `product_variants`: id, product_id, sku unique, barcode nullable, title,
  price_amount, compare_at_amount nullable, cost_amount nullable, currency,
  weight_grams, attributes jsonb, is_active.
- `attributes`: id, code unique, name, type, is_filterable, position.
- `attribute_values`: id, attribute_id, value, label, position.
- `product_attribute_value`: product_id, variant_id nullable, attribute_value_id.
- `category_product`, `collection_product`: relation ids, position.
- `media`: id, disk, path, mime_type, width, height, alt, metadata jsonb.
- `mediables`: media_id, mediable_type/id, role, position.
- `product_relations`: product_id, related_product_id, type, position.

## Залишки

- `inventory_locations`: id, name, code, address jsonb, active.
- `inventory_levels`: variant_id, location_id, on_hand, reserved, safety_stock,
  version; unique(variant_id, location_id).
- `inventory_movements`: id, variant_id, location_id, order_id nullable, type,
  quantity, reason, actor_type/id, idempotency_key unique, created_at.

`available = on_hand - reserved - safety_stock`; резервування виконується в
транзакції з row lock або optimistic version check.

## Кошик і checkout

- `carts`: id, user_id nullable, session_token_hash, currency, coupon_code,
  expires_at, timestamps.
- `cart_items`: id, cart_id, variant_id, quantity, unit_price_snapshot,
  metadata jsonb; unique(cart_id, variant_id, metadata_hash).
- `checkout_sessions`: id, cart_id, user_id nullable, email, phone,
  billing_address jsonb, shipping_address jsonb, shipping_method,
  shipping_quote jsonb, totals jsonb, idempotency_key unique, expires_at.

## Замовлення, оплати й доставка

- `orders`: id, number unique, user_id nullable, email, phone, status,
  payment_status, fulfillment_status, currency, subtotal_amount,
  discount_amount, shipping_amount, tax_amount, total_amount, customer_note,
  billing_address jsonb, shipping_address jsonb, placed_at, timestamps.
- `order_items`: id, order_id, product_id nullable, variant_id nullable, sku,
  name, variant_title, quantity, unit_price_amount, discount_amount, tax_amount,
  total_amount, product_snapshot jsonb.
- `order_status_history`: id, order_id, from_status, to_status, actor_type/id,
  note, created_at.
- `payments`: id, order_id, provider, provider_payment_id, status, amount,
  currency, payment_method, idempotency_key unique, payload jsonb, timestamps.
- `payment_transactions`: id, payment_id, type, provider_transaction_id,
  status, amount, payload jsonb, processed_at.
- `refunds`: id, order_id, payment_id, status, amount, reason, provider_ref,
  processed_at.
- `shipments`: id, order_id, provider, service, tracking_number, status,
  label_url, shipped_at, delivered_at, payload jsonb.
- `shipment_items`: shipment_id, order_item_id, quantity.
- `webhook_events`: id, provider, external_id, event_type, signature_valid,
  payload jsonb, status, attempts, processed_at; unique(provider, external_id).

## Промо, контент і системні таблиці

- `promotions`: id, name, type, value, conditions jsonb, starts_at, ends_at,
  usage_limit, per_customer_limit, is_active.
- `coupons`: id, promotion_id, code unique, usage_limit, used_count.
- `promotion_redemptions`: promotion_id, coupon_id nullable, order_id, user_id
  nullable, discount_amount, created_at.
- `pages`: id, title, slug unique, content jsonb, status, published_at, seo fields.
- `content_blocks`: id, placement, type, payload jsonb, starts_at, ends_at,
  position, is_active.
- `redirects`: from_path unique, to_path, status_code, active.
- Laravel service tables: `jobs`, `failed_jobs`, `cache`, `sessions`,
  `notifications`, `password_reset_tokens`.

## Ключові індекси й обмеження

- Unique: product/category/page slugs, SKU, order number, coupon code.
- Partial indexes для активних/опублікованих товарів і незавершених замовлень.
- GIN/trigram для пошуку назв, SKU й описів; GIN для вибраних JSONB полів.
- Check constraints: невід'ємні суми/кількості; дозволені переходи статусів у коді.
- PII не дублюється в логах; snapshots у замовленні потрібні для історичної
  точності навіть після редагування товару.

