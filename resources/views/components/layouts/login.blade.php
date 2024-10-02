<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? '' }}</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Quicksand:300,400,500,700"
    rel="stylesheet">
    <link href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css" rel="stylesheet">
  
    {{-- stylos propios --}}
    <link rel="stylesheet" type="text/css" href="/modernadmin/app-assets/css/vendors.css">
    <link rel="stylesheet" type="text/css" href="/modernadmin/app-assets/css/app.css">
    <link rel="stylesheet" type="text/css" href="/modernadmin/app-assets/css/core/colors/palette-gradient.css">
    <link rel="stylesheet" type="text/css" href="/modernadmin/app-assets/css/pages/login-register.css">
    <link rel="stylesheet" type="text/css" href="/modernadmin/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="/css/style-p.css">

    <script type="text/javascript" src="/assets/jquery.min.js?v={{ env('VERSION_STYLE') }}"></script>
    <link rel="stylesheet" type="text/css" href="/assets/toastr/toastr.css?v={{ env('VERSION_STYLE') }}">
    <script type="text/javascript" src="/assets/toastr/toastr.js?v={{ env('VERSION_STYLE') }}"></script>
    <!-- END Custom CSS-->
    
</head>
<body class="vertical-layout vertical-menu-modern 1-column menu-expanded blank-page blank-page" data-open="click" data-menu="vertical-menu-modern" data-col="1-column">

    {{ $slot }}

    <!-- BEGIN VENDOR JS-->
    <script src="/modernadmin/app-assets/vendors/js/vendors.min.js?v={{ env('VERSION_STYLE') }}" type="text/javascript"></script>
    <script src="/modernadmin/app-assets/js/core/app-menu.js?v={{ env('VERSION_STYLE') }}" type="text/javascript"></script>
    <script src="/modernadmin/app-assets/js/core/app.js?v={{ env('VERSION_STYLE') }}" type="text/javascript"></script>
    <script src="/js/jquery.js?v={{ env('VERSION_STYLE') }}" type="text/javascript"></script>
    <script src="/js/show_alerts.js" type="text/javascript"></script>
    <!-- END PAGE LEVEL JS-->

</body>
</html>