@php
  $img = $p->getFirstMediaUrl('images', 'thumb') ?: 'https://placehold.co/600x400?text=Home';
  $price = number_format((float)$p->price, 0);
@endphp
<div class="card property h-100">
  <a href="{{ route('properties.show', $p->id) }}" class="text-decoration-none text-dark">
    <img src="{{ $img }}" class="thumb" alt="{{ $p->title }}">
    <div class="card-body" style="min-height: 120px;">
      <div class="d-flex justify-content-between align-items-start mb-1">
        <div class="fw-semibold" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 70%;">
          {{ Str::limit($p->title, 30) }}
        </div>
        <span class="pill text-muted">{{ $p->type->name ?? '—' }}</span>
      </div>
      <div class="text-muted small mb-2" style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
        {{ collect([$p->city, $p->state])->filter()->implode(', ') }}
      </div>
      <div class="fw-semibold">
        ₹ {{ $price }} <span class="text-muted">{{ $p->rent_type === 'daily' ? '/day' : '/month' }}</span>
      </div>
    </div>
  </a>
</div>