{{-- フッター：ページナビのみ（© はトップページに表示） --}}
<footer class="l-footer">
  <nav class="l-footer__nav" aria-label="メインナビゲーション">
    <ul class="l-footer__nav-list">
      @foreach ([
        ['route' => 'gallery.index', 'pattern' => 'gallery.*', 'label' => 'Gallery'],
        ['route' => 'blog.index', 'pattern' => 'blog.*', 'label' => 'Blog'],
        ['route' => 'about', 'pattern' => 'about', 'label' => 'About'],
        ['route' => 'contact.index', 'pattern' => 'contact.*', 'label' => 'Contact'],
      ] as $item)
        @if (Route::has($item['route']))
          <li class="l-footer__nav-item {{ request()->routeIs($item['pattern']) ? 'is-current' : '' }}">
            <a class="l-footer__nav-link" href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
          </li>
        @endif
      @endforeach
    </ul>
  </nav>
</footer>
