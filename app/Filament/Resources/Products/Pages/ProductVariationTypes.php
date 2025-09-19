<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Enums\ProductVariationTypeEnum;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use BackedEnum;


class ProductVariationTypes extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Variation Types Product';
    protected static ?string $navigationLabel = 'Variation Types';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-m-list-bullet';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Repeater::make('variationTypes')
                ->disableLabel()
                ->relationship()
                ->collapsible()
                ->defaultItems(1)
                ->addActionLabel('Add New Variation Type')
                ->columns(2)
                ->columnSpan(2)
                ->schema([
                    TextInput::make('name')
                    ->required(),
                    Select::make('type')
                    ->required()
                    ->options(ProductVariationTypeEnum::labels()),
                    Repeater::make('options')
                        ->relationship()
                        ->collapsible()
                        ->defaultItems(1)
                        ->columns(2)
                        ->columnSpan(2)
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->columnSpanFull(),
                            SpatieMediaLibraryFileUpload::make('images')
                                ->image()
                                ->multiple()
                                ->openable()
                                ->panelLayout('grid')
                                ->collection('images')
                                ->reorderable()
                                ->appendFiles()
                                ->preserveFilenames()
                                ->columnSpanFull(),
                        ]),
                ]),
        ]);
    }
}
