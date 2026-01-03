<?php

namespace App\Filament\Resources\ApiSettings\Pages;

use App\Filament\Resources\ApiSettings\ApiSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApiSetting extends CreateRecord
{
    protected static string $resource = ApiSettingResource::class;
}
