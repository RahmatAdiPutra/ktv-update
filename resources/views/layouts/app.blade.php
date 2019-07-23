<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="msapplication-TileColor" content="#22b9ff">
<meta name="msapplication-config" content="{{ asset('favicon/browserconfig.xml') }}">
<meta name="theme-color" content="#22b9ff">
<base href="http://localhost:8000/">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('favicon/apple-touch-icon.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon/favicon-16x16.png') }}">
<link rel="manifest" href="{{ asset('favicon/site.webmanifest') }}">
<link rel="mask-icon" href="{{ asset('favicon/safari-pinned-tab.svg') }}" color="#5bbad5">
<link rel="shortcut icon" href="{{ asset('favicon/favicon.ico') }}">
<link href="{{ asset('plugins/pace/pace-theme-flash.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('plugins/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('plugins/font-awesome/css/font-awesome.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('plugins/jquery-scrollbar/jquery.scrollbar.css') }}" rel="stylesheet" type="text/css" media="screen">
<link href="{{ asset('plugins/select2/css/select2.min.css') }}" rel="stylesheet" type="text/css" media="screen">
<link href="{{ asset('plugins/switchery/css/switchery.min.css') }}" rel="stylesheet" type="text/css" media="screen">
<title>@stack('appTitle') | {{ env('APP_NAME') }}</title>
<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
<script>
  WebFont.load({
    google: {"families":["Poppins:300,400,500,600,700","Roboto:300,400,500,600,700"]},
    active: function() {
        sessionStorage.fonts = true;
    }
  });
  window.onload = function()
  {
    // fix for windows 8
    if (navigator.appVersion.indexOf("Windows NT 6.2") != -1) {
      document.head.innerHTML += '<link rel="stylesheet" type="text/css" href="css/windows.chrome.fix.css" />';
    }
  }
</script>

<!-- BEGIN: HTML header section -->
@stack('appHeader')
<!-- END: HTML header section -->

<link href="{{ asset('css/pages-icons.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('css/pages.css') }}" class="main-stylesheet" rel="stylesheet" type="text/css">
<link href="{{ asset('css/simple-app.css') }}" class="main-stylesheet" rel="stylesheet" type="text/css">
</head>
<body class="m-content--skin-light2 m-body @stack('bodyClass')">

	<!-- begin:: app content -->
	@yield('appContent')
	<!-- end:: app content -->

<!-- BEGIN: VENDOR JS-->
<script src="{{ asset('plugins/pace/pace.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery/jquery-3.2.1.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/modernizr.custom.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-ui/jquery-ui.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/popper/umd/popper.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/bootstrap/js/bootstrap.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery/jquery-easy.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-unveil/jquery.unveil.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-ios-list/jquery.ioslist.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-actual/jquery.actual.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-scrollbar/jquery.scrollbar.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/select2/js/select2.full.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/classie/classie.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/switchery/js/switchery.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/jquery-validation/js/jquery.validate.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('plugins/mustache/mustache.min.js') }}" type="text/javascript"></script>
<!-- END: VENDOR JS -->
<script src="{{ asset('js/pages.js') }}"></script>
@stack('appFooter')
</body>
</html>
