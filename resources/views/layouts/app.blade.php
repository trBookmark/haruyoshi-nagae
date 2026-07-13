<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') | @endif{{ config('app.name') }}</title>
    @hasSection('description')
      <meta name="description" content="@yield('description')">
    @endif
    @include('partials.ogp')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Italianno&family=Noto+Serif+SC:wght@200;600&display=swap" rel="stylesheet">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    @stack('head')
  </head>
  <body>
    @include('partials.header')

    <main class="l-main">
      @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.drawer')

    @stack('scripts')
  </body>
</html>
