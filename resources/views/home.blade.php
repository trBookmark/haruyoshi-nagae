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
              srcset="{{ $setting->image->srcset(['medium', 'large']) }}"
              sizes="100vw"
              alt="{{ $setting->image_alt ?? '' }}"
              fetchpriority="high"
              decoding="async">
          </a>
        @else
          <img class="p-top__img"
            src="{{ $setting->image->imageUrl('medium') }}"
            srcset="{{ $setting->image->srcset(['medium', 'large']) }}"
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
      <p class="p-top__copyright">
        <img class="p-top__icon" src="{{ Vite::asset('resources/images/icon.png') }}" width="24" height="24" alt="">
        <small>&copy; {{ date('Y') }} {{ config('app.name') }}</small>
      </p>
    </section>
  </div>
@endsection
