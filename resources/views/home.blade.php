@extends('layouts.app')

@section('description', str_replace("\n", '', $setting->description))

@if ($setting->image)
  @section('og-image', $setting->image->imageUrl('large'))
@endif

@section('content')
  <div class="p-top">
    @if ($setting->image)
      <figure class="p-top__feature">
        @if (Route::has('gallery.index'))
          <a class="p-top__feature-link" href="{{ route('gallery.index') }}" aria-label="ギャラリーへ移動">
            <img class="p-top__img"
              src="{{ $setting->image->imageUrl('medium') }}"
              srcset="{{ $setting->image->imageUrl('medium') }} 600w, {{ $setting->image->imageUrl('large') }} 1920w"
              sizes="100vw"
              alt="{{ $setting->image_alt ?? '' }}"
              fetchpriority="high"
              decoding="async">
          </a>
        @else
          <img class="p-top__img"
            src="{{ $setting->image->imageUrl('medium') }}"
            srcset="{{ $setting->image->imageUrl('medium') }} 600w, {{ $setting->image->imageUrl('large') }} 1920w"
            sizes="100vw"
            alt="{{ $setting->image_alt ?? '' }}"
            fetchpriority="high"
            decoding="async">
        @endif
      </figure>
    @endif

    <section class="p-top__text">
      <h1 class="p-top__title">Top</h1>
      <p class="p-top__msg">{{ $setting->description }}</p>
    </section>
  </div>
@endsection
