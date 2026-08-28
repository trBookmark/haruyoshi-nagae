@extends('layouts.app')

@section('title', strtoupper($category->name) . ' - GALLERY')
@section('description', config('app.name') . ' GALLERY「' . strtoupper($category->name) . '」（IMAGE LIST）')
@section('og-image', $images->first()->imageUrl('large'))

@section('content')
  <nav class="c-breadcrumb" aria-label="現在位置">
    <ol class="c-breadcrumb__list">
      <li class="c-breadcrumb__item">
        <a class="c-breadcrumb__link" href="{{ route('gallery.index') }}" aria-label="ギャラリートップへ移動">GALLERY</a>
      </li>
    </ol>
    <h1 class="c-breadcrumb__current">{{ strtoupper($category->name) }}</h1>
  </nav>

  <div class="p-category">
    @foreach ($images as $image)
      @php
        // タイトル未入力時のフォールバック表示
        $title = $image->title ?: 'No. ' . $image->id;
        $url = Route::has('images.show') ? route('images.show', [$category, $image]) : null;
      @endphp
      <a class="p-category__card c-reveal js-reveal"
        @if ($url) href="{{ $url }}" @endif
        aria-label="作品「{{ $title }}」の個別ページへ移動">
        @if ($image->isAnimatedGif())
          {{-- GIF はリサイズなし（全サイズ同一ファイル）のため srcset 不要。動いたまま表示 --}}
          <img class="p-category__img u-img-align--{{ $image->thumbnail_align->value }}"
            src="{{ $image->imageUrl('thumb') }}"
            alt="{{ $title }}"
            loading="lazy"
            decoding="async">
        @else
          <img class="p-category__img u-img-align--{{ $image->thumbnail_align->value }}"
            src="{{ $image->imageUrl('medium') }}"
            srcset="{{ $image->srcset(['thumb', 'medium']) }}"
            sizes="(max-width: 559px) 50vw, (max-width: 899px) 33vw, 25vw"
            alt="{{ $title }}"
            loading="lazy"
            decoding="async">
        @endif
        <div class="p-category__caption" aria-hidden="true">
          <h2 class="p-category__title">{{ $title }}</h2>
          @if ($image->year)
            <p class="p-category__year">{{ $image->year }}制作</p>
          @endif
          @if ($image->tags->isNotEmpty())
            <ul class="p-category__tags">
              @foreach ($image->tags as $tag)
                <li class="p-category__tag">{{ $tag->name }}</li>
              @endforeach
            </ul>
          @endif
        </div>
      </a>
    @endforeach
  </div>
@endsection
