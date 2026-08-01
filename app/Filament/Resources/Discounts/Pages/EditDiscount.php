<?php

namespace App\Filament\Resources\Discounts\Pages;

use App\Filament\Resources\Discounts\DiscountResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDiscount extends EditRecord
{
    protected static string $resource = DiscountResource::class;
    protected function getHeaderActions(): array { return [DeleteAction::make()]; }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['scope'] ?? 'all') !== 'product') $data['product_id'] = null;
        if (($data['scope'] ?? 'all') !== 'category') $data['category_id'] = null;
        return $data;
    }
}
