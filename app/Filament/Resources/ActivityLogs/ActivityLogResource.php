<?php

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function getNavigationLabel(): string
    {
        return 'Історія змін';
    }

    public static function getModelLabel(): string
    {
        return 'запис історії';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Історія змін';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Коли')->dateTime('d.m.Y H:i:s')->sortable(),
                TextColumn::make('causer.name')->label('Хто')->placeholder('Система')->searchable(),
                TextColumn::make('event')->label('Дія')->badge()->formatStateUsing(fn (?string $state): string => match ($state) {
                    'created' => 'Створено',
                    'updated' => 'Змінено',
                    'deleted' => 'Видалено',
                    'restored' => 'Відновлено',
                    default => $state ?: 'Інше',
                }),
                TextColumn::make('subject_type')->label('Розділ')->formatStateUsing(fn (?string $state): string => class_basename((string) $state))->searchable(),
                TextColumn::make('subject_id')->label('ID')->sortable(),
                TextColumn::make('properties')->label('Що змінено')->formatStateUsing(function ($state): string {
                    $properties = $state instanceof Collection ? $state->all() : (array) $state;
                    $attributes = (array) ($properties['attributes'] ?? []);
                    $old = (array) ($properties['old'] ?? []);

                    return collect($attributes)->map(function ($value, string $field) use ($old): string {
                        $before = self::displayValue($old[$field] ?? null);
                        $after = self::displayValue($value);

                        return e($field).': '.e($before).' → '.e($after);
                    })->implode('<br>') ?: '—';
                })->html()->wrap(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListActivityLogs::route('/')];
    }

    private static function displayValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'так' : 'ні';
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return Str::limit((string) ($value ?? '—'), 120);
    }
}
