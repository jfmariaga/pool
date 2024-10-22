<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? '' }}</title>

    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Quicksand:300,400,500,700"
        rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">

    {{-- stylos propios --}}
    {{-- stylos propios --}}
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style-p.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/switch.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('modernadmin/app-assets/css/vendors.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('modernadmin/app-assets/vendors/css/tables/datatable/datatables.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('modernadmin/app-assets/css/app.css') }}">
    <link rel="stylesheet" type="text/css"
        href="{{ asset('modernadmin/app-assets/css/core/menu/menu-types/vertical-menu-modern.css') }}">
        <link rel="stylesheet" type="text/css"
        href="{{ asset('modernadmin/app-assets/vendors/css/forms/selects/select2.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/theme.css') }}">



    <script type="text/javascript" src="{{ asset('assets/jquery.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/toastr/toastr.css') }}">
    <script type="text/javascript" src="{{ asset('assets/toastr/toastr.js') }}"></script>
    <!-- END Custom CSS-->
    {{-- mask input --}}
    <script src="https://jsuites.net/v4/jsuites.js"></script>
    <link rel="stylesheet" href="https://jsuites.net/v4/jsuites.css" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>

<body class="vertical-layout vertical-menu-modern 2-columns menu-expanded fixed-navbar light-theme" data-open="click"
    data-menu="vertical-menu-modern" data-col="2-columns">

      <!-- ===== GIF DE CARGA ===== -->
    <div class="cc-loadingpage"><span class="loader_new"></span></div>
    {{-- loading de carga con transparencia --}}
    <div class="cc-loadingpage_transparent dnone"><span class="loader_new"></span></div>

    <x-extends.nav></x-extends.nav>

    <x-extends.menu></x-extends.menu>

    {{ $slot }}

    <script src="{{ asset('modernadmin/app-assets/vendors/js/vendors.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('modernadmin/app-assets/vendors/js/forms/select/select2.js') }}" type="text/javascript"></script>
    <script src="{{ asset('modernadmin/app-assets/js/core/app-menu.js') }}" type="text/javascript"></script>
    <script src="{{ asset('modernadmin/app-assets/js/core/app.js') }}" type="text/javascript"></script>
    <script src="{{ asset('modernadmin/app-assets/js/scripts/customizer.js') }}" type="text/javascript"></script>


    <script src="{{ asset('js/jquery.js') }}" type="text/javascript"></script>
    <script src="{{ asset('modernadmin/app-assets/vendors/js/tables/datatable/datatables.min.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('modernadmin/app-assets/js/scripts/tables/datatables/datatable-basic.js') }}"
        type="text/javascript"></script>
    <script src="{{ asset('js/show_alerts.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/fancybox4.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/basic.js') }}" type="text/javascript"></script>

    @stack('js_extra')

</body>

</html>
