<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @if(!config('database.connections.saleprosaas_landlord'))
    <link rel="icon" type="image/png" href="{{url('logo', $general_setting->site_logo)}}" />
    <title>{{$general_setting->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{url('manifest.json')}}">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo asset('vendor/bootstrap/css/bootstrap.min.css') ?>" type="text/css">
    <link rel="preload" href="<?php echo asset('vendor/bootstrap-toggle/css/bootstrap-toggle.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/bootstrap-toggle/css/bootstrap-toggle.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="<?php echo asset('vendor/bootstrap/css/bootstrap-datepicker.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/bootstrap/css/bootstrap-datepicker.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="<?php echo asset('vendor/jquery-timepicker/jquery.timepicker.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/jquery-timepicker/jquery.timepicker.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="<?php echo asset('vendor/bootstrap/css/awesome-bootstrap-checkbox.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/bootstrap/css/awesome-bootstrap-checkbox.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="<?php echo asset('vendor/bootstrap/css/bootstrap-select.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/bootstrap/css/bootstrap-select.min.css') ?>" rel="stylesheet"></noscript>
    <!-- Font Awesome CSS-->
    <link rel="preload" href="<?php echo asset('vendor/font-awesome/css/font-awesome.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet"></noscript>
    <!-- Drip icon font-->
    <link rel="preload" href="<?php echo asset('vendor/dripicons/webfont.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/dripicons/webfont.css') ?>" rel="stylesheet"></noscript>
    <!-- Google fonts - Roboto -->
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" rel="stylesheet"></noscript>
    <!-- jQuery Circle-->
    <link rel="preload" href="<?php echo asset('css/grasp_mobile_progress_circle-1.0.0.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('css/grasp_mobile_progress_circle-1.0.0.min.css') ?>" rel="stylesheet"></noscript>
    <!-- Custom Scrollbar-->
    <link rel="preload" href="<?php echo asset('vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css') ?>" rel="stylesheet"></noscript>
    <!-- virtual keybord stylesheet-->
    <link rel="preload" href="<?php echo asset('vendor/keyboard/css/keyboard.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/keyboard/css/keyboard.css') ?>" rel="stylesheet"></noscript>
 
    <!-- table sorter stylesheet-->
    <link rel="preload" href="<?php echo asset('vendor/datatable/dataTables.bootstrap4.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/datatable/dataTables.bootstrap4.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="stylesheet" href="<?php echo asset('css/style.default.css') ?>" id="theme-stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('css/style.css') ?>">

    <link rel="stylesheet" href="<?php echo asset('css/modal-select-fix.css') ?>" type="text/css">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="<?php echo asset('css/custom-'.$general_setting->theme) ?>" type="text/css" id="custom-style">
    @if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl)
      <!-- RTL css -->
      <link rel="stylesheet" href="<?php echo asset('vendor/bootstrap/css/bootstrap-rtl.min.css') ?>" type="text/css">
      <link rel="stylesheet" href="<?php echo asset('css/custom-rtl.css') ?>" type="text/css" id="custom-style">
    @endif

    @else
    <link rel="icon" type="image/png" href="{{url('../../logo', $general_setting->site_logo)}}" />
    <title>{{$general_setting->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{url('../../manifest.json')}}">
    <!-- Bootstrap CSS-->
    <link rel="stylesheet" href="<?php echo asset('../../vendor/bootstrap/css/bootstrap.min.css') ?>" type="text/css">
    <link rel="preload" href="<?php echo asset('../../vendor/bootstrap-toggle/css/bootstrap-toggle.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/bootstrap-toggle/css/bootstrap-toggle.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="<?php echo asset('../../vendor/bootstrap/css/bootstrap-datepicker.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/bootstrap/css/bootstrap-datepicker.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="<?php echo asset('../../vendor/jquery-timepicker/jquery.timepicker.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/jquery-timepicker/jquery.timepicker.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="<?php echo asset('../../vendor/bootstrap/css/awesome-bootstrap-checkbox.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/bootstrap/css/awesome-bootstrap-checkbox.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="<?php echo asset('../../vendor/bootstrap/css/bootstrap-select.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/bootstrap/css/bootstrap-select.min.css') ?>" rel="stylesheet"></noscript>
    <!-- Font Awesome CSS-->
    <link rel="preload" href="<?php echo asset('../../vendor/font-awesome/css/font-awesome.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/font-awesome/css/font-awesome.min.css') ?>" rel="stylesheet"></noscript>
    <!-- Drip icon font-->
    <link rel="preload" href="<?php echo asset('../../vendor/dripicons/webfont.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/dripicons/webfont.css') ?>" rel="stylesheet"></noscript>
    <!-- jQuery Circle-->
    <link rel="preload" href="<?php echo asset('../../css/grasp_mobile_progress_circle-1.0.0.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../css/grasp_mobile_progress_circle-1.0.0.min.css') ?>" rel="stylesheet"></noscript>
    <!-- Custom Scrollbar-->
    <link rel="preload" href="<?php echo asset('../../vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css') ?>" rel="stylesheet"></noscript>
    <!-- virtual keybord stylesheet-->
    <link rel="preload" href="<?php echo asset('../../vendor/keyboard/css/keyboard.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/keyboard/css/keyboard.css') ?>" rel="stylesheet"></noscript>
    <!-- table sorter stylesheet-->
    <link rel="preload" href="<?php echo asset('../../vendor/datatable/dataTables.bootstrap4.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/datatable/dataTables.bootstrap4.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="stylesheet" href="<?php echo asset('../../css/style.default.css') ?>" id="theme-stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('../../css/style.css') ?>">

    <link rel="stylesheet" href="<?php echo asset('../../css/modal-select-fix.css') ?>" type="text/css">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="<?php echo asset('../../css/custom-'.$general_setting->theme) ?>" type="text/css" id="custom-style">
    @if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl)
      <!-- RTL css -->
      <link rel="stylesheet" href="<?php echo asset('../../vendor/bootstrap/css/bootstrap-rtl.min.css') ?>" type="text/css">
      <link rel="stylesheet" href="<?php echo asset('../../css/custom-rtl.css') ?>" type="text/css" id="custom-style">
    @endif
    @endif
    <!-- Google fonts - Roboto -->
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" rel="stylesheet"></noscript>
    
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");
      
      /* Modern Design System */
      :root {
        /* --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        --primary-gradient: linear-gradient(135deg, #c4c6cf 0%, #299e42 100%);
        --primary-color: #13bd60;
        --secondary-color: #764ba2;
        --accent-color: #ffd700;
        --text-dark: #1a1a2e;
        --text-light: #f2f2f2;
        --bg-light: #0c0c0c;
        --border-color: #e0e0e0;
        --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.12);
        --shadow-lg: 0 8px 24px rgba(0, 0, 0, 0.15);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      }

      * {
        scroll-behavior: smooth;
      }

      body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        font-size: 15px;
        color: var(--text-dark);
        line-height: 1.6;
        font-weight: 400;
        background: var(--bg-light);
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        margin: 0;
        padding: 0;
      }

      /* Remove header spacing */
      header {
        margin: 0 !important;
        padding: 0 !important;
        height: auto !important;
      }

      section.pos-section {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
      }

    

      .pos-page {
        background: white !important;
        min-height: auto !important;
        padding-bottom: 0 !important;
      }

      body.pos-page {
        background: white !important;
      }

      /* Header Navigation Styling */
      nav.navbar {
        /* background: linear-gradient(to right, #ffffff 0%, #f8f9fa 100%); */
        background: #13bd60!important;
        border-bottom: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 12px 24px;
        position: sticky;
        top: 0;
        z-index: 100;
      }

      nav.navbar .navbar-brand {
        font-size: 22px;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -1px;
        display: flex;
        align-items: center;
      }

      nav.navbar .navbar-brand img {
        margin-right: 12px;
        height: 32px;
        width: auto;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
      }

      /* Navigation Items */
      nav.navbar .nav-item {
        margin: 0 8px;
      }

      nav.navbar .nav-item a {
        color: var(--text-dark) !important;
        font-size: 15px;
        font-weight: 500;
        padding: 8px 12px !important;
        border-radius: 6px;
        transition: var(--transition);
        position: relative;
      }

      nav.navbar .nav-item a:before {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--primary-color);
        transition: width 0.3s ease;
      }

      nav.navbar .nav-item a:hover {
        background: rgba(102, 126, 234, 0.08);
        color: var(--primary-color) !important;
      }

      nav.navbar .nav-item a:hover:before {
        width: 100%;
      }

      nav.navbar .nav-item a i {
        font-size: 16px;
        margin-right: 6px;
      }

      /* Navbar Toggle */
      .navbar-toggler {
        border: none;
        padding: 8px 12px;
        border-radius: 6px;
        transition: var(--transition);
      }

      .navbar-toggler:not(.collapsed) {
        background: rgba(102, 126, 234, 0.1);
      }

      .navbar-toggler:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
      }

      .navbar-toggler-icon {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        width: 24px;
        height: 20px;
      }

      .navbar-toggler-icon span {
        display: block;
        width: 100%;
        height: 2px;
        background: var(--text-dark);
        border-radius: 2px;
        transition: var(--transition);
      }

      .navbar-toggler:not(.collapsed) .navbar-toggler-icon span:nth-child(1) {
        transform: rotate(45deg) translate(8px, 8px);
      }

      .navbar-toggler:not(.collapsed) .navbar-toggler-icon span:nth-child(2) {
        opacity: 0;
      }

      .navbar-toggler:not(.collapsed) .navbar-toggler-icon span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
      }

      /* Sidebar Navbar */
      .side-navbar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: var(--shadow-md);
        border-radius: 0;
        overflow: hidden;
      }
      #side-main-menu > li > a
 {
        color: green !important;
 }

      .side-navbar li a {
        color: var(--text-light) !important;
        font-size: 14px;
        font-weight: 500;
        padding: 12px 16px !important;
        border-left: 3px solid transparent;
        transition: var(--transition);
        position: relative;
        /* color: green !important; */
      }

      .side-navbar li a:before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.1);
        opacity: 0;
        transition: opacity 0.3s ease;
        z-index: 0;
      }

      .side-navbar li a {
        position: relative;
        z-index: 1;
      }

      .side-navbar li a:hover {
        border-left-color: var(--accent-color);
        background: rgba(255, 255, 255, 0.1);
        padding-left: 20px !important;
      }

      .side-navbar li a.active {
        background: rgba(255, 255, 255, 0.15);
        border-left-color: var(--accent-color);
        font-weight: 600;
      }

      .side-navbar li a i {
        margin-right: 10px;
        font-size: 16px;
        transition: transform 0.3s ease;
      }

      .side-navbar li a:hover i {
        transform: translateX(2px);
      }

      /* Buttons */
      .btn {
        font-weight: 600;
        border-radius: 8px;
        padding: 10px 20px;
        transition: var(--transition);
        border: none;
        font-size: 14px;
      }

      .btn-primary {
        background: var(--primary-gradient);
        color: white;
        box-shadow: var(--shadow-sm);
      }

      .btn-primary:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-2px);
      }

      .btn-primary:active {
        transform: translateY(0);
      }

      .btn-secondary {
        background: var(--bg-light);
        color: var(--text-dark);
        border: 1px solid var(--border-color);
      }

      .btn-secondary:hover {
        background: #ffffff;
        border-color: var(--primary-color);
        color: var(--primary-color);
      }

      .btn-pos {
        background: rgba(255, 255, 255, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        color: var(--text-light) !important;
        border-radius: 8px;
        padding: 10px 18px;
        font-weight: 600;
        transition: var(--transition);
      }

      .btn-pos:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        transform: translateY(-2px);
      }

      .btn-pos i,
      .btn-pos span {
        color: var(--text-light) !important;
      }

      /* Form Elements */
      .form-control,
      .form-select {
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        transition: var(--transition);
      }

      .form-control:focus,
      .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
      }

      /* Cards */
      .card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
      }

      .card:hover {
        box-shadow: var(--shadow-md);
      }

      .card-header {
        /* background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%); */
        background: rgba(102, 126, 234, 0.05);
        border-bottom: 1px solid var(--border-color);
        border-radius: 12px 12px 0 0;
        padding: 16px 20px;
        font-weight: 600;
        color: var(--text-dark);
      }

      /* Collapse/Accordion */
      .collapse {
        transition: var(--transition);
      }

      .collapse.show {
        animation: slideDown 0.3s ease;
      }

      @keyframes slideDown {
        from {
          opacity: 0;
          transform: translateY(-10px);
        }
        to {
          opacity: 1;
          transform: translateY(0);
        }
      }

      /* Table Styling */
      table {
        font-size: 14px;
        border-collapse: collapse;
      }

      table thead th {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        color: var(--text-dark);
        font-weight: 600;
        border: none;
        padding: 14px 12px;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
      }

      table tbody td {
        padding: 12px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
      }

      table tbody tr {
        transition: var(--transition);
      }

      table tbody tr:hover {
        background: rgba(102, 126, 234, 0.05);
      }

      /* Badge */
      .badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 12px;
        letter-spacing: 0.3px;
      }

      .badge-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
      }

      .badge-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
      }

      .badge-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
      }

      .badge-warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: #fff;
      }

      /* Loader */
      #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        align-items: center;
        justify-content: center;
      }

      #loader.show {
        display: flex;
      }

      #loader::after {
        content: '';
        width: 50px;
        height: 50px;
        border: 4px solid var(--border-color);
        border-top-color: var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }

      @keyframes spin {
        to { transform: rotate(360deg); }
      }

      /* Modal Styling */
      .modal-content {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: var(--shadow-lg);
      }

      .modal-header {
        background: linear-gradient(135deg, #5cd562 0%, #e2bb0e 100%);
        color: white;
        border: none;
        border-radius: 12px 12px 0 0;
        padding: 16px 20px;
        font-weight: 600;
      }

      .modal-header .close {
        color: white;
        opacity: 0.8;
      }

      .modal-header .close:hover {
        opacity: 1;
      }

      /* Alert/Toast */
      .alert {
        border: none;
        border-radius: 8px;
        padding: 14px 16px;
        font-weight: 500;
        box-shadow: var(--shadow-sm);
      }

      .alert-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
      }

      .alert-danger {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
      }

      .alert-warning {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: #fff;
      }

      .alert-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
      }

      /* Responsive Design */
      @media (max-width: 768px) {
        nav.navbar {
          padding: 10px 16px;
        }

        .side-navbar {
          position: fixed;
          left: -100%;
          top: 0;
          width: 100%;
          max-width: 280px;
          height: 100vh;
          z-index: 1000;
          transition: left 0.3s ease;
          box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
        }

        .side-navbar.show {
          left: 0;
        }
      }

      /* Dark Mode Support */
      @media (prefers-color-scheme: dark) {
        :root {
          --text-dark: #111010;
          --text-light: #1a1a2e;
          --bg-light: #1a1a2e;
          --border-color: #333333;
        }

        nav.navbar {
          background: linear-gradient(to right, #1a1a2e 0%, #16213e 100%);
          border-bottom-color: var(--border-color);
        }

        body {
          background: #0f3460;
          color: var(--text-dark);
        }
      }
    </style>
  </head>
  <body class="pos-page" onload="myFunction()">
    <div id="loader"></div>

      <div style="display:none;" id="content" >
          @yield('content')
      </div>

    <!-- notification modal -->
    <div id="notification-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Send Notification')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                    {!! Form::open(['route' => 'notifications.store', 'method' => 'post']) !!}
                      <div class="row">
                          <?php
                              $lims_user_list = DB::table('users')->where([
                                ['is_active', true],
                                ['id', '!=', \Auth::user()->id]
                              ])->get();
                          ?>
                          <div class="col-md-6 form-group">
                              <label>{{trans('file.User')}} *</label>
                              <select name="user_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select user...">
                                  @foreach($lims_user_list as $user)
                                  <option value="{{$user->id}}">{{$user->name . ' (' . $user->email. ')'}}</option>
                                  @endforeach
                              </select>
                          </div>
                          <div class="col-md-12 form-group">
                              <label>{{trans('file.Message')}} *</label>
                              <textarea rows="5" name="message" class="form-control" required></textarea>
                          </div>
                      </div>
                      <div class="form-group">
                          <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                      </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <!-- end notification modal -->

    <!-- Category Modal -->
      <div id="category-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
          <div role="document" class="modal-dialog">
            <div class="modal-content">
              {!! Form::open(['route' => 'category.store', 'method' => 'post', 'files' => true]) !!}
              <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Category')}}</h5>
                <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
              </div>
              <div class="modal-body">
                <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                  <div class="row">
                      <div class="col-md-6 form-group">
                          <label>{{trans('file.name')}} *</label>
                          {{Form::text('name',null,array('required' => 'required', 'class' => 'form-control', 'placeholder' => 'Type category name...'))}}
                      </div>
                      <div class="col-md-6 form-group">
                          <label>{{trans('file.Image')}}</label>
                          <input type="file" name="image" class="form-control">
                      </div>
                      <div class="col-md-6 form-group">
                          <label>{{trans('file.Parent Category')}}</label>
                          <select name="parent_id" class="form-control selectpicker" id="parent">
                              <option value="">No {{trans('file.parent')}}</option>
                              @foreach($categories_list as $category)
                              <option value="{{$category->id}}">{{$category->name}}</option>
                              @endforeach
                          </select>
                      </div>
                  </div>

                  <div class="form-group">
                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                  </div>
              </div>
              {{ Form::close() }}
            </div>
          </div>
      </div>
      <!-- Category Modal -->

    <!-- expense modal -->
    <div id="expense-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Expense')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                    {!! Form::open(['route' => 'expenses.store', 'method' => 'post']) !!}
                    <?php
                      $lims_expense_category_list = DB::table('expense_categories')->where('is_active', true)->get();
                      $isStaff = Auth::user()->roles->contains(fn($role) => $role->id > 2);
                      if($isStaff)
                        $lims_warehouse_list = DB::table('warehouses')->where([
                          ['is_active', true],
                          ['id', Auth::user()->warehouse_id]
                        ])->get();
                      else
                        $lims_warehouse_list = DB::table('warehouses')->where('is_active', true)->get();
                      $lims_account_list = \App\Models\Account::where('is_active', true)->get();

                    ?>
                      <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{trans('file.Expense Category')}} *</label>
                            <select name="expense_category_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select Expense Category...">
                                @foreach($lims_expense_category_list as $expense_category)
                                <option value="{{$expense_category->id}}">{{$expense_category->name . ' (' . $expense_category->code. ')'}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{trans('file.Warehouse')}} *</label>
                            <select name="warehouse_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select Warehouse...">
                                @foreach($lims_warehouse_list as $warehouse)
                                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{trans('file.Amount')}} *</label>
                            <input type="number" name="amount" step="any" required class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label> {{trans('file.Account')}}</label>
                            <select class="form-control selectpicker" name="account_id">
                            @foreach($lims_account_list as $account)
                                @if($account->is_default)
                                <option selected value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                                @else
                                <option value="{{$account->id}}">{{$account->name}} [{{$account->account_no}}]</option>
                                @endif
                            @endforeach
                            </select>
                        </div>
                      </div>
                      <div class="form-group">
                          <label>{{trans('file.Note')}}</label>
                          <textarea name="note" rows="3" class="form-control"></textarea>
                      </div>
                      <div class="form-group">
                          <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                      </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <!-- end expense modal -->

    <!-- sale return modal -->
    <div id="add-sale-return" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
          <div class="modal-content">
            {!! Form::open(['route' => 'return-sale.create', 'method' => 'get']) !!}
            <div class="modal-header">
              <h5 id="exampleModalLabel" class="modal-title">Add Sale Return</h5>
              <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
              <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
               <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{trans('file.Sale Reference')}} *</label>
                            <input type="text" name="reference_no" class="form-control">
                        </div>
                    </div>
               </div>
                {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
            </div>
            {!! Form::close() !!}
          </div>
        </div>
    </div>
    <!-- end sale return modal -->

    <!-- purchase return modal -->
    <div id="add-purchase-return" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
          <div class="modal-content">
            {!! Form::open(['route' => 'return-purchase.create', 'method' => 'get']) !!}
            <div class="modal-header">
              <h5 id="exampleModalLabel" class="modal-title">Add Purchase Return</h5>
              <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
            </div>
            <div class="modal-body">
              <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
               <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>{{trans('file.Purchase Reference')}} *</label>
                            <input type="text" name="reference_no" class="form-control">
                        </div>
                    </div>
               </div>
                {{Form::submit('Submit', ['class' => 'btn btn-primary'])}}
            </div>
            {!! Form::close() !!}
          </div>
        </div>
    </div>
    <!-- end purchase return modal -->

    <!-- warehouse modal -->
    <div id="warehouse-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Warehouse Report')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                    {!! Form::open(['route' => 'report.warehouse', 'method' => 'post']) !!}
                    <?php
                      $lims_warehouse_list = DB::table('warehouses')->where('is_active', true)->get();
                    ?>
                      <div class="form-group">
                          <label>{{trans('file.Warehouse')}} *</label>
                          <select name="warehouse_id" class="selectpicker form-control" required data-live-search="true" id="warehouse-id" data-live-search-style="begins" title="Select warehouse...">
                              @foreach($lims_warehouse_list as $warehouse)
                              <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                              @endforeach
                          </select>
                      </div>

                      <input type="hidden" name="start_date" value="{{date('Y-m-d')}}" />
                      <input type="hidden" name="end_date" value="{{date('Y-m-d')}}" />

                      <div class="form-group">
                          <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                      </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <!-- end warehouse modal -->

    <!-- user modal -->
    <div id="user-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.User Report')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                    {!! Form::open(['route' => 'report.user', 'method' => 'post']) !!}
                    <?php
                      $lims_user_list = DB::table('users')->where('is_active', true)->get();
                    ?>
                      <div class="form-group">
                          <label>{{trans('file.User')}} *</label>
                          <select name="user_id" class="selectpicker form-control" required data-live-search="true" id="user-id" data-live-search-style="begins" title="Select user...">
                              @foreach($lims_user_list as $user)
                              <option value="{{$user->id}}">{{$user->name . ' (' . $user->phone. ')'}}</option>
                              @endforeach
                          </select>
                      </div>

                      <input type="hidden" name="start_date" value="{{date('Y-m-d')}}" />
                      <input type="hidden" name="end_date" value="{{date('Y-m-d')}}" />

                      <div class="form-group">
                          <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                      </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <!-- end user modal -->

    <!-- customer modal -->
    <div id="customer-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Customer Report')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                    {!! Form::open(['route' => 'report.customer', 'method' => 'post']) !!}
                    <?php
                      $lims_customer_list = DB::table('customers')->where('is_active', true)->get();
                    ?>
                      <div class="form-group">
                          <label>{{trans('file.customer')}} *</label>
                          <select name="customer_id" class="selectpicker form-control" required data-live-search="true" id="customer-id" data-live-search-style="begins" title="Select customer...">
                              @foreach($lims_customer_list as $customer)
                              <option value="{{$customer->id}}">{{$customer->name . ' (' . $customer->phone_number. ')'}}</option>
                              @endforeach
                          </select>
                      </div>

                      <input type="hidden" name="start_date" value="{{date('Y-m-d')}}" />
                      <input type="hidden" name="end_date" value="{{date('Y-m-d')}}" />

                      <div class="form-group">
                          <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                      </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <!-- end customer modal -->

    <!-- customer group modal -->
    <div id="customer-group-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
      <div role="document" class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Customer Group Report')}}</h5>
                  <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
              </div>
              <div class="modal-body">
                <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                  {!! Form::open(['route' => 'report.customer_group', 'method' => 'post']) !!}
                  <?php
                    $lims_customer_group_list = DB::table('customer_groups')->where('is_active', true)->get();
                  ?>
                    <div class="form-group">
                        <label>{{trans('file.Customer Group')}} *</label>
                        <select name="customer_group_id" class="selectpicker form-control" required data-live-search="true" id="customer-group-id" data-live-search-style="begins" title="Select customer group...">
                            @foreach($lims_customer_group_list as $customer_group)
                            <option value="{{$customer_group->id}}">{{$customer_group->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="start_date" value="{{date('Y-m-d')}}" />
                    <input type="hidden" name="end_date" value="{{date('Y-m-d')}}" />

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                    </div>
                  {{ Form::close() }}
              </div>
          </div>
      </div>
    </div>
      <!-- end customer group modal -->

    <!-- supplier modal -->
    <div id="supplier-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Supplier Report')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                    {!! Form::open(['route' => 'report.supplier', 'method' => 'post']) !!}
                    <?php
                      $lims_supplier_list = DB::table('suppliers')->where('is_active', true)->get();
                    ?>
                      <div class="form-group">
                          <label>{{trans('file.Supplier')}} *</label>
                          <select name="supplier_id" class="selectpicker form-control" required data-live-search="true" id="supplier-id" data-live-search-style="begins" title="Select Supplier...">
                              @foreach($lims_supplier_list as $supplier)
                              <option value="{{$supplier->id}}">{{$supplier->name . ' (' . $supplier->phone_number. ')'}}</option>
                              @endforeach
                          </select>
                      </div>

                      <input type="hidden" name="start_date" value="{{date('Y-m-d')}}" />
                      <input type="hidden" name="end_date" value="{{date('Y-m-d')}}" />

                      <div class="form-group">
                          <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                      </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
    <!-- end supplier modal -->
    @if(!config('database.connections.saleprosaas_landlord'))
    <script type="text/javascript" src="<?php echo asset('vendor/jquery/jquery.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/jquery/jquery-ui.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/jquery/bootstrap-datepicker.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/jquery/jquery.timepicker.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/popper.js/umd/popper.min.js') ?>">
    </script>
    <script type="text/javascript" src="<?php echo asset('vendor/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/bootstrap-toggle/js/bootstrap-toggle.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/bootstrap/js/bootstrap-select.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/keyboard/js/jquery.keyboard.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/keyboard/js/jquery.keyboard.extension-autocomplete.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('js/grasp_mobile_progress_circle-1.0.0.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/jquery.cookie/jquery.cookie.js') ?>">
    </script>
    <script type="text/javascript" src="<?php echo asset('vendor/jquery-validation/jquery.validate.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js')?>"></script>
    @if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl)
      <script type="text/javascript" src="<?php echo asset('js/front_rtl.js') ?>"></script>
    @else
      <script type="text/javascript" src="<?php echo asset('js/front.js') ?>"></script>
    @endif
    <script type="text/javascript" src="<?php echo asset('vendor/daterange/js/moment.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/daterange/js/knockout-3.4.2.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/datatable/jquery.dataTables.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('vendor/datatable/dataTables.bootstrap4.min.js') ?>"></script>

    @else
    <script type="text/javascript" src="<?php echo asset('../../vendor/jquery/jquery.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/jquery/jquery-ui.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/jquery/bootstrap-datepicker.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/jquery/jquery.timepicker.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/popper.js/umd/popper.min.js') ?>">
    </script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/bootstrap/js/bootstrap.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/bootstrap-toggle/js/bootstrap-toggle.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/bootstrap/js/bootstrap-select.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/keyboard/js/jquery.keyboard.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/keyboard/js/jquery.keyboard.extension-autocomplete.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../js/grasp_mobile_progress_circle-1.0.0.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/jquery.cookie/jquery.cookie.js') ?>">
    </script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/jquery-validation/jquery.validate.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js')?>"></script>
    @if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl)
      <script type="text/javascript" src="<?php echo asset('../../js/front_rtl.js') ?>"></script>
    @else
      <script type="text/javascript" src="<?php echo asset('../../js/front.js') ?>"></script>
    @endif
    <script type="text/javascript" src="<?php echo asset('../../vendor/daterange/js/moment.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/daterange/js/knockout-3.4.2.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/jquery.dataTables.min.js') ?>"></script>
    <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/dataTables.bootstrap4.min.js') ?>"></script>
    @endif

    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator ) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/demo/service-worker.js').then(function(registration) {
                    // Registration was successful
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    // registration failed :(
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
    <script type="text/javascript">

          function myFunction() {
              setTimeout(showPage, 150);
          }

          function showPage() {
            document.getElementById("loader").style.display = "none";
            document.getElementById("content").style.display = "";
            $("#lims_productcodeSearch").focus();
          }

          $("div.alert").delay(3000).slideUp(750);
          $('select').selectpicker({
              style: 'btn-link',
          });

        //switch theme code
        var theme = <?php echo json_encode($theme); ?>;
        if(theme == 'dark') {
            $('body').addClass('dark-mode');
            $('#switch-theme i').addClass('dripicons-brightness-low');
        }
        else {
            $('body').removeClass('dark-mode');
            $('#switch-theme i').addClass('dripicons-brightness-max');
        }
        $('#switch-theme').click(function() {
            if(theme == 'light') {
                theme = 'dark';
                var url = <?php echo json_encode(route('switchTheme', 'dark')); ?>;
                $('body').addClass('dark-mode');
                $('#switch-theme i').addClass('dripicons-brightness-low');
            }
            else {
                theme = 'light';
                var url = <?php echo json_encode(route('switchTheme', 'light')); ?>;
                $('body').removeClass('dark-mode');
                $('#switch-theme i').addClass('dripicons-brightness-max');
            }

            $.get(url, function(data) {
                console.log('theme changed to '+theme);
            });
        });

        $("li#notification-icon").on("click", function (argument) {
              $.get('notifications/mark-as-read', function(data) {
                  $("span.notification-number").text(alert_product);
              });
          });

      $("a#add-expense").click(function(e){
        e.preventDefault();
        $('#expense-modal').modal();
      });

      $("a#send-notification").click(function(e){
        e.preventDefault();
        $('#notification-modal').modal();
      });

      $("a#add-account").click(function(e){
        e.preventDefault();
        $('#account-modal').modal();
      });

      $("a#account-statement").click(function(e){
        e.preventDefault();
        $('#account-statement-modal').modal();
      });

      $("a#profitLoss-link").click(function(e){
        e.preventDefault();
        $("#profitLoss-report-form").submit();
      });

      $("a#report-link").click(function(e){
        e.preventDefault();
        $("#product-report-form").submit();
      });

      $("a#purchase-report-link").click(function(e){
        e.preventDefault();
        $("#purchase-report-form").submit();
      });

      $("a#sale-report-link").click(function(e){
        e.preventDefault();
        $("#sale-report-form").submit();
      });

      $("a#payment-report-link").click(function(e){
        e.preventDefault();
        $("#payment-report-form").submit();
      });

      $("a#warehouse-report-link").click(function(e){
        e.preventDefault();
        $('#warehouse-modal').modal();
      });

      $("a#user-report-link").click(function(e){
        e.preventDefault();
        $('#user-modal').modal();
      });

      $("a#customer-report-link").click(function(e){
        e.preventDefault();
        $('#customer-modal').modal();
      });

      $("a#customer-group-report-link").click(function(e){
        e.preventDefault();
        $('#customer-group-modal').modal();
      });

      $("a#supplier-report-link").click(function(e){
        e.preventDefault();
        $('#supplier-modal').modal();
      });

      $("a#due-report-link").click(function(e){
        e.preventDefault();
        $("#customer-due-report-form").submit();
      });

      $("a#supplier-due-report-link").click(function(e){
        e.preventDefault();
        $("#supplier-due-report-form").submit();
      });

      $('.date').datepicker({
         format: "dd-mm-yyyy",
         autoclose: true,
         todayHighlight: true
       });

    </script>
  </body>
</html>
