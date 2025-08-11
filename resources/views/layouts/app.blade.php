<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Test Particles</title>
  <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite('resources/js/app.js')
  @vite('resources/css/app.css')
</head>

    <body class="bg-[#e3e8f8]">

    @yield('content')

    @yield('scripts')
</body>
</html>
