<?php

namespace App\Filament\Resources\Discounts\Pages;

use App\Filament\Resources\Discounts\DiscountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['product_id'] = null;
        $data['category_id'] = null;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->products()->sync(($this->data['scope'] ?? null) === 'product' ? ($this->data['product_ids'] ?? []) : []);
        $this->record->categories()->sync(($this->data['scope'] ?? null) === 'category' ? ($this->data['category_ids'] ?? []) : []);
    }
}
