<?php

namespace App\Filament\Resources\PaymentRoutingSettings;

use App\Filament\Resources\PaymentRoutingSettings\Pages\EditPaymentRoutingSetting;
use App\Filament\Resources\PaymentRoutingSettings\Pages\ListPaymentRoutingSettings;
use App\Models\PaymentRoutingSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentRoutingSettingResource extends Resource
{
    protected static ?string $model = PaymentRoutingSetting::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    public static function getNavigationLabel(): string { return 'Розподіл оплат'; }
    public static function getModelLabel(): string { return 'розподіл оплат'; }
    public static function getPluralModelLabel(): string { return 'Розподіл оплат'; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Змішаний кошик')
                ->description('Застосовується, коли в одному замовленні одночасно є товари ФОП-2 і ФОП-3.')
                ->schema([
                    Radio::make('mixed_cart_destination')
                        ->label('Куди направляти всю оплату')
                        ->options([
                            'mono' => 'ФОП-2',
                            'privat' => 'ФОП-3',
                        ])
                        ->default('privat')
                        ->inline()
                        ->required(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('mixed_cart_destination')->label('Банк для змішаного кошика')
                ->formatStateUsing(fn (string $state): string => $state === 'mono' ? 'ФОП-2' : 'ФОП-3'),
        ])->recordActions([EditAction::make()]);
    }

    public static function canCreate(): bool { return PaymentRoutingSetting::query()->doesntExist(); }
    public static function canDelete($record): bool { return false; }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentRoutingSettings::route('/'),
            'edit' => EditPaymentRoutingSetting::route('/{record}/edit'),
        ];
    }
}
