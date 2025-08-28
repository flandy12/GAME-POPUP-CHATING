<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Monash University</title>
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('/images/logo.png') }}">
    <script src="https://cdn.tiny.cloud/1/eurlu7d7btago4qbkngk9koxh3cn62potiv7f1ryk6kmosf7/tinymce/7/tinymce.min.js"
        referrerpolicy="origin"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>

    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
</head>

<body class="bg-[#e3e8f8] w-full">

    @yield('content')

    @stack('scripts')
</body>

</html>
