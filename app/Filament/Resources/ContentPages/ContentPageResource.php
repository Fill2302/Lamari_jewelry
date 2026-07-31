<?php
namespace App\Filament\Resources\ContentPages;

use App\Filament\Resources\ContentPages\Pages\CreateContentPage;
use App\Filament\Resources\ContentPages\Pages\EditContentPage;
use App\Filament\Resources\ContentPages\Pages\ListContentPages;
use App\Models\ContentPage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentPageResource extends Resource
{
    protected static ?string $model=ContentPage::class;
    protected static string|BackedEnum|null $navigationIcon=Heroicon::OutlinedDocumentText;
    public static function getNavigationLabel(): string { return 'Сторінки й меню'; }
    public static function getModelLabel(): string { return 'сторінку'; }
    public static function getPluralModelLabel(): string { return 'Сторінки й меню'; }
    public static function form(Schema $schema): Schema { return $schema->components([
        TextInput::make('title')->label('Заголовок')->required(),
        TextInput::make('slug')->label('Адреса (slug)')->required()->unique(ignoreRecord:true),
        RichEditor::make('content')->label('Вміст')->columnSpanFull(),
        TextInput::make('seo_title')->label('SEO-заголовок'),
        Textarea::make('seo_description')->label('SEO-опис'),
        Toggle::make('show_in_menu')->label('Показувати в меню'),
        TextInput::make('position')->label('Порядок')->numeric()->default(0),
        Toggle::make('is_active')->label('Активна')->default(true),
    ]); }
    public static function table(Table $table): Table { return $table->columns([
        TextColumn::make('title')->label('Заголовок')->searchable()->sortable(),
        TextColumn::make('slug')->label('Адреса'),
        IconColumn::make('show_in_menu')->label('У меню')->boolean(),
        TextColumn::make('position')->label('Порядок')->sortable(),
        IconColumn::make('is_active')->label('Активна')->boolean(),
    ])->defaultSort('position')->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]); }
    public static function getPages(): array { return ['index'=>ListContentPages::route('/'),'create'=>CreateContentPage::route('/create'),'edit'=>EditContentPage::route('/{record}/edit')]; }
}
