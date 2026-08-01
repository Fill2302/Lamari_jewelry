<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Номер замовлення')
                    ->required(),
                TextInput::make('user_id')
                    ->label('ID користувача')
                    ->numeric(),
                Select::make('merchant_account_id')
                    ->label('Магазин')
                    ->relationship('merchantAccount', 'id')
                    ->required(),
                TextInput::make('legal_entity_id')
                    ->label('ID юридичної особи')
                    ->required()
                    ->numeric(),
                TextInput::make('email')
                    ->label('Електронна пошта')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->tel()
                    ->required(),
                TextInput::make('first_name')
                    ->label('Ім’я')
                    ->required(),
                TextInput::make('last_name')
                    ->label('Прізвище')
                    ->required(),
                Textarea::make('shipping_address')
                    ->label('Адреса доставки')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('status')
                    ->label('Статус замовлення')
                    ->required()
                    ->default('pending_payment'),
                TextInput::make('payment_status')
                    ->label('Статус оплати')
                    ->required()
                    ->default('pending'),
                TextInput::make('subtotal_amount')
                    ->label('Сума товарів, коп.')
                    ->required()
                    ->numeric(),
                TextInput::make('total_amount')
                    ->label('Разом, коп.')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->label('Валюта')
                    ->required()
                    ->default('UAH'),
            ]);
    }
}
