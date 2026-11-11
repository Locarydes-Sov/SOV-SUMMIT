@php
    /**
     * The API "price" is the price BEFORE discount. LocalRydes charges
     * price - discount (fixed) or price - price * discount / 100 (percentage).
     *
     * @var array<string, mixed> $offer
     */
    $symbol = data_get($offer, 'sourceCurrency.symbol') ?: data_get($offer, 'sourceCurrency.isoCode', '');
    $price = (float) data_get($offer, 'price', 0);
    $discount = (float) data_get($offer, 'discount', 0);
    $isPercent = data_get($offer, 'discountType') !== 'fixed';
    $discountAmount = $discount > 0 ? ($isPercent ? $price * $discount / 100 : $discount) : 0;
    $finalPrice = max(0, round($price - $discountAmount, 2));
@endphp
<span class="offer-price">
  <span class="offer-price__amount">{{ $symbol }}{{ number_format($finalPrice, 2) }}</span>
  @if ($discountAmount > 0)
    <s class="offer-price__old">{{ $symbol }}{{ number_format($price, 2) }}</s>
    <span class="offer-price__save">Save {{ $isPercent ? rtrim(rtrim(number_format($discount, 2), '0'), '.').'%' : $symbol.number_format($discount, 2) }}</span>
  @endif
</span>
