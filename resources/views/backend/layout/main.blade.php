<!DOCTYPE html>
<html dir="@if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl){{'rtl'}}@endif">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @if(!config('database.connections.saleprosaas_landlord'))
    <link rel="icon" type="image/png" href="{{url('logo', $general_setting->site_favicon)}}" />
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

    <!-- jQuery Circle-->
    <link rel="preload" href="<?php echo asset('css/grasp_mobile_progress_circle-1.0.0.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('css/grasp_mobile_progress_circle-1.0.0.min.css') ?>" rel="stylesheet"></noscript>
    <!-- Custom Scrollbar-->
    <link rel="preload" href="<?php echo asset('vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css') ?>" rel="stylesheet"></noscript>

    @if(Route::current()->getName() != '/')
    <!-- date range stylesheet-->
    <link rel="preload" href="<?php echo asset('vendor/daterange/css/daterangepicker.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/daterange/css/daterangepicker.min.css') ?>" rel="stylesheet"></noscript>
    <!-- table sorter stylesheet-->
    <link rel="preload" href="<?php echo asset('vendor/datatable/dataTables.bootstrap4.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('vendor/datatable/dataTables.bootstrap4.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdn.datatables.net/fixedheader/3.1.6/css/fixedHeader.bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.datatables.net/fixedheader/3.1.6/css/fixedHeader.bootstrap.min.css" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css" rel="stylesheet"></noscript>
    @endif

    <link rel="stylesheet" href="<?php echo asset('css/style.default.css') ?>" id="theme-stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('css/dropzone.css') ?>">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="<?php echo asset('css/custom-'.$general_setting->theme) ?>" type="text/css" id="custom-style">
    <link rel="stylesheet" href="<?php echo asset('css/sidebar-layout-fix.css') ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('css/modal-select-fix.css') ?>" type="text/css">

    @if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl)
      <!-- RTL css -->
      <link rel="stylesheet" href="<?php echo asset('vendor/bootstrap/css/bootstrap-rtl.min.css') ?>" type="text/css">
      <link rel="stylesheet" href="<?php echo asset('css/custom-rtl.css') ?>" type="text/css" id="custom-style">
    @endif
    @else
    <link rel="icon" type="image/png" href="https://admin.dailyfairgh.com/v2/public/logo/20231120015906.png" />
    <title>{{$general_setting->site_title}}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{url('manifest.json')}}">
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

    @if(Route::current()->getName() != '/')
    <!-- date range stylesheet-->
    <link rel="preload" href="<?php echo asset('../../vendor/daterange/css/daterangepicker.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/daterange/css/daterangepicker.min.css') ?>" rel="stylesheet"></noscript>
    <!-- table sorter stylesheet-->
    <link rel="preload" href="<?php echo asset('../../vendor/datatable/dataTables.bootstrap4.min.css') ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="<?php echo asset('../../vendor/datatable/dataTables.bootstrap4.min.css') ?>" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdn.datatables.net/fixedheader/3.1.6/css/fixedHeader.bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.datatables.net/fixedheader/3.1.6/css/fixedHeader.bootstrap.min.css" rel="stylesheet"></noscript>
    <link rel="preload" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css" rel="stylesheet"></noscript>
    @endif

    <link rel="stylesheet" href="<?php echo asset('../../css/style.default.css') ?>" id="theme-stylesheet" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('../../css/dropzone.css') ?>">
    <!-- Custom stylesheet - for your changes-->
    <link rel="stylesheet" href="<?php echo asset('../../css/custom-'.$general_setting->theme) ?>" type="text/css" id="custom-style">
    <link rel="stylesheet" href="<?php echo asset('../../css/sidebar-layout-fix.css') ?>" type="text/css">
    <link rel="stylesheet" href="<?php echo asset('../../css/modal-select-fix.css') ?>" type="text/css">

    @if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl)
      <!-- RTL css -->
      <link rel="stylesheet" href="<?php echo asset('../../vendor/bootstrap/css/bootstrap-rtl.min.css') ?>" type="text/css">
      <link rel="stylesheet" href="<?php echo asset('../../css/custom-rtl.css') ?>" type="text/css" id="custom-style">
    @endif
    @endif
    <!-- Google fonts - Roboto -->
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link href="https://fonts.googleapis.com/css?family=Nunito:400,500,700" rel="stylesheet"></noscript>
    @stack('css')

{!! ToastMagic::styles() !!}

    <style>
      @import url("https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap");
      
      /* Modern Design System */
      :root {
        --primary-gradient: linear-gradient(135deg, #c4c6cf 0%, #299e42 100%);
        --primary-color: #13bd60;
        --secondary-color: #764ba2;
        --accent-color: #ffd700;
        --text-dark:rgb(7, 10, 8);
        --text-light:#13bd60;
        --bg-light: #13bd60;
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
        /* background: var(--bg-light); */
        background-color: #c4d0de!important;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
      }

      body.dark-mode {
        background: #0f3460;
        color: var(--text-light);
      }

      body.dark-mode .card {
        background: #16213e;
        border-color: #333;
      }

      body.dark-mode .card-header {
        background: rgba(102, 126, 234, 0.1);
        border-bottom-color: #333;
      }

      /* Header Navigation Styling */
      nav.navbar {
        background: linear-gradient(to right, #ffffff 0%, #f8f9fa 100%);
        border-bottom: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        padding: 12px 24px;
        position: sticky;
        top: 0;
        z-index: 100;
      }

      /* Top bar: menu btn + brand + nav menu – separated and responsive */
      nav.navbar.navbar-main {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px 20px;
      }

      nav.navbar .menu-btn {
        flex-shrink: 0;
        order: 1;
        min-width: 44px;
        min-height: 44px;
        padding: 10px 12px;
        margin: 0;
        margin-right: 4px;
        background: transparent !important;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease;
      }

      nav.navbar .menu-btn:hover {
        background: rgba(0, 0, 0, 0.06) !important;
      }

      nav.navbar .menu-btn .navbar-toggler-iconx {
        font-size: 1.25rem;
        color: #fff;
      }

      nav.navbar .menu-btn .navbar-toggler-iconx i {
        color: inherit;
      }

      nav.navbar .navbar-brand {
        order: 2;
        flex: 1 1 auto;
        min-width: 0;
        margin: 0;
        padding: 0 8px;
        font-size: 22px;
        font-weight: 800;
        background: var(--primary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -1px;
        display: flex;
        align-items: center;
        text-decoration: none;
      }

      nav.navbar .navbar-brand-title {
        margin: 0;
        font-size: inherit;
        font-weight: inherit;
        color: #212529;
        -webkit-text-fill-color: #212529;
        background: none;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      nav.navbar .nav-menu {
        order: 3;
        margin-left: auto;
        flex-shrink: 0;
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

      /* Header right-sidebar: compact, curvy bottom, no extra whitespace */
      nav.navbar .right-sidebar {
        height: auto !important;
        min-height: 0 !important;
        top: 56px !important;
        padding: 8px 0 14px 0 !important;
        border-radius: 0 0 16px 16px !important;
        max-height: calc(100vh - 72px);
        overflow-y: auto;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
      }
      nav.navbar .right-sidebar li {
        line-height: 40px !important;
        padding: 0 20px !important;
      }
      nav.navbar .right-sidebar li:last-child {
        border-bottom: none !important;
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

      /* Sidebar Navbar */
      .side-navbar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: var(--shadow-md);
        border-radius: 0;
        overflow: hidden;
      }

      .side-navbar li a {
        color: var(--primary-color) !important;
        font-size: 14px;
        font-weight: 500;
        padding: 12px 16px !important;
        border-left: 3px solid transparent;
        transition: var(--transition);
        position: relative;
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
        color: #f2f2f2;
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
        color: var(--primary-color) !important;
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
        color: var(--primary-color) !important;
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
        border-bottom: 1px solid var(--border-color);
        border-radius: 12px 12px 0 0;
        padding: 16px 20px;
        font-weight: 600;
        color: #333333;
        /* color: var(--text-dark); */
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

      /* DataTables top controls layout */
      .dataTables_wrapper > .row:first-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        min-width: 0;
        margin-bottom: 0.5rem;
      }

      .dataTables_wrapper > .row:first-child > * {
        flex-shrink: 0;
      }

      /* Last column (dt-buttons): only as wide as the buttons, no extra space */
      .dataTables_wrapper > .row:first-child > *:last-child {
        flex: 0 0 auto;
        width: auto;
        max-width: none;
      }

      /* dt-buttons btn-group: end right after the last button */
      .dataTables_wrapper .dt-buttons.btn-group {
        width: fit-content;
        max-width: 100%;
        flex-wrap: nowrap;
      }

      /* Let the filter (middle) column shrink so length + buttons + search stay in one row */
      .dataTables_wrapper > .row:first-child > *:nth-child(2) {
        flex-shrink: 1;
        min-width: 140px;
      }

      .dataTables_wrapper .dataTables_filter input {
        min-width: 120px;
      }

      .dataTables_wrapper .dataTables_length,
      .dataTables_wrapper .dataTables_filter,
      .dataTables_wrapper .dt-buttons {
        display: flex;
        align-items: center;
        margin-bottom: 0.25rem;
      }

      .dataTables_wrapper .dt-buttons {
        flex-wrap: nowrap;
      }

      /* Length: prevent select overlapping "records per page" text and avoid header distortion */
      .dataTables_wrapper .dataTables_length label {
        display: inline-flex;
        align-items: center;
        flex-wrap: nowrap;
        gap: 8px;
        margin-bottom: 0;
        white-space: nowrap;
      }
      .dataTables_wrapper .dataTables_length select,
      .dataTables_wrapper .dataTables_length .bootstrap-select {
        width: auto !important;
        min-width: 60px;
        max-width: 80px;
        margin: 0 2px;
      }
      .dataTables_wrapper .dataTables_length .bootstrap-select .dropdown-toggle {
        min-width: 60px;
        max-width: 80px;
      }
      .dataTables_wrapper .dataTables_length .filter-option-inner-inner {
        overflow: hidden;
        text-overflow: ellipsis;
      }
      /* Keep length dropdown above other controls when open */
      .dataTables_wrapper .dataTables_length .bootstrap-select.open .dropdown-menu {
        z-index: 1060;
      }

      .dataTables_wrapper .dataTables_filter label {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 0;
      }

      /* On smaller screens, stack the controls and keep even spacing */
      @media (max-width: 991px) {
        .dataTables_wrapper > .row:first-child {
          flex-direction: column;
          align-items: stretch;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dt-buttons {
          justify-content: space-between;
        }
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

      /* Loader: z-index below modal (10050) so modals stay on top and interactive */
      #loader {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 10030;
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
          padding: 10px 12px;
        }

        .sidebar {
          display: flex;
          flex-direction: column;
          height: 100vh;
        }

        .sidebar .side-menu {
          flex: 1;
          overflow-y: auto;
        }

        nav.navbar.navbar-main {
          gap: 8px 12px;
        }

        nav.navbar .menu-btn {
          min-width: 40px;
          min-height: 40px;
          padding: 8px 10px;
          margin-right: 0;
        }

        nav.navbar .navbar-brand {
          font-size: 1rem;
          padding: 0 6px;
        }

        nav.navbar .navbar-brand-title {
          font-size: 1rem;
        }

        nav.navbar .nav-menu {
          width: 100%;
          margin-left: 0;
          margin-top: 4px;
          padding-top: 8px;
          border-top: 1px solid rgba(0, 0, 0, 0.08);
          justify-content: flex-end;
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

      @media (max-width: 480px) {
        nav.navbar .navbar-brand {
          font-size: 0.9rem;
        }

        nav.navbar .navbar-brand-title {
          font-size: 0.9rem;
        }
      }

      /* .btn-group{
        border: 3px solid #000000!important
      } */

      /* Dark Mode Support */
      @media (prefers-color-scheme: dark) {
        :root {
          --text-dark: #13bd60;;
          --text-light: #13bd60;;
          --bg-light: #13bd60;;
          --border-color: #13bd60;;
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
      .btn-group{
        border: 1px solid #13bd60!important;
        border-radius: 8px!important;
      }

              .ui-autocomplete {
            z-index: 9999 !important;
            max-height: 280px;
            overflow-y: auto;
            background: #1a1a1a !important;
            border: 1px solid #333 !important;
            border-radius: 4px;
        }
        .ui-autocomplete .ui-menu-item {
            border-color: #333 !important;
        }
        .ui-autocomplete .ui-menu-item-wrapper {
            background: #1a1a1a !important;
            color: #e0e0e0 !important;
            padding: 8px 12px;
        }
        .ui-autocomplete .ui-menu-item-wrapper.ui-state-active,
        .ui-autocomplete .ui-menu-item-wrapper:hover {
            background: #333 !important;
            color: #fff !important;
            border-color: #333 !important;
        }

        /* In your stylesheet */
        .right-sidebar .divider {
            height: 1px;
            margin: 5px 0;
            overflow: hidden;
            background-color: #e5e5e5;
        }

    </style>
  </head>

  <body class="@if($theme == 'dark')dark-mode dripicons-brightness-low @endif  @if(Route::current()->getName() == 'sale.pos') pos-page @endif" onload="myFunction()">
    <div id="loader"></div>
      <!-- Side Navbar --> 
      <nav class="side-navbar" style="background-color: #ecf0f4 !important;">
        <span class="brand-big mb-3 mt-2">
            @if($general_setting->site_logo)
            <a href="{{url('/')}}"><img src="{{asset('/images/msoft.png')}}" style="width:125px; height:45px;"></a>
            @else
            <a href="{{url('/')}}"><h1 class="d-inline">{{$general_setting->site_title}}</h1></a>
            @endif
        </span>
        @include('backend.layout.sidebar')
      </nav>

    <div class="page">
        <!-- navbar-->
      @if(Route::current()->getName() != 'sale.pos')
      <header >
        <nav class="navbar navbar-main" style="border-radius: 0px !important;  background: linear-gradient(to right, #13bd60, #f5f8fe) !important;">
          <button class="menu-btn" type="button" aria-label="Toggle menu" id="toggle-btn">
            <span class="navbar-toggler-iconx"><i class="fa fa-bars"></i></span>
          </button>
          <a class="navbar-brand" href="#"><h1 class="navbar-brand-title">{{ $general_setting->site_title }}</h1></a>


           <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
            <!-- <div class="dropdown">
              <a class="btn-pos btn-sm" type="button" data-toggle="dropdown" aria-expanded="false">
                <i class="dripicons-plus"></i>
              </a>
              <ul class="dropdown-menu">
                <?php
                    $category_permission_active = $role_has_permissions_list->where('name', 'category')->first();
                ?>
                @if($category_permission_active)
                <li class="dropdown-item"><a data-toggle="modal" data-target="#category-modal">{{__('file.Add Category')}}</a></li>
                @endif
                <?php
                    $add_permission_active = $role_has_permissions_list->where('name', 'products-add')->first();
                ?>
                @if($add_permission_active)
                <li class="dropdown-item"><a href="{{route('products.create')}}">{{__('file.add_product')}}</a></li>
                @endif
                <?php
                $add_permission_active = $role_has_permissions_list->where('name', 'purchases-add')->first();
                ?>
                @if($add_permission_active)
                <li class="dropdown-item"><a href="{{route('purchases.create')}}">{{trans('file.Add Purchase')}}</a></li>
                @endif
                <?php
                $sale_add_permission_active = $role_has_permissions_list->where('name', 'sales-add')->first();
                ?>
                @if($sale_add_permission_active)
                <li class="dropdown-item"><a href="{{route('sales.create')}}">{{trans('file.Add Sale')}}</a></li>
                @endif
                <?php
                $expense_add_permission_active = $role_has_permissions_list->where('name', 'expenses-add')->first();
                ?>
                @if($expense_add_permission_active)
                <li class="dropdown-item"><a data-toggle="modal" data-target="#expense-modal"> {{trans('file.Add Expense')}}</a></li>
                @endif
                <?php
                $quotation_add_permission_active = $role_has_permissions_list->where('name', 'quotes-add')->first();
                ?>
                @if($quotation_add_permission_active)
                <li class="dropdown-item"><a href="{{route('quotations.create')}}">{{trans('file.Add Quotation')}}</a></li>
                @endif
                <?php
                $transfer_add_permission_active = $role_has_permissions_list->where('name', 'transfers-add')->first();
                ?>
                @if($transfer_add_permission_active)
                <li class="dropdown-item"><a href="{{route('transfers.create')}}">{{trans('file.Add Transfer')}}</a></li>
                @endif
                <?php
                $return_add_permission_active = $role_has_permissions_list->where('name', 'returns-add')->first();
                ?>
                @if($return_add_permission_active)
                <li class="dropdown-item"><a href="#" data-toggle="modal" data-target="#add-sale-return"> {{trans('file.Add Return')}}</a></li>
                @endif
                <?php
                $purchase_return_add_permission_active = $role_has_permissions_list->where('name', 'purchase-return-add')->first();
                ?>
                @if($purchase_return_add_permission_active)
                <li class="dropdown-item"><a href="#" data-toggle="modal" data-target="#add-purchase-return"> {{trans('file.Add Purchase Return')}}</a></li>
                @endif
                <?php
                    $user_add_permission_active = $role_has_permissions_list->where('name', 'users-add')->first();
                ?>
                @if($user_add_permission_active)
                <li class="dropdown-item"><a href="{{route('user.create')}}">{{trans('file.Add User')}}</a></li>
                @endif
                <?php
                    $customer_add_permission_active = $role_has_permissions_list->where('name', 'customers-add')->first();
                ?>
                @if($customer_add_permission_active)
                <li class="dropdown-item"><a href="{{route('customer.create')}}">{{trans('file.Add Customer')}}</a></li>
                @endif
                <?php
                    $biller_add_permission_active = $role_has_permissions_list->where('name', 'billers-add')->first();
                ?>
                @if($biller_add_permission_active)
                <li class="dropdown-item"><a href="{{route('biller.create')}}">{{trans('file.Add Biller')}}</a></li>
                @endif
                <?php
                    $supplier_add_permission_active = $role_has_permissions_list->where('name', 'suppliers-add')->first();
                ?>
                @if($supplier_add_permission_active)
                <li class="dropdown-item"><a href="{{route('supplier.create')}}">{{trans('file.Add Supplier')}}</a></li>
                @endif
              </ul>
            </div>-->
            <?php
                $empty_database_permission_active = $role_has_permissions_list->where('name', 'empty_database')->first();

                $sale_add_permission_active = $role_has_permissions_list->where('name', 'sales-add')->first();

                $product_qty_alert_active = $role_has_permissions_list->where('name', 'product-qty-alert')->first();

                $general_setting_permission_active = $role_has_permissions_list->where('name', 'general_setting')->first();
            ?>
           {{-- @if($sale_add_permission_active)
            <li class="nav-item"><a class="btn-pos btn-sm " href="{{route('sale.pos')}}" style="color:#fff !important;"><i class="dripicons-shopping-bag"></i><span class="text-white"> POS</span></a></li>
            @endif
            --}}
            {{--<li class="nav-item"><a id="switch-theme" data-toggle="tooltip" title="{{trans('file.Switch Theme')}}"><i class="dripicons-brightness-max"></i></a></li>--}}
            <li class="nav-item"><a id="btnFullscreen" data-toggle="tooltip" title="{{trans('file.Full Screen')}}"><i class="dripicons-expand"></i></a></li>
           @if(!Auth::user()->roles->contains(fn($r) => $r->id <= 2)){{'d-none'}}
               {{-- <li class="nav-item"><a href="{{route('cashRegister.index')}}" data-toggle="tooltip" title="{{trans('file.Cash Register List')}}"><i class="dripicons-archive"></i></a></li>--}}
            @endif
            
            @if($product_qty_alert_active && ($alert_product + $dso_alert_product_no + \Auth::user()->unreadNotifications->where('data.reminder_date', date('Y-m-d'))->count() ) > 0)
                <li class="nav-item" id="notification-icon">
                    <a rel="nofollow" data-toggle="tooltip" title="{{__('Notifications')}}" class="nav-link dropdown-item"><i class="dripicons-bell"></i><span class="badge badge-danger notification-number">{{$alert_product + $dso_alert_product_no + \Auth::user()->unreadNotifications->where('data.reminder_date', date('Y-m-d'))->count()}}</span>
                    </a>
                    <ul class="right-sidebar">
                        <li class="notifications">
                            <a href="{{route('report.qtyAlert')}}" class="btn btn-link"> {{$alert_product}} product exceeds alert quantity</a>
                        </li>
                        @if($dso_alert_product_no)
                        <li class="notifications">
                            <a href="{{route('report.dailySaleObjective')}}" class="btn btn-link"> {{$dso_alert_product_no}} product could not fulfill daily sale objective</a>
                        </li>
                        @endif
                        @foreach(\Auth::user()->unreadNotifications->where('data.reminder_date', date('Y-m-d')) as $key => $notification)
                            <li class="notifications">
                                @if($notification->data['document_name'])
                                <a target="_blank" href="{{url('public/documents/notification', $notification->data['document_name'])}}" class="btn btn-link">{{ $notification->data['message'] }}</a>
                                @else
                                <a href="#" class="btn btn-link">{{ $notification->data['message'] }}</a>
                                @endif
                            </li>
                        @endforeach

                        {{-- ✅ Divider + Mark all as read --}}
                        <li class="divider"></li>
                        <li class="notifications mark-all-read">
                            <a href="{{ route('notifications.markAsRead') }}" id="mark-notifications-read" class="btn btn-link">
                                {{ __('Mark all as read') }}
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if(\Auth::user()->unreadNotifications->where('data.reminder_date', date('Y-m-d'))->count() > 0)
                <li class="nav-item" id="notification-icon">
                    <a rel="nofollow" data-toggle="tooltip" title="{{__('Notifications')}}" class="nav-link dropdown-item"><i class="dripicons-bell"></i><span class="badge badge-danger notification-number">{{\Auth::user()->unreadNotifications->where('data.reminder_date', date('Y-m-d'))->count()}}</span>
                    </a>
                    <ul class="right-sidebar">
                        @foreach(\Auth::user()->unreadNotifications->where('data.reminder_date', date('Y-m-d')) as $key => $notification)
                            <li class="notifications">
                                @if($notification->data['document_name'])
                                <a target="_blank" href="{{url('public/documents/notification', $notification->data['document_name'])}}" class="btn btn-link">{{ $notification->data['message'] }}</a>
                                @else
                                <a href="#" class="btn btn-link">{{ $notification->data['message'] }}</a>
                                @endif
                            </li>
                        @endforeach


                        {{-- ✅ Divider + Mark all as read --}}
                        <li class="divider"></li>
                        <li class="notifications mark-all-read">
                            <a href="javascript:void(0)" id="mark-notifications-read" class="btn btn-link">
                                {{ __('Mark all as read') }}
                            </a>
                        </li>
                    </ul>

                </li>
            @endif
            <li class="nav-item">
                    <a rel="nofollow" title="{{trans('file.language')}}" data-toggle="tooltip" class="nav-link dropdown-item"><i class="dripicons-web"></i></a>
                    <ul class="right-sidebar">
                        <li>
                        <a href="{{ url('language_switch/en') }}" class="btn btn-link"> English</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/es') }}" class="btn btn-link"> Español</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/ar') }}" class="btn btn-link"> عربى</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/id') }}" class="btn btn-link"> Bahasa</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/ms') }}" class="btn btn-link"> Malay</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/s_chinese') }}" class="btn btn-link">中国人</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/t_chinese') }}" class="btn btn-link">中國人</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/pt_BR') }}" class="btn btn-link"> Portuguese</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/fr') }}" class="btn btn-link"> Français</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/de') }}" class="btn btn-link"> Deutsche</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/hi') }}" class="btn btn-link"> हिंदी</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/vi') }}" class="btn btn-link"> Tiếng Việt</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/ru') }}" class="btn btn-link"> русский</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/bg') }}" class="btn btn-link"> български</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/tr') }}" class="btn btn-link"> Türk</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/it') }}" class="btn btn-link"> Italiano</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/nl') }}" class="btn btn-link"> Nederlands</a>
                        </li>
                        <li>
                        <a href="{{ url('language_switch/lao') }}" class="btn btn-link"> Lao</a>
                        </li>
                        <li>
                          <a href="{{ url('language_switch/swahili') }}" class="btn btn-link"> Swahili</a>
                        </li>
                    </ul>
            </li>
            <li class="nav-item">
                <a rel="nofollow" data-toggle="tooltip" class="nav-link dropdown-item"><i class="dripicons-user"></i> <span>{{ucfirst(Auth::user()->name)}}</span> <i class="fa fa-angle-down"></i>
                </a>
                <ul class="right-sidebar">
                    <li>
                    <a href="{{route('user.profile', ['id' => Auth::id()])}}"><i class="dripicons-user"></i> {{trans('file.profile')}}</a>
                    </li>
                    @if($general_setting_permission_active)
                    <li>
                    <a href="{{route('setting.general')}}"><i class="dripicons-gear"></i> {{trans('file.settings')}}</a>
                    </li>
                    @endif
                    <li>
                    <a href="{{url('my-transactions/'.date('Y').'/'.date('m'))}}"><i class="dripicons-swap"></i> {{trans('file.My Transaction')}}</a>
                    </li>
                    @if(!Auth::user()->roles->contains(fn($r) => $r->id == 5))
                    <li>
                    <a href="{{url('holidays/my-holiday/'.date('Y').'/'.date('m'))}}"><i class="dripicons-vibrate"></i> {{trans('file.My Holiday')}}</a>
                    </li>
                    @endif
                    @if($empty_database_permission_active)
                    <li>
                    <a onclick="return confirm('Are you sure want to delete? If you do this all of your data will be lost.')" href="{{route('setting.emptyDatabase')}}"><i class="dripicons-stack"></i> {{trans('file.Empty Database')}}</a>
                    </li>
                    @endif
                    <li>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault();
                                        document.getElementById('logout-form').submit();"><i class="dripicons-power"></i>
                        {{trans('file.logout')}}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    </li>
                </ul>
            </li>
            </ul>
        </nav>
      </header>
      @endif


      <div style="display:none" id="content" class="animate-bottom">
            @include('includes.session_message')
            @yield('content')
      </div>

      <footer class="main-footer">
        <div class="container-fluid">
          <div class="row">
            <div class="col-sm-12">
              <p>&copy; {{$general_setting->site_title}} | {{trans('file.Developed')}} {{trans('file.By')}} <span class="external">{{$general_setting->developed_by}}</span> | V {{env('VERSION')}}</p>
            </div>
          </div>
        </div>
      </footer>
    </div>
    <!-- .page closed so modals are body children and appear above backdrop -->

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
                    {!! Form::open(['route' => 'notifications.store', 'method' => 'post', 'files'=> true]) !!}
                      <div class="row">

                          <div class="col-md-4 form-group">
                                <input type="hidden" name="sender_id" value="{{\Auth::id()}}">
                              <label>{{trans('file.User')}} *</label>
                              <select id="receiver_id" name="receiver_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select user...">

                              </select>
                          </div>
                          <div class="col-md-4 form-group">
                                <label>{{trans('file.Reminder Date')}}</label>
                                <input type="text" name="reminder_date" class="form-control date" value="{{date('d-m-Y')}}">
                          </div>
                          <div class="col-md-4 form-group">
                                <label>{{trans('file.Attach Document')}}</label>
                                <input type="file" name="document" class="form-control">
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
                          <label>Parent Category</label> 
                          <div class="input-group flex-nowrap">
                            <select name="parent_id" class="form-control selectpicker" id="parent">
                                <option value="">Select {{trans('file.parent')}}</option>

                                @foreach($categories_list as $category)
                                <option value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                            </select>
                                <div class="input-group-append">
                                    <a href="#category-modal" data-toggle="modal" data-target="#" class="btn btn-default btn-sm ml-0"><i class="dripicons-plus"></i></a>
                                </div>
                            </div>
                      </div>
                      <div class="col-md-6 form-group">
                          <label>Department</label> 
                          <div class="input-group flex-nowrap">
                            <select name="department_id" class="form-control selectpicker" id="parent">
                                <option value="">Select Department</option>
                                @php
                                    $category_department = DB::table('category_departments')->where('is_active', true)->get();
                                @endphp
                                @foreach($category_department as $department)
                                <option value="{{$department->id}}">{{$department->name}}</option>
                                @endforeach
                            </select>
                                <div class="input-group-append">
                                    <a href="#department-modal" data-toggle="modal" data-target="#" class="btn btn-default btn-sm ml-0"><i class="dripicons-plus"></i></a>
                                </div>
                            </div>
                      </div>
                      @if (\Schema::hasColumn('categories', 'woocommerce_category_id'))
                      <div class="col-md-6 form-group mt-4">
                        <h5><input name="is_sync_disable" type="checkbox" id="is_sync_disable" value="1">&nbsp; {{trans('file.Disable Woocommerce Sync')}}</h5>
                      </div>
                      @endif

                      @if(in_array('ecommerce',explode(',',$general_setting->modules)))
                      <div class="col-md-12 mt-3">
                          <h6><strong>{{ __('For Website') }}</strong></h6>
                          <hr>
                      </div>

                      <div class="col-md-6 form-group">
                          <label>{{ __('Icon') }} (SVG format)</label>
                          <input type="file" name="icon" class="form-control">
                      </div>
                      <div class="col-md-6 form-group">
                          <br>
                          <input type="checkbox" name="featured" id="featured" value="1"> <label>{{ __('List on category dropdown') }}</label>
                      </div>
                      @endif
                  </div>

                  @if(in_array('ecommerce',explode(',',$general_setting->modules)))
                  <div class="row">
                      <div class="col-md-12 mt-3">
                          <h6><strong>{{ __('For SEO') }}</strong></h6>
                          <hr>
                      </div>
                      <div class="col-md-12 form-group">
                          <label>{{ __('Meta Title') }}</label>
                          {{Form::text('page_title',null,array('class' => 'form-control', 'placeholder' => 'Meta Title...'))}}
                      </div>
                      <div class="col-md-12 form-group">
                          <label>{{ __('Meta Description') }}</label>
                          {{Form::text('short_description',null,array('class' => 'form-control', 'placeholder' => 'Meta Description...'))}}
                      </div>
                  </div>
                  @endif
                  <div class="form-group">
                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                  </div>
              </div>
              {{ Form::close() }}
            </div>
          </div>
      </div>
      <!-- Category Modal -->

      <!--Department Modal -->
           <div id="department-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
          <div role="document" class="modal-dialog">
            <div class="modal-content">
              {!! Form::open(['route' => 'category-department.store', 'method' => 'post', 'files' => true]) !!}
              <div class="modal-header">
                <h5 id="exampleModalLabel" class="modal-title">Add Department</h5>
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


                     
                  </div>


                  <div class="form-group">
                    <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                  </div>
              </div>
              {{ Form::close() }}
            </div>
          </div>
      </div>
      <!-- end Department Modal -->

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
                      <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Name </label>
                            <input type="text" name="name" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{trans('file.Date')}}</label>
                            <input type="text" name="created_at" class="form-control date" placeholder="Choose date"/>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{trans('file.Expense Category')}} *</label>
                            <select name="expense_category_id" id="expense_category_modal_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select Expense Category...">

                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{trans('file.Warehouse')}} *</label>
                            <select name="warehouse_id" id="expense_modal_warehouse_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select Warehouse...">

                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{trans('file.Amount')}} *</label>
                            <input type="number" name="amount" step="any" required class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label> {{trans('file.Account')}}</label>
                            <select class="form-control selectpicker" name="account_id" id="expense_modal_account_id">

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

      <!-- account modal -->
      <div id="account-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Account')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                    {!! Form::open(['route' => 'accounts.store', 'method' => 'post']) !!}
                      <div class="form-group">
                          <label>{{trans('file.Account No')}} *</label>
                          <input type="text" name="account_no" required class="form-control">
                      </div>
                      <div class="form-group">
                          <label>{{trans('file.name')}} *</label>
                          <input type="text" name="name" required class="form-control">
                      </div>
                      <div class="form-group">
                          <label>{{trans('file.Initial Balance')}}</label>
                          <input type="number" name="initial_balance" step="any" class="form-control">
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
      <!-- end account modal -->

      <!-- account statement modal -->
      <div id="account-statement-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Account Statement')}}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                  <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                    {!! Form::open(['route' => 'accounts.statement', 'method' => 'post']) !!}
                      <div class="row">
                        <div class="col-md-6 form-group">
                            <label> {{trans('file.Account')}}</label>
                            <select class="form-control selectpicker" name="account_id" id="account_statement_modal_id">

                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label> {{trans('file.Type')}}</label>
                            <select class="form-control selectpicker" name="type">
                                <option value="0">{{trans('file.All')}}</option>
                                <option value="1">{{trans('file.Debit')}}</option>
                                <option value="2">{{trans('file.Credit')}}</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>{{trans('file.Choose Your Date')}}</label>
                            <div class="input-group">
                                <input type="text" class="account-statement-daterangepicker-field form-control" required />
                                <input type="hidden" name="start_date" />
                                <input type="hidden" name="end_date" />
                            </div>
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
      <!-- end account statement modal -->

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

                      <div class="form-group">
                          <label>{{trans('file.Warehouse')}} *</label>
                          <select name="warehouse_id" id="warehouse_modal_id" class="selectpicker form-control" required data-live-search="true" id="warehouse-id" data-live-search-style="begins" title="Select warehouse...">

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

                      <div class="form-group">
                          <label>{{trans('file.User')}} *</label>
                          <select name="user_id" id="user_modal_id" class="selectpicker form-control" required data-live-search="true" id="user-id" data-live-search-style="begins" title="Select user...">
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

                      <div class="form-group">
                          <label>{{trans('file.customer')}} *</label>
                          <select name="customer_id" id="customer_modal_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select customer...">

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

                      <div class="form-group">
                          <label>{{trans('file.Customer Group')}} *</label>
                          <select name="customer_group_id" id="customer_group_modal_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select customer group...">

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
                   
                        
                        <!-- use a model to get all unique company names -->
                        <div class="form-group">
                            <label>Company *</label>
                            <select name="company_name" id="company_modal_id" class="selectpicker form-control" required data-live-search="true" data-live-search-style="begins" title="Select Company...">
                                <option value="">Loading companies...</option>
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
        @if(Route::current()->getName() == 'sale.pos')
        <script type="text/javascript" src="<?php echo asset('vendor/keyboard/js/jquery.keyboard.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/keyboard/js/jquery.keyboard.extension-autocomplete.js') ?>"></script>
        @endif
        <script type="text/javascript" src="<?php echo asset('js/grasp_mobile_progress_circle-1.0.0.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/jquery.cookie/jquery.cookie.js') ?>">
        </script>
        <script type="text/javascript" src="<?php echo asset('vendor/chart.js/Chart.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('js/charts-custom.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/jquery-validation/jquery.validate.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js')?>"></script>
        @if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl)
          <script type="text/javascript" src="<?php echo asset('js/front_rtl.js') ?>"></script>
        @else
          <script type="text/javascript" src="<?php echo asset('js/front.js') ?>"></script>
        @endif

        @if(Route::current()->getName() != '/')
        <script type="text/javascript" src="<?php echo asset('vendor/daterange/js/moment.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/daterange/js/knockout-3.4.2.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/daterange/js/daterangepicker.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/tinymce/js/tinymce/tinymce.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('js/dropzone.js') ?>"></script>

        <!-- table sorter js-->
        @if( Config::get('app.locale') == 'ar')
            <script type="text/javascript" src="<?php echo asset('vendor/datatable/pdfmake_arabic.min.js') ?>"></script>
            <script type="text/javascript" src="<?php echo asset('vendor/datatable/vfs_fonts_arabic.js') ?>"></script>
        @else
            <script type="text/javascript" src="<?php echo asset('vendor/datatable/pdfmake.min.js') ?>"></script>
            <script type="text/javascript" src="<?php echo asset('vendor/datatable/vfs_fonts.js') ?>"></script>
        @endif
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/jquery.dataTables.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/dataTables.bootstrap4.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/dataTables.buttons.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/jszip.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/buttons.bootstrap4.min.js') ?>">"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/buttons.colVis.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/buttons.html5.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/buttons.printnew.js') ?>"></script>

        <script type="text/javascript" src="<?php echo asset('vendor/datatable/sum().js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('vendor/datatable/dataTables.checkboxes.min.js') ?>"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>
        @endif
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

        <script type="text/javascript" src="<?php echo asset('../../js/grasp_mobile_progress_circle-1.0.0.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/jquery.cookie/jquery.cookie.js') ?>">
        </script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/chart.js/Chart.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../js/charts-custom.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/jquery-validation/jquery.validate.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js')?>"></script>
        @if( Config::get('app.locale') == 'ar' || $general_setting->is_rtl)
          <script type="text/javascript" src="<?php echo asset('../../js/front_rtl.js') ?>"></script>
        @else
          <script type="text/javascript" src="<?php echo asset('../../js/front.js') ?>"></script>
        @endif

        @if(Route::current()->getName() != '/')
        <script type="text/javascript" src="<?php echo asset('../../vendor/daterange/js/moment.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/daterange/js/knockout-3.4.2.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/daterange/js/daterangepicker.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/tinymce/js/tinymce/tinymce.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../js/dropzone.js') ?>"></script>

        <!-- table sorter js-->
        @if( Config::get('app.locale') == 'ar')
            <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/pdfmake_arabic.min.js') ?>"></script>
            <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/vfs_fonts_arabic.js') ?>"></script>
        @else
            <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/pdfmake.min.js') ?>"></script>
            <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/vfs_fonts.js') ?>"></script>
        @endif
        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/jquery.dataTables.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/dataTables.bootstrap4.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/dataTables.buttons.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/buttons.bootstrap4.min.js') ?>">"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/buttons.colVis.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/buttons.html5.min.js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/buttons.printnew.js') ?>"></script>

        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/sum().js') ?>"></script>
        <script type="text/javascript" src="<?php echo asset('../../vendor/datatable/dataTables.checkboxes.min.js') ?>"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/fixedheader/3.1.6/js/dataTables.fixedHeader.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
        <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>
        @endif
    @endif
    @stack('scripts')
    <script>
      $(document).ready(function() {
          // Load companies
          $.ajax({
              url: '{{ route("supplier.allCompanies") }}',
              type: 'GET',
              success: function(response) {
                  $('#company_modal_id').html(response);
                  $('#company_modal_id').selectpicker('refresh');
              },
              error: function() {
                  $('#company_modal_id').html('<option value="">Error loading companies</option>');
                  $('#company_modal_id').selectpicker('refresh');
              }
          });
      });
    </script>
    <script>
        if ('serviceWorker' in navigator ) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/salepro/service-worker.js').then(function(registration) {
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

        var alert_product = <?php echo json_encode($alert_product) ?>;

      if ($(window).outerWidth() > 1199) {
          $('nav.side-navbar').removeClass('shrink');
      }

      function myFunction() {
          setTimeout(showPage, 100);
      }

      function showPage() {
        document.getElementById("loader").style.display = "none";
        document.getElementById("content").style.display = "block";
      }

      /* Ensure loader is hidden and modal is moved to body so it appears above backdrop */
      $(document).on('show.bs.modal', '.modal', function () {
        $('#loader').css('display', 'none');
        var $modal = $(this);
        if ($modal.parent().length && !$modal.parent().is('body')) {
          $modal.appendTo('body');
        }
      });

      $("div.alert:not(#update-alert-section)").delay(4000).slideUp(800);

      function confirmDelete() {
          if (confirm("Are you sure want to delete?")) {
              return true;
          }
          return false;
      }

      $("li#notification-icon").on("click", function (argument) {
          $.get('notifications/mark-as-read', function(data) {
              $("span.notification-number").text(alert_product);
          });
      });

      $("#add-expense").click(function(e){
        e.preventDefault();
        console.log('test');
        $('#loader').css('display','block');
        $.ajax({
          url: "{{route('expense_category.all')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#expense_category_modal_id').html(data);
            $('.selectpicker').selectpicker('refresh');
          }
        });

        $.ajax({
          url: "{{route('warehouse.all')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#expense_modal_warehouse_id').html(data);
            $('.selectpicker').selectpicker('refresh');
          }
        });

        $.ajax({
          url: "{{route('account.all')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#expense_modal_account_id').html(data);
            $('.selectpicker').selectpicker('refresh');
            $('#loader').css('display','none');
            $('#expense-modal').modal();
          }
        });
      });

      $("a#send-notification").click(function(e){
        e.preventDefault();
        $('#loader').css('display','block');
        $.ajax({
          url: "{{route('user.notification')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#receiver_id').html(data);
            $('.selectpicker').selectpicker('refresh');
            $('#loader').css('display','none');
            $('#notification-modal').modal();
          }
        });

      });

      $("a#add-account").click(function(e){
        e.preventDefault();
        $('#account-modal').modal();
      });

      $("a#account-statement").click(function(e){
        e.preventDefault();
        $('#loader').css('display','block');
        $.ajax({
          url: "{{route('account.all')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#account_statement_modal_id').html(data);
            $('.selectpicker').selectpicker('refresh');
            $('#loader').css('display','none');
            $('#account-statement-modal').modal();
          }
        });
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
      $("a#sale-report-chart-link").click(function(e){
        e.preventDefault();
        $("#sale-report-chart-form").submit();
      });

      $("a#payment-report-link").click(function(e){
        e.preventDefault();
        $("#payment-report-form").submit();
      });

      $("a#warehouse-report-link").click(function(e){
        e.preventDefault();
        $('#loader').css('display','block');
        $.ajax({
          url: "{{route('warehouse.all')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#warehouse_modal_id').html(data);
            $('.selectpicker').selectpicker('refresh');
            $('#loader').css('display','none');
            $('#warehouse-modal').modal();
          }
        });

      });

      $("a#user-report-link").click(function(e){
        e.preventDefault();
        $('#loader').css('display','block');
        $.ajax({
          url: "{{route('user.all')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#user_modal_id').html(data);
            $('.selectpicker').selectpicker('refresh');
            $('#loader').css('display','none');
            $('#user-modal').modal();
          }
        });

      });

      $("a#customer-report-link").click(function(e){
        e.preventDefault();
        $('#loader').css('display','block');
        $.ajax({
          url: "{{route('customer.all')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#customer_modal_id').html(data);
            $('.selectpicker').selectpicker('refresh');
            $('#loader').css('display','none');
            $('#customer-modal').modal();
          }
        });
      });

      $("a#customer-group-report-link").click(function(e){
        e.preventDefault();
        $('#loader').css('display','block');
        $.ajax({
          url: "{{route('customer_group.all')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#customer_group_modal_id').html(data);
            $('.selectpicker').selectpicker('refresh');
            $('#loader').css('display','none');
            $('#customer-group-modal').modal();
          }
        });
      });

      $("a#supplier-report-link").click(function(e){
        e.preventDefault();
        $('#loader').css('display','block');
        $.ajax({
          url: "{{route('supplier.allCompanies')}}",
          type: 'GET',
          dataType: 'json',
          success: function(data) {
            $('#company_modal_id').html(data);
            $('.selectpicker').selectpicker('refresh');
            $('#loader').css('display','none');
            $('#supplier-modal').modal();
          }
        });

      });

      $("a#due-report-link").click(function(e){
        e.preventDefault();
        $("#customer-due-report-form").submit();
      });

      $("a#supplier-due-report-link").click(function(e){
        e.preventDefault();
        $("#supplier-due-report-form").submit();
      });

      $(".account-statement-daterangepicker-field").daterangepicker({
          callback: function(startDate, endDate, period){
            var start_date = startDate.format('YYYY-MM-DD');
            var end_date = endDate.format('YYYY-MM-DD');
            var title = start_date + ' To ' + end_date;
            $(this).val(title);
            $('#account-statement-modal input[name="start_date"]').val(start_date);
            $('#account-statement-modal input[name="end_date"]').val(end_date);
          }
      });

      $('.date').datepicker({
         format: "dd-mm-yyyy",
         autoclose: true,
         todayHighlight: true
       });

      $('.selectpicker').selectpicker({
          style: 'btn-link',
      });
    </script>
    {!! ToastMagic::scripts() !!}

  </body>
</html>
