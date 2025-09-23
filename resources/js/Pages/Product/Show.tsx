import { useMemo, useState } from "react";
import { Product, VariationType, VariationTypeOption } from "@/types";
import { useForm, usePage } from "@inertiajs/react";

export default function Show({
  product,
  variationOptions,
}: {
  product: Product;
  variationOptions: number[];
}) {
  const form = useForm<{
    option_ids: Record<string, number>;
    quantity: number;
    price: number | null;
  }>({
    option_ids: {},
    quantity: 1,
    price: null,
  });

  const { url } = usePage();

  const [selectedOptions, setSelectedOptions] = useState<
    Record<number, VariationTypeOption>
  >([]);

  const images = useMemo(() => {
    for (let typeId in selectedOptions) {
      const option = selectedOptions[typeId];
      if (Array.isArray(option.images) && option.images.length > 0)
        return option.images;
    }
    return product.images;
  }, [product, selectedOptions]);

  const computedProduct = useMemo(() => {
    const selectedOptionsIds = Object.values(selectedOptions)
      .map((op) => op.id)
      .sort();
  }, [product, selectedOptions]);

  return <div>Show</div>;
}
