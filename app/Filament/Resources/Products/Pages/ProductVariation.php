<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Enums\ProductVariationTypeEnum;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use BackedEnum;
use Illuminate\Database\Eloquent\Model;


class ProductVariation extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Variation Product';
    protected static ?string $navigationLabel = 'Variation';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public function form(Schema $schema): Schema
    {
        $types = $this->record->variationTypes;
        $fields = [];
        foreach ($types as $type) {
            $fields[] = TextInput::make('variantion_type_' . ($type->id) . '.id')
                ->hidden();
            $fields[] = TextInput::make('variantion_type_' . ($type->id) . '.name')
                ->label($type->name);
        }

        return $schema->components([
            Repeater::make('variations')
            ->hiddenLabel()
            ->collapsible()
            ->addable(false)
            ->defaultItems(1)
            ->columns(2)
            ->columnSpan(2)
            ->schema([
                Section::make()
                    ->schema($fields)
                    ->columnSpanFull(),
                TextInput::make('quantity')
                    ->label('Quantity')
                    ->numeric(),
                TextInput::make('price')
                    ->label('Price')
                    ->numeric()
            ])
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array{
        $variantions = $this->record->variations->toArray();
        $data['variations'] = $this->mergeCartesianWithExisting($this->record->variationTypes, $variantions);


        return $data;
    }

    private function mergeCartesianWithExisting($variationTypes, $existingData){
        $defaultQuantity = $this->record->quality;
        $defaultPrice = $this->record->price;
        $cartesianProduct = $this->cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice);
        $mergedResult = [];

        foreach ($cartesianProduct as $product) {
            $optionIds = collect($product)
                ->filter(fn($value, $key) => str_starts_with($key,  'varianton_type'))
                ->map(fn($option) => $option['id'])
                ->values()
                ->toArray();

                $match = array_filter($existingData, function ($existingOption) use ($optionIds) {
                    return $existingOption['variation_type_option_ids' === $optionIds];
                });

                if (!empty($match)) {
                    $existingEntry = reset($match);
                    $product['id'] = $existingEntry['id'];
                    $product['quantity'] = $existingEntry['quantity'];
                    $product['price'] = $existingEntry['price'];
                } else {
                    $product['quantity'] = $defaultQuantity;
                    $product['price'] = $defaultPrice; 
                }

            $mergedResult[] = $product;
        }
        return $mergedResult;
    }

    private function cartesianProduct($variationTypes, $defaultQuantity = null, $defaultPrice = null) {
        $result = [[]];
        
        foreach ($variationTypes as $index => $variationType) {
            $temp = [];
            foreach ($variationType->options as $option) {
                foreach ($result as $combination) {
                    $newCombination = $combination + [
                        'variantion_type_' . ($variationType->id) => [
                            'id' => $option->id,
                            'name' => $option->name,
                            'label' => $variationType->name,
                        ],
                    ];
                    $temp[] = $newCombination;
                }
            }
            $result = $temp;
        }

        // Add default quantity and price to complete combinations
        foreach ($result as &$combination) {
            if (count($combination) === count($variationTypes)){
                $combination['quantity'] = $defaultQuantity;
                $combination['price'] = $defaultPrice;
            }
        }
        
        return $result;
    }

    protected function mutateFormDataBeforeSave(array $data): array {
        $formattedData = []; 

        foreach ($data['variations'] as $option) {
            $variationTypeOptionIds = [];
            foreach ($this->record->variationTypes as $i => $variationType) {
                $variationTypeOptionIds[] = $option['variantion_type_' . ($variationType->id)]['id'];
            }
            $quantity = $option['quantity'];
            $price = $option['price'];
            $id = $option['id'];

            $formatedData[] = [
                'id' => $id,
                'variation_type_option_ids' => $variationTypeOptionIds,
                'quantity' => $quantity,
                'price' => $price,
            ];
        }
        $data['variations'] = $formatedData;
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model {
        $variations = $data['variations'];
        unset($data['variations']);

        $variations = collect($variations)->map(function ($variation) {
            return [
                'id' => $variation['id'],
                'variation_type_option_ids' => json_encode($variation['variation_type_option_ids']),
                'quantity' => $variation['quantity'],
                'price' => $variation['price'],
            ];
        })->toArray();

        $record->variations()->upsert($variations, ['id'], ['variation_type_option_ids', 'quantity', 'price']);
        
        return $record;
    }
}
