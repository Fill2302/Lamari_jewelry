<?php

namespace App\Filament\Resources\Discounts\Pages;

use App\Filament\Resources\Discounts\DiscountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscount extends EditRecord
{
    protected static string $resource = DiscountResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['product_ids'] = $this->record->products()->pluck('products.id')->all();
        $data['category_ids'] = $this->record->categories()->pluck('categories.id')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['product_id'] = null;
        $data['category_id'] = null;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->products()->sync(($this->data['scope'] ?? null) === 'product' ? ($this->data['product_ids'] ?? []) : []);
        $this->record->categories()->sync(($this->data['scope'] ?? null) === 'category' ? ($this->data['category_ids'] ?? []) : []);
    }
}
