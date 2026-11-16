{{-- Renders FAQ items from App\Support\Faq. Answers are trusted HTML written in that class. --}}
<div class="faq-list">
  @foreach ($items as $item)
    <details class="faq-item">
      <summary>{{ $item['question'] }}</summary>
      {!! $item['answer'] !!}
    </details>
  @endforeach
</div>
