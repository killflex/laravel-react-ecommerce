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


class ProductVariation extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $title = 'Variation Types Product';
    protected static ?string $navigationLabel = 'Variation Types';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            
        ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variantions = $this->record->variations->toArray();
        $data['variations'] = $this->mergeCartesianWithExisting($this->record->variationTypes, $variantions);


        return $data;
    }

    private function mergeCartesianWithExisting($variationTypes, $existingData)
    {
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
            $tmp = [];
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
            $result = $tmp;
        }
        
        return $result;
    }

}
