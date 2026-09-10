@extends('layouts.app')

@php
  // タイトル未入力時のフォールバック（一覧ページと同じ規則）
  $title = $image->title ?: 'No. ' . $image->id;
@endphp

@section('title', $title . ' - ' . strtoupper($category->name))
@section('description', $image->caption ?: config('app.name') . ' ' . strtoupper($category->name) . '「' . $title . '」')
@section('og-type', 'article')
@section('og-image', $image->imageUrl('large'))

@section('content')
  <nav class="c-breadcrumb" aria-label="現在位置">
    <ol class="c-breadcrumb__list">
      <li class="c-breadcrumb__item">
        <a class="c-breadcrumb__link" href="{{ route('gallery.index') }}" aria-label="ギャラリートップへ移動">GALLERY</a>
      </li>
      <li class="c-breadcrumb__item">
        <a class="c-breadcrumb__link" href="{{ route('gallery.show', $category) }}" aria-label="{{ strtoupper($category->name) }}の一覧へ移動">{{ strtoupper($category->name) }}</a>
      </li>
    </ol>
    {{-- 作品ページは説明欄に専用のタイトル欄（h1）を持つため、ここは見出しにしない --}}
    <span class="c-breadcrumb__current">{{ $title }}</span>
  </nav>

  <article class="p-image">
    <figure class="p-image__figure">
      {{-- 表示枠：グリッド行いっぱいに伸びる実体のあるボックス。この中で画像を縮小させる --}}
      <div class="p-image__frame">
        @if ($image->isAnimatedGif())
          {{-- GIF はリサイズなし（全サイズ同一ファイル）のため srcset 不要。動いたまま表示 --}}
          <img class="p-image__img"
            src="{{ $image->imageUrl('large') }}"
            {{-- 移行データ等で寸法が欠けている場合は属性ごと出さない（不正空文字回避） --}}
            @if ($image->width && $image->height)
              width="{{ $image->width }}"
              height="{{ $image->height }}"
            @endif
            alt="{{ $title }}"
            fetchpriority="high"
            decoding="async">
        @else
          <img class="p-image__img"
            src="{{ $image->imageUrl('medium') }}"
            srcset="{{ $image->srcset(['medium', 'large']) }}"
            sizes="(max-width: 899px) 100vw, 900px"
            @if ($image->width && $image->height)
              width="{{ $image->width }}"
              height="{{ $image->height }}"
            @endif
            alt="{{ $title }}"
            fetchpriority="high"
            decoding="async">
        @endif
      </div>

      <figcaption class="p-image__meta">
        <h1 class="p-image__title">{{ $title }}</h1>
        @if ($image->year)
          <p class="p-image__year">{{ $image->year }}制作</p>
        @endif
        @if ($image->caption)
          <p class="p-image__caption">{{ $image->caption }}</p>
        @endif
        @if ($image->tags->isNotEmpty())
          <ul class="p-image__tags">
            @foreach ($image->tags as $tag)
              <li class="p-image__tag">
                @if (Route::has('tags.show'))
                  <a class="p-image__tag-link" href="{{ route('tags.show', $tag) }}">{{ $tag->name }}</a>
                @else
                  {{ $tag->name }}
                @endif
              </li>
            @endforeach
          </ul>
        @endif
      </figcaption>
    </figure>

    <nav class="p-image__nav" aria-label="作品の前後移動">
      @if ($previousId)
        <a class="p-image__nav-link p-image__nav-link--symbol"
          href="{{ route('images.show', [$category, $previousId]) }}"
          rel="prev"
          aria-label="前の作品へ移動">&lt;</a>
      @else
        <span class="p-image__nav-link p-image__nav-link--symbol is-disabled" aria-hidden="true">&lt;</span>
      @endif

      <p class="p-image__nav-position">{{ $position }} / {{ $total }}</p>

      @if ($nextId)
        <a class="p-image__nav-link p-image__nav-link--symbol"
          href="{{ route('images.show', [$category, $nextId]) }}"
          rel="next"
          aria-label="次の作品へ移動">&gt;</a>
      @else
        <span class="p-image__nav-link p-image__nav-link--symbol is-disabled" aria-hidden="true">&gt;</span>
      @endif
    </nav>
  </article>
@endsection
