<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Styles --}}
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet">
    <link href="{{ asset('assets/css/icons/icomoon/styles.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/all.min.css') }}" rel="stylesheet">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png')}}" />

    {{-- Core JS --}}
    <script src="{{ asset('assets/js/main/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/main/bootstrap.bundle.min.js') }}"></script>

    {{-- Theme JS --}}
    <script src="{{ asset('assets/js/plugins/visualization/d3/d3.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/ui/moment/moment.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugins/pickers/daterangepicker.js') }}"></script>

    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/demo_pages/dashboard.js') }}"></script>
    <script src="{{ asset('assets/js/draft.js') }}"></script>
</head>
<body>

    {{-- Header --}}
    @include('partials.header')

    <div class="d-flex">
        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Main Content --}}
        <div class="content-wrapper flex-grow-1">
            @yield('content')
        </div>
    </div>

    @stack('scripts')
</body>
</html>
