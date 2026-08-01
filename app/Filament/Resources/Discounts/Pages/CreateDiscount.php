<?php

namespace App\Filament\Resources\Discounts\Pages;

use App\Filament\Resources\Discounts\DiscountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['scope'] ?? 'all') !== 'product') $data['product_id'] = null;
        if (($data['scope'] ?? 'all') !== 'category') $data['category_id'] = null;
        return $data;
    }
}
