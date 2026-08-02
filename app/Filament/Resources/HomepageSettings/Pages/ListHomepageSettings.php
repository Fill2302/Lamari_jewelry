<?php

namespace App\Filament\Resources\HomepageSettings\Pages;

use App\Filament\Resources\HomepageSettings\HomepageSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListHomepageSettings extends ListRecords
{
    protected static string $resource = HomepageSettingResource::class;

    public function mount(): void
    {
        parent::mount();
        if ($record = HomepageSettingResource::getModel()::query()->first()) {
            $this->redirect(HomepageSettingResource::getUrl('edit', ['record' => $record]));
        }
    }
}
