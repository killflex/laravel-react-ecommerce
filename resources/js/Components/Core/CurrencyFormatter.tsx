export default function CurrencyFormatter({
  amount,
  currency = "USD",
  locale,
}: {
  amount: number;
  currency?: string;
  locale?: string;
}) {
  return new Intl.NumberFormat(locale || undefined, {
    style: "currency",
    currency,
  }).format(amount);
}
