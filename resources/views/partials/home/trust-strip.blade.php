@php
    $heroStats = [
        ['value' => '550+', 'label' => 'Events delivered'],
        ['value' => '30+',  'label' => 'Countries'],
        ['value' => '20+',  'label' => 'Heads of state supported'],
    ];
@endphp
<section class="trust-strip" aria-label="SOV SUMMIT in numbers">
  <ul class="trust-strip__stats">
    @foreach ($heroStats as $stat)
      <li class="trust-strip__stat">
        <span class="trust-strip__value">{{ $stat['value'] }}</span>
        <span class="trust-strip__label">{{ $stat['label'] }}</span>
      </li>
    @endforeach
  </ul>
</section>
