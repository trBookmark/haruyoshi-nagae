@extends('layouts.app')

@section('title', 'GALLERY')
@section('description', config('app.name') . ' GALLERY（CATEGORY LIST）')

@php
  $firstCover = $categories->firstWhere('coverImage')?->coverImage;
@endphp
@if ($firstCover)
  @section('og-image', $firstCover->imageUrl('large'))
@endif

@section('content')
  <nav class="c-breadcrumb" aria-label="現在位置">
    <h1 class="c-breadcrumb__current">GALLERY</h1>
  </nav>

  <div class="p-gallery" style="--count: {{ $categories->count() }}">
    @foreach ($categories as $category)
      @php
        // リンク先は model_type で分岐（IMAGE=カテゴリ別画像一覧、POST=ブログ一覧）
        // プレイリスト専用カテゴリ（null）は手順7（gallery.show のサブカテゴリ分岐実装）でリンク化する
        // 対象ルートが未実装の間は Route::has が偽になりリンクなしで表示される
        $count = $category->itemCount();
        $url = null;
        if ($count > 0) {
          $url = match ($category->model_type) {
            App\Enums\ModelType::IMAGE => Route::has('gallery.show') ? route('gallery.show', $category) : null,
            App\Enums\ModelType::POST  => Route::has('blog.index') ? route('blog.index') : null,
            null                       => null,
          };
        }
      @endphp
      <section class="p-gallery__card">
        <a class="p-gallery__link" @if ($url) href="{{ $url }}" aria-label="カテゴリ「{{ $category->name_ja }}」の一覧へ移動" @endif>
          <h2 class="p-gallery__title">{{ strtoupper($category->name) }}</h2>
          <figure class="p-gallery__figure">
            @php $cover = $category->coverImage; @endphp
            <img class="p-gallery__img"
              src="{{ $cover?->imageUrl('medium') ?? App\Models\Image::noImageUrl('medium') }}"
              @if ($cover)
                srcset="{{ $cover->srcset(['thumb', 'medium']) }}"
                sizes="(max-width: 767px) 100vw, 320px"
              @endif
              alt="{{ $category->name_ja }}"
              loading="lazy"
              decoding="async">
          </figure>
          <p class="p-gallery__info">
            {{ $category->name_ja }}（{{ $count > 0 ? $count : '準備中' }}）
          </p>
        </a>
      </section>
    @endforeach
  </div>
@endsection
