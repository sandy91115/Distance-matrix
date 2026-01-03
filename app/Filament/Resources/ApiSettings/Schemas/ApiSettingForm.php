<?php

namespace App\Filament\Resources\ApiSettings\Schemas;

use Filament\Schemas\Schema;
use App\Filament\Resources\ApiSettingResource\Pages;
use App\Models\ApiSetting;
use Filament\Forms;

use Filament\Schemas\Components\Section;

use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class ApiSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
                  Section::make('Google Maps API Configuration')
                ->description('Configure your Google Maps API key with Places, Directions, and Distance Matrix APIs enabled')
                ->schema([
                    Forms\Components\TextInput::make('key')
                        ->label('Setting Key')
                        ->required()
                        ->default('google_maps_api_key')
                        ->disabled(fn ($record) => $record !== null),
                    
                    Forms\Components\Textarea::make('value')
                        ->label('API Key')
                        ->rows(2)
                        ->required()
                        ->placeholder('AIzaSy...'),
                    
                    Forms\Components\Toggle::make('is_encrypted')
                        ->label('Encrypt API Key')
                        ->default(true)
                        ->disabled(),
                ])
        
            ]);
    }
}
