<?php

namespace App\Filament\Resources\ApiSettings\Pages;

use App\Filament\Resources\ApiSettings\ApiSettingResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewApiSetting extends ViewRecord
{
    protected static string $resource = ApiSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
