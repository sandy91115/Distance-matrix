<?php

namespace App\Filament\Resources\ApiSettings;

use App\Filament\Resources\ApiSettings\Pages\CreateApiSetting;
use App\Filament\Resources\ApiSettings\Pages\EditApiSetting;
use App\Filament\Resources\ApiSettings\Pages\ListApiSettings;
use App\Filament\Resources\ApiSettings\Pages\ViewApiSetting;
use App\Filament\Resources\ApiSettings\Schemas\ApiSettingForm;
use App\Filament\Resources\ApiSettings\Schemas\ApiSettingInfolist;
use App\Filament\Resources\ApiSettings\Tables\ApiSettingsTable;
use App\Models\ApiSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ApiSettingResource extends Resource
{
    protected static ?string $model = ApiSetting::class;
    
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'API Setting';

    public static function form(Schema $schema): Schema
    {
        return ApiSettingForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ApiSettingInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ApiSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListApiSettings::route('/'),
            'create' => CreateApiSetting::route('/create'),
            'view' => ViewApiSetting::route('/{record}'),
            'edit' => EditApiSetting::route('/{record}/edit'),
        ];
    }
}
