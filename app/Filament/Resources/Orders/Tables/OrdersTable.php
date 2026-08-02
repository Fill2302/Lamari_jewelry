<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->label('Номер замовлення')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->label('ID користувача')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('merchantAccount.code')->label('Магазин'),
                TextColumn::make('legal_entity_id')
                    ->label('ID юридичної особи')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Електронна пошта')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Ім’я')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Прізвище')
                    ->searchable(),
                TextColumn::make('marketing_attribution.last_touch.utm_source')
                    ->label('UTM джерело')
                    ->placeholder('Прямий перехід')
                    ->toggleable(),
                TextColumn::make('marketing_attribution.last_touch.utm_campaign')
                    ->label('UTM кампанія')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('marketing_attribution.last_touch.gclid')
                    ->label('Google Click ID')
                    ->limit(18)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Статус замовлення')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_payment' => 'Очікує оплати',
                        'confirmed' => 'Підтверджено',
                        'processing' => 'В обробці',
                        'shipped' => 'Відправлено',
                        'completed' => 'Виконано',
                        'cancelled' => 'Скасовано',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('payment_status')
                    ->label('Статус оплати')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Очікує оплати',
                        'paid' => 'Оплачено',
                        'cash_on_delivery' => 'Оплата при отриманні',
                        'failed' => 'Помилка оплати',
                        'refunded' => 'Повернено',
                        default => $state,
                    })
                    ->searchable(),
                TextColumn::make('subtotal_amount')
                    ->label('Сума товарів, грн')
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2, '.', ' '))
                    ->sortable(),
                TextColumn::make('total_amount')->label('Разом, грн')
                    ->formatStateUsing(fn ($state) => number_format($state / 100, 2, '.', ' '))
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Валюта')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
