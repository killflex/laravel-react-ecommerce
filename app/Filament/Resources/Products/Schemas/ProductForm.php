<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Enums\ProductStatusEnum;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;


class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->live(onBlur: true)
                    ->required()
                    ->afterStateUpdated(function (TextInput $component, ?string $state, callable $set) {
                        if ($state) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                TextInput::make('slug')
                    ->required(),
                Select::make('department_id')
                    ->relationship('department', 'name')
                    ->label(__('Department'))
                    ->required()
                    ->preload()
                    ->reactive()
                    ->searchable()
                    ->afterStateUpdated(function (callable $set) {
                        $set('category_id', null); // Reset category when department changes
                    }),
                Select::make('category_id')
                    ->relationship(name: 'category', titleAttribute: 'name', modifyQueryUsing: function (Builder $query, callable $get) {
                        $departmentId = $get('department_id');
                        if ($departmentId) {
                            $query->where('department_id', $departmentId);
                        }
                        return $query; 
                    })
                    ->required()
                    ->preload()
                    ->reactive()
                    ->searchable(),
                RichEditor::make('description')
                    ->required()
                    ->columnSpan('2')
                    ->toolbarButtons([
                        'blockquote',
                        'bold',
                        'bulletList',
                        'codeBlock',
                        'h2',
                        'h3',
                        'italic',
                        'link',
                        'orderedList',
                        'redo',
                        'strike',
                        'subscript',
                        'superscript',
                        'underline',
                        'undo'
                    ]),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('$'),
                TextInput::make('quantity')
                    ->numeric(),
                Select::make('status')
                    ->options(ProductStatusEnum::labels())
                    ->default(ProductStatusEnum::Draft->value)
                    ->required(),
                // SpatieMediaLibraryFileUpload::make('images')
                //     ->image()
                //     ->multiple()
                //     ->openable()
                //     ->panelLayout('grid')
                //     ->collection('images')
                //     ->reorderable()
                //     ->appendFiles()
                //     ->preserveFilenames()
                //     ->label('Product Images')
                //     ->columnSpan(2)
                ]);
            ;}
}
