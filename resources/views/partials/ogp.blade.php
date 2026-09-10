{{-- OGP：値は各ページの @section で上書き、未指定はフォールバック --}}
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:type" content="@yield('og-type', 'website')">
<meta property="og:title" content="@hasSection('title')@yield('title') | @endif{{ config('app.name') }}">
@hasSection('description')
  <meta property="og:description" content="@yield('description')">
@endif
<meta property="og:url" content="{{ url()->current() }}">
@hasSection('og-image')
  <meta property="og:image" content="@yield('og-image')">
@endif
<meta name="twitter:card" content="summary_large_image">
