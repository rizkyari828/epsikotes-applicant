<?php
    
?>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SIM E-Psikotest') }}</title>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="{{ asset('css/style.css') }}" rel="stylesheet">
        <link href="{{ asset('css/all_icons.min.css') }}" rel="stylesheet">
        <link href="{{ asset('css/custom-style.css') }}" rel="stylesheet">
    </head>
    <body>
        <div id="preloader">
            <div data-loader="circle-side"></div>
        </div><!-- /Preload -->
        
        <div id="loader_form">
            <div data-loader="circle-side-2"></div>
        </div><!-- /loader_form -->
        <div id="app">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <main class="py-4">
                            @yield('content')
                        </main>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
        <script src="{{ asset('/js/jquery-3.3.1.min.js') }}"></script>
        <script src="{{ asset('/js/bootstrap.min.js') }}"></script>
        <script src="{{ asset('/js/common_scripts.min.js') }}"></script>
        <script src="{{ asset('/js/main.js') }}"></script>
        <script src="{{ asset('/js/jquery.countdown.min.js') }}"></script>

        <script type="text/javascript">
            function preventBack() { window.history.forward(); }
            setTimeout("preventBack()", 0);
            window.onunload = function () { null };

            // history.pushState(null, null, location.href);
            // window.onpopstate = function () {
            //     history.go(1);
            // };
        </script>
    </body>
</html>
