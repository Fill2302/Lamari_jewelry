<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Throwable;

class TelegramOrderNotifier
{
    public function enabled(): bool
    {
        return (bool) config('services.telegram_orders.enabled')
            && filled(config('services.telegram_orders.bot_token'))
            && filled(config('services.telegram_orders.chat_id'));
    }

    public function notifyCreated(Order $order): void
    {
        $this->safelySend($this->createdMessage($order));
    }

    public function notifyPaid(Payment $payment): void
    {
        $order = $payment->order;
        $this->safelySend(implode("\n", [
            '✅ <b>Оплачено</b>',
            '<b>Замовлення:</b> '.$this->escape($order->number),
            '<b>Сума:</b> '.$this->money($payment->amount),
            '<b>Джерело:</b> '.$this->escape((string) config('services.telegram_orders.source')),
        ]));
    }

    private function createdMessage(Order $order): string
    {
        $order->loadMissing('items');
        $shipping = $order->shipping_address ?? [];
        $items = $order->items->map(function ($item, int $index): string {
            return implode("\n", [
                ($index + 1).'. <b>'.$this->escape($item->name).'</b>',
                'Артикул: '.$this->escape($item->sku),
                'Ціна: '.$this->money($item->unit_price_amount).' × '.$item->quantity,
            ]);
        })->implode("\n\n");

        return implode("\n", [
            '🛍 <b>Нове замовлення</b>',
            '<b>Номер:</b> '.$this->escape($order->number),
            '<b>Джерело:</b> '.$this->escape((string) config('services.telegram_orders.source')),
            '<b>Статус оплати:</b> '.($order->payment_status === 'cash_on_delivery' ? 'Оплата при отриманні' : 'Очікує оплати'),
            '',
            $items,
            '',
            '<b>Загальна сума:</b> '.$this->money($order->total_amount),
            '<b>ПІБ:</b> '.$this->escape($order->customer_name),
            '<b>Email:</b> '.$this->escape($order->email),
            '<b>Телефон:</b> '.$this->escape($order->phone),
            '<b>Спосіб доставки:</b> Нова Пошта',
            '<b>Спосіб оплати:</b> '.($order->payment_method === 'cash_on_delivery' ? 'Оплата при отриманні' : 'Оплата карткою на сайті'),
            '<b>Місто:</b> '.$this->escape((string) ($shipping['city'] ?? '—')),
            '<b>Відділення/поштомат:</b> '.$this->escape((string) ($shipping['address'] ?? '—')),
            '<b>Примітка:</b> —',
        ]);
    }

    private function safelySend(string $message): void
    {
        if (! $this->enabled()) {
            return;
        }

        try {
            Http::asForm()
                ->timeout(8)
                ->retry(2, 200)
                ->post('https://api.telegram.org/bot'.config('services.telegram_orders.bot_token').'/sendMessage', [
                    'chat_id' => config('services.telegram_orders.chat_id'),
                    'text' => $message,
                    'parse_mode' => 'HTML',
                    'disable_web_page_preview' => true,
                ])->throw();
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function money(int $amount): string
    {
        return number_format($amount / 100, 2, ',', ' ').' грн';
    }

    private function escape(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
