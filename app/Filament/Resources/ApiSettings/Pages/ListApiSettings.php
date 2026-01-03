<?php

namespace App\Filament\Resources\ApiSettings\Pages;

use App\Filament\Resources\ApiSettings\ApiSettingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListApiSettings extends ListRecords
{
    protected static string $resource = ApiSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
