<?php
namespace App\Filament\Resources\PromoCodes;

use App\Filament\Resources\PromoCodes\Pages\CreatePromoCode;
use App\Filament\Resources\PromoCodes\Pages\EditPromoCode;
use App\Filament\Resources\PromoCodes\Pages\ListPromoCodes;
use App\Models\PromoCode;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PromoCodeResource extends Resource
{
    protected static ?string $model=PromoCode::class;
    protected static string|BackedEnum|null $navigationIcon=Heroicon::OutlinedTicket;
    public static function getNavigationLabel(): string { return 'Промокоди й знижки'; }
    public static function getModelLabel(): string { return 'промокод'; }
    public static function getPluralModelLabel(): string { return 'Промокоди й знижки'; }
    public static function form(Schema $schema): Schema { return $schema->components([
        TextInput::make('code')->label('Код')->required()->unique(ignoreRecord:true),
        Select::make('discount_type')->label('Тип знижки')->options(['percent'=>'Відсоток','fixed'=>'Фіксована сума'])->required(),
        TextInput::make('discount_value')->label('Значення')->numeric()->required(),
        TextInput::make('minimum_order_amount')->label('Мінімальна сума, коп.')->numeric(),
        TextInput::make('usage_limit')->label('Ліміт використань')->numeric(),
        DateTimePicker::make('starts_at')->label('Початок дії'),
        DateTimePicker::make('ends_at')->label('Кінець дії'),
        Toggle::make('is_active')->label('Активний')->default(true),
    ]); }
    public static function table(Table $table): Table { return $table->columns([
        TextColumn::make('code')->label('Код')->searchable(),
        TextColumn::make('discount_type')->label('Тип')->formatStateUsing(fn($state)=>$state==='percent'?'Відсоток':'Сума'),
        TextColumn::make('discount_value')->label('Знижка'),
        TextColumn::make('used_count')->label('Використано'),
        TextColumn::make('ends_at')->label('Діє до')->dateTime('d.m.Y H:i'),
        IconColumn::make('is_active')->label('Активний')->boolean(),
    ])->recordActions([EditAction::make()]); }
    public static function getPages(): array { return ['index'=>ListPromoCodes::route('/'),'create'=>CreatePromoCode::route('/create'),'edit'=>EditPromoCode::route('/{record}/edit')]; }
}
