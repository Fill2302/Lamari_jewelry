<?php

namespace App\Filament\Resources\ProductCardSettings\Pages;

use App\Filament\Resources\ProductCardSettings\ProductCardSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListProductCardSettings extends ListRecords
{
    protected static string $resource = ProductCardSettingResource::class;

    public function mount(): void
    {
        parent::mount();
        if ($record = ProductCardSettingResource::getModel()::query()->first()) {
            $this->redirect(ProductCardSettingResource::getUrl('edit', ['record' => $record]));
        }
    }
}
