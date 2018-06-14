<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config("app.name") }}</title>
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    </head>
    <body>
        <div id="app">
            <navigation></navigation>
            <transition name="fade">
                <router-view></router-view>
            </transition>
        </div>

        <!-- Scripts -->
        <script>
            var appSettings = {
                'name': {!! json_encode(config("app.name")) !!},
                'version': {!! json_encode(config("app.version")) !!}
            };
        </script>
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
