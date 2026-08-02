<?php

namespace App\Filament\Resources\PaymentRoutingSettings\Pages;

use App\Filament\Resources\PaymentRoutingSettings\PaymentRoutingSettingResource;
use App\Models\PaymentRoutingSetting;
use Filament\Resources\Pages\ListRecords;

class ListPaymentRoutingSettings extends ListRecords
{
    protected static string $resource = PaymentRoutingSettingResource::class;

    public function mount(): void
    {
        parent::mount();
        if ($record = PaymentRoutingSetting::query()->first()) {
            $this->redirect(PaymentRoutingSettingResource::getUrl('edit', ['record' => $record]));
        }
    }
}
