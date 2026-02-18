<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>{{env('APP_NAME')}} Log In</title>

  <!-- Stylesheets -->
  <link href="{{asset('/board2/css/bootstrap.css')}}" rel="stylesheet">
  <link href="{{asset('/board2/css/style.css')}}" rel="stylesheet">
  <link href="{{asset('/board2/css/responsive.css')}}" rel="stylesheet">

  <link rel="shortcut icon" href="{{url('logo', $general_setting->site_logo)}}" type="image/x-icon">
  <link rel="icon" href="{{url('logo', $general_setting->site_logo)}}" type="image/x-icon">

  <!-- Responsive -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
</head>

<body>

  @yield('content')

  <script src="{{asset('/board2/js/jquery.js')}}"></script>
  <script src="{{asset('/board2/js/popper.min.js')}}"></script>
  <script src="{{asset('/board2/js/chosen.min.js')}}"></script>
  <script src="{{asset('/board2/js/bootstrap.min.js')}}"></script>
  <script src="{{asset('/board2/js/jquery-ui.min.js')}}"></script>
  <script src="{{asset('/board2/js/jquery.fancybox.js')}}"></script>
  <script src="{{asset('/board2/js/jquery.modal.min.js')}}"></script>
  <script src="{{asset('/board2/js/mmenu.polyfills.js')}}"></script>
  <script src="{{asset('/board2/js/mmenu.js')}}"></script>
  <script src="{{asset('/board2/js/appear.js')}}"></script>
  <script src="{{asset('/board2/js/ScrollMagic.min.js')}}"></script>
  <script src="{{asset('/board2/js/rellax.min.js')}}"></script>
  <script src="{{asset('/board2/js/owl.js')}}"></script>
  <script src="{{asset('/board2/js/wow.js')}}"></script>
  <script src="{{asset('/board2/js/script.js')}}"></script>

</body>


</html>