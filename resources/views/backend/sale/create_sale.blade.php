@extends('backend.layout.top-header') 
@section('content')
@if($errors->has('phone_number'))
<div class="alert alert-danger alert-dismissible text-center">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ $errors->first('phone_number') }}</div>
@endif
@if(session()->has('message'))
    <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
@endif
@if(session()->has('not_permitted'))
  <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
@endif

<link rel="stylesheet" href="{{ asset('css/pos-layout.css') }}" type="text/css">
    {!! ToastMagic::styles() !!}
    <style>
        /* Remove all default spacing */
        * {
            margin: 0;
            padding: 0;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
            height: 100%;
            min-height: 100vh;  
            padding-bottom: 0 !important;
        }

        header {
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border: none !important;
            height: auto !important;
            display: block !important;
            min-height: auto !important;
        }

        nav.navbar {
            margin: 0 !important;
            padding: 12px 24px !important;
        }

   

        .container-fluid {
            padding: 0 !important;
            margin: 0 !important;
        }

        .row {
            margin: 0 !important;
        }

        .pos-page {
            min-height: auto !important;
            background: white !important;
            padding-bottom: 0 !important;
        }

        /* Sidebar toggle and main content layout handled by pos-layout.css */

        /* Remove all space-creating elements */
        .page,
        .pos-page::before,
        .pos-page::after {
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            min-height: auto !important;
            padding-bottom: 0 !important;
            overflow: hidden;
        }

        /* Ensure navbar is compact */
        header nav {
            height: auto !important;
            min-height: auto !important;
        }

        /* Remove all bottom spacing */
        .card,
        .card-body,
        .card-footer,
        section,
        .container-fluid > div {
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }

        body.pos-page {
            padding-bottom: 0 !important;
            overflow-y: auto;
            background: white !important;
        }

        body {
            padding-bottom: 0 !important;
            padding: 0 !important;
        }

        
        #product-table td p {
              color:green !important;  
        }
        #product-table td {
            background-color:#e6f5ff !important;

            border: none;
            border-top-width: medium;
            border-top-style: none;
            border-top-color: currentcolor;
            border-right-width: medium;
            border-right-style: none;
            border-right-color: currentcolor;
            border-bottom-width: medium;
            border-bottom-style: none;
            border-bottom-color: currentcolor;
            border-left-width: medium;
            border-left-style: none;
            border-left-color: currentcolor;
            border-right: 1px solid #e4e6fc;
            border-bottom: 1px solid #e4e6fc;
        }

        .form-control{
          box-shadow: rgba(0, 0, 0, 0.05) 0px 0px 0px 1px;
        }
        select{
            color:red !important;
        }
        .dripicons{
            color:#fff !important;
        }

        /* Totals section: nicer labels and values */
        .totals .col-sm-4 {
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.25rem;
            border-radius: 8px;
            transition: background 0.2s ease;
        }
        .totals .col-sm-4:hover {
            background: rgba(19, 189, 96, 0.06);
        }
        .totals-title {
            font-weight: 600;
            font-size: 0.875rem;
            letter-spacing: 0.02em;
            color: inherit;
            opacity: 0.95;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .totals .col-sm-4 > span:not(.totals-title) {
            font-weight: 700;
            font-size: 1rem;
            margin-left: 4px;
        }
        .totals-title .btn {
            padding: 0 2px;
            vertical-align: middle;
        }
        .totals-title .btn:hover {
            opacity: 0.9;
        }
        /* Totals title icons: gradient color */
        .totals-title i,
        .totals-title .btn i {
            background:rgb(199, 66, 33) !important;
            -webkit-background-clip: text !important;
            background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            color: transparent !important;
        }
        .column-5{
            margin-bottom:15px !important;
        }
        .payment-amount h2 {
            color: #058c49;
            font-size: 1.5rem;
            line-height: 2;
            margin-bottom: 0;
        }
        :root {
            --theme-color:#00172D;
        }
        .transition-all {
            transition: all 0.3s ease;
        }

        /* Target only #myTable */
       .btn svg {vertical-align: middle; width: 16px}
       button.close svg {vertical-align: middle; width: 26px}

        /* Remove white space at bottom */
        .card-body {
            padding-bottom: 0 !important;
        }

        .container-fluid > .row {
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }

        #content,
        .animate-bottom {
            padding-bottom: 0 !important;
            margin-bottom: 0 !important;
        }


            /* Make body a vertical flex container that fills the viewport */
        body {
            display: flex !important;
            flex-direction: column !important;
            min-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* #content stays block, but grows to fill available space */
        #content {
            flex: 1 0 auto !important;   /* expands to fill height */
            display: block !important;    /* keep block layout */
            width: 100%;
        }

        /* Make the forms section fill the expanded #content */
        section.forms {
            height: 100%;                  /* fill its parent (#content) */
            display: flex;
            flex-direction: column;
        }

        /* Ensure the inner container also stretches */
        section.forms .container-fluid {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
        }

        section.forms .row {
            flex: 1 0 auto;
        }

        /* Ensure product search autocomplete dropdown is visible above sidebar/modal - black/dark theme */
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
        #side-main-menu > li > a
        {
                color: green !important;
        }
        .side-navbar li ul li a {
            color: green!important;
        }
        #side-main-menu i {
            color: green !important;
        }
        #side-main-menu i:hover {
            color: green !important;
        }
        #side-main-menu i:active {
            color: green !important;
        }
        #side-main-menu i:focus {
            color: green !important;
        }

        /* Payment buttons strip: responsive grid, good-looking */
        .pos-payment-buttons {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
            padding: 0.75rem;
            align-content: start;
        }
        @media (min-width: 400px) {
            .pos-payment-buttons { grid-template-columns: repeat(2, 1fr); }
        }
        @media (min-width: 576px) {
            .pos-payment-buttons { padding: 0.5rem; gap: 0.4rem; }
        }
        .pos-payment-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 56px;
            padding: 0.5rem 0.35rem;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.08);
            font-size: 0.75rem;
            font-weight: 600;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            cursor: pointer;
            text-align: center;
            line-height: 1.2;
        }
        .pos-payment-btn i {
            display: block;
            font-size: 1.25rem;
            margin-bottom: 0.2rem;
        }
        .pos-payment-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .pos-payment-btn:active {
            transform: translateY(0);
        }
        .pos-payment-btn--method {
            background: #f8f9fa !important;
            color: #1a1a1a !important;
        }
        .pos-payment-btn--method:hover {
            background: #e9ecef !important;
        }
        .pos-payment-btn--hold {
            background: #f8f9fa !important;
            color: #495057 !important;
        }
        .pos-payment-btn--cancel {
            background: #dc3545 !important;
            color: #fff !important;
            border-color: #c82333;
        }
        .pos-payment-btn--cancel:hover {
            background: #c82333 !important;
            color: #fff !important;
        }
        .pos-payment-btn--recent {
            background: #ffc107 !important;
            color: #212529 !important;
            border-color: #e0a800;
        }
        .pos-payment-btn--recent:hover {
            background: #e0a800 !important;
            color: #212529 !important;
        }
        .pos-payment-btn--points {
            background: transparent !important;
            color: #1a1a1a !important;
            border: 1px dashed rgba(0,0,0,0.2);
        }

    </style>

    <!-- Side Navbar -->
    <nav class="side-navbar shrink" style="background-color: #ecf0f4 !important;">
    <span class="brand-big mb-3">
        @if($general_setting->site_logo)
        <a href="{{url('/')}}"> <a href="{{url('/')}}"><img src="{{asset('/images/msoft.png')}}" style="width:125px; height:45px;"></a></a>
        @else
        <a href="{{url('/')}}"><h1 class="d-inline">{{$general_setting->site_title}}</h1></a>
        @endif
        
      <a  href="#" class="menu-btn float-end" onclick="$('.side-navbar').addClass('shrink');"><i class="fa fa-times text-danger"> </i></a>
      
      
    </span>

    @include('backend.layout.sidebar')
</nav>
 <!-- navbar-->
<header>
    <nav class="navbar" style="border-radius: 0px !important;  background: linear-gradient(to right, #13bd60, #f5f8fe) !important;">

        <a id="toggle-btn" href="#" class="menu-btn"><i class="fa fa-bars"> </i></a>

        <span class="brand-big mb-3 mt-2">
            <a href="{{url('/')}}"><h1 class="d-inline">{{$general_setting->site_title}}</h1></a>
        </span>

        <div class="navbar-header">
            <ul class="nav-menu list-unstyled d-flex flex-md-row align-items-md-center">
            <div class="dropdown">
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
            </div>
            <li class="nav-item ml-4"><a id="btnFullscreen" data-toggle="tooltip" title="Full Screen"><i class="dripicons-expand"></i></a></li>
            <li class="nav-item">
                <a href="#" id="launch-customer-display" data-toggle="tooltip" title="Launch Customer Display">
                    <i class="dripicons-device-desktop"></i>
                </a>
            </li>
            <?php
                $general_setting_permission = $permission_list->where('name', 'general_setting')->first();
                $general_setting_permission_active = DB::table('role_has_permissions')->where([
                            ['permission_id', $general_setting_permission->id],
                            ['role_id', Auth::user()->role_id]
                        ])->first();

                $pos_setting_permission = $permission_list->where('name', 'pos_setting')->first();

                $pos_setting_permission_active = DB::table('role_has_permissions')->where([
                    ['permission_id', $pos_setting_permission->id],
                    ['role_id', Auth::user()->role_id]
                ])->first();
            ?>
            @if($pos_setting_permission_active)
            <li class="nav-item"><a class="dropdown-item" data-toggle="tooltip" href="{{route('setting.pos')}}" title="{{trans('file.POS Setting')}}"><i class="dripicons-gear"></i></a> </li>
            @endif
            <li class="nav-item">
                <a href="{{route('sales.printLastReciept')}}" data-toggle="tooltip" title="{{trans('file.Print Last Reciept')}}"><i class="dripicons-print"></i></a>
            </li>
            <li class="nav-item">
                <a href="" id="register-details-btn" data-toggle="tooltip" title="{{trans('file.Cash Register Details')}}"><i class="dripicons-briefcase"></i></a>
            </li>
            <?php
                $today_sale_permission = $permission_list->where('name', 'today_sale')->first();
                $today_sale_permission_active = DB::table('role_has_permissions')->where([
                            ['permission_id', $today_sale_permission->id],
                            ['role_id', Auth::user()->role_id]
                        ])->first();

                $today_profit_permission = $permission_list->where('name', 'today_profit')->first();
                $today_profit_permission_active = DB::table('role_has_permissions')->where([
                            ['permission_id', $today_profit_permission->id],
                            ['role_id', Auth::user()->role_id]
                        ])->first();
            ?>

            @if($today_sale_permission_active)
            <li class="nav-item">
                <a href="" id="today-sale-btn" data-toggle="tooltip" title="{{trans('file.Today Sale')}}"><i class="dripicons-shopping-bag"></i></a>
            </li>
            @endif
            @if($today_profit_permission_active)
            <li class="nav-item">
                <a href="" id="today-profit-btn" data-toggle="tooltip" title="{{trans('file.Today Profit')}}"><i class="dripicons-graph-line"></i></a>
            </li>
            @endif
            @if(($alert_product + count(\Auth::user()->unreadNotifications)) > 0)
            <li class="nav-item" id="notification-icon">
                    <a rel="nofollow" data-toggle="tooltip" title="{{__('Notifications')}}" class="nav-link dropdown-item"><i class="dripicons-bell"></i><span class="badge badge-danger notification-number">{{$alert_product + count(\Auth::user()->unreadNotifications)}}</span>
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </a>
                    <ul class="right-sidebar" user="menu">
                        <li class="notifications">
                        <a href="{{route('report.qtyAlert')}}" class="btn btn-link">{{$alert_product}} product exceeds alert quantity</a>
                        </li>
                        @foreach(\Auth::user()->unreadNotifications as $key => $notification)
                            <li class="notifications">
                                <a href="#" class="btn btn-link">{{ $notification->data['message'] }}</a>
                            </li>
                        @endforeach
                    </ul>
            </li>
            @endif
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
                    @if(!Auth::user()->roles->contains(fn($role) => $role->id ==5))
                    <li>
                        <a href="{{url('holidays/my-holiday/'.date('Y').'/'.date('m'))}}"><i class="dripicons-vibrate"></i> {{trans('file.My Holiday')}}</a>
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
        </div>
    </nav>
</header>
<section class="forms pos-section">
    <div class="container-fluid">

        <div class="row">
            <audio id="mysoundclip1" preload="auto">
                <source src="{{url('beep/beep-timber.mp3')}}"></source>
            </audio>
            <audio id="mysoundclip2" preload="auto">
                <source src="{{url('beep/beep-07.mp3')}}"></source>
            </audio>
            <div class="col-md-2 order-3 order-md-1" style="border-radius: 0px !important;  background: linear-gradient(to bottom, #13bd60, #f5f8fe) !important;">
                <div class="pos-payment-buttons">
                        @if(in_array("cash",$options))
                        <button type="button" class="pos-payment-btn pos-payment-btn--method payment-btn" data-toggle="modal" data-target="#add-payment" id="cash-btn"><i class="fa fa-money"></i> {{trans('file.Cash')}}</button>
                        @endif
                        @if(in_array("card",$options))
                        <button type="button" class="pos-payment-btn pos-payment-btn--method payment-btn" data-toggle="modal" data-target="#add-payment" id="credit-card-btn"><i class="fa fa-credit-card"></i> {{trans('file.Card')}}</button>
                        @endif
                        @if(in_array("paypal",$options) && $lims_pos_setting_data && (strlen($lims_pos_setting_data->paypal_live_api_username)>0) && (strlen($lims_pos_setting_data->paypal_live_api_password)>0) && (strlen($lims_pos_setting_data->paypal_live_api_secret)>0))
                        <button type="button" class="pos-payment-btn pos-payment-btn--method payment-btn" data-toggle="modal" data-target="#add-payment" id="paypal-btn"><i class="fa fa-paypal"></i> {{trans('file.PayPal')}}</button>
                        @endif
                        <button type="button" class="pos-payment-btn pos-payment-btn--hold" id="draft-btn"><i class="dripicons-flag"></i> Hold</button>
                        @if(in_array("cheque",$options))
                        <button type="button" class="pos-payment-btn pos-payment-btn--method payment-btn" data-toggle="modal" data-target="#add-payment" id="cheque-btn"><i class="fa fa-money"></i> {{trans('file.Cheque')}}</button>
                        @endif
                        @if(in_array("gift_card",$options))
                        <button type="button" class="pos-payment-btn pos-payment-btn--method payment-btn" data-toggle="modal" data-target="#add-payment" id="gift-card-btn"><i class="fa fa-credit-card-alt"></i> {{trans('file.Gift Card')}}</button>
                        @endif
                        @if(in_array("deposit",$options))
                        <button type="button" class="pos-payment-btn pos-payment-btn--method payment-btn" data-toggle="modal" data-target="#add-payment" id="deposit-btn"><i class="fa fa-university"></i> {{trans('file.Deposit')}}</button>
                        @endif
                        
                        @if(in_array("mobile_money",$options))
                        <button type="button" class="pos-payment-btn pos-payment-btn--method payment-btn" data-toggle="modal" data-target="#add-payment" id="mobile_money-btn"><i class="fa fa-mobile"></i> Mobile Money</button>
                        @endif
                        @if($lims_reward_point_setting_data && $lims_reward_point_setting_data->is_active && in_array("points",$options))
                        <button type="button" class="pos-payment-btn pos-payment-btn--points payment-btn" data-toggle="modal" data-target="#add-payment" id="point-btn"><i class="dripicons-rocket"></i> {{trans('file.Points')}}</button>
                        @endif
                        <button type="button" class="pos-payment-btn pos-payment-btn--cancel" id="cancel-btn" onclick="return confirmCancel()"><i class="fa fa-close"></i> {{trans('file.Cancel')}}</button>
                        <button type="button" class="pos-payment-btn pos-payment-btn--recent" data-toggle="modal" data-target="#recentTransaction"><i class="dripicons-clock"></i> {{trans('file.Recent Transaction')}}</button>
                </div>
            </div>
            <div class="col-md-10 col-lg-10 order-2 order-md-2 transition-all" id="main-column"  style="background-color:#f5f5f0 !important;">
                <div class="card " style="margin-top: 10px; background-color:#f5f5f0 !important;">
                    <div class="card-body" style="padding-bottom: 0px !important;">
                        {!! Form::open(['route' => 'sales.store', 'method' => 'post', 'files' => true, 'class' => 'payment-form']) !!}
                        @php
                            if($lims_pos_setting_data)
                                $keybord_active = $lims_pos_setting_data->keybord_active;
                            else
                                $keybord_active = 0;

                            $customer_active = DB::table('permissions')
                              ->join('role_has_permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
                              ->where([
                                ['permissions.name', 'customers-add'],
                                ['role_id', \Auth::user()->role_id] ])->first();

                            if($lims_sale_data->coupon_id)
                                $lims_coupon_data = DB::table('coupons')->find($lims_sale_data->coupon_id);
                        @endphp
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                          <input type="text" name="created_at" class="form-control date" placeholder="Choose date" onkeyup='saveValue(this);'/>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                          <input type="text" id="reference-no" name="reference_no" class="form-control" placeholder="Type reference number" onkeyup='saveValue(this);'/>
                                      </div>
                                      @if($errors->has('reference_no'))
                                       <span>
                                           <strong>{{ $errors->first('reference_no') }}</strong>
                                        </span>
                                        @endif
                                    </div>
                                    @if($lims_pos_setting_data->is_table)
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                @if($lims_pos_setting_data)
                                                <input type="hidden" name="warehouse_id_hidden" value="{{$lims_pos_setting_data->warehouse_id}}">
                                                @endif
                                                <select required id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select warehouse...">
                                                    @foreach($lims_warehouse_list as $warehouse)
                                                    <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <select required id="table_id" name="table_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select table...">
                                                    @foreach($lims_table_list as $table)
                                                    <option value="{{$table->id}}">{{$table->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                @if($lims_pos_setting_data)
                                                <input type="hidden" name="warehouse_id_hidden" value="{{$lims_pos_setting_data->warehouse_id}}">
                                                @endif
                                                <select required id="warehouse_id" name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select warehouse...">
                                                    @foreach($lims_warehouse_list as $warehouse)
                                                    <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            @if($lims_pos_setting_data)
                                            <input type="hidden" name="biller_id_hidden" value="{{$lims_pos_setting_data->biller_id}}">
                                            @endif
                                            <select required id="biller_id" name="biller_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select Biller...">
                                            @foreach($lims_biller_list as $biller)
                                            <option value="{{$biller->id}}">{{$biller->name . ' (' . $biller->company_name . ')'}}</option>
                                            @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            @if($lims_pos_setting_data)
                                            <input type="hidden" name="customer_id_hidden" value="{{$lims_pos_setting_data->customer_id}}">
                                            @endif

        <div class="input-group pos">
            @php
                // Determine default selected customer
                $selectedCustomerId = $lims_sale_data->customer_id ?? $lims_pos_setting_data->customer_id ?? null;

         
            @endphp

            <select required name="customer_id" id="customer_id"
                    class="selectpicker form-control"
                    data-live-search="true"
                    title="Select customer..."
                    style="width: 100%">
                @foreach($lims_customer_list as $customer)
                    <option value="{{ $customer->id }}" {{ $selectedCustomerId == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} ({{ $customer->phone_number }})
                    </option>
                @endforeach
            </select>

            @if($customer_active)
                <a href="#" data-toggle="modal" data-target="#addCustomer" class="btn btn-default btn-sm">
                    <i class="dripicons-plus"></i>
                </a>
            @endif
        </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="currency_id" id="currency" class="form-control selectpicker" data-toggle="tooltip" title="" data-original-title="Sale currency">
                                            @foreach($currency_list as $currency_data)
                                            <option value="{{$currency_data->id}}" data-rate="{{$currency_data->exchange_rate}}">{{$currency_data->code}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group d-flex">
                                            <input class="form-control" type="text" id="exchange_rate" name="exchange_rate" value="{{$currency->exchange_rate}}">
                                            <div class="input-group-append">
                                                <span class="input-group-text" data-toggle="tooltip" title="" data-original-title="currency exchange rate">i</span>
                                            </div>
                                        </div>
                                    </div>
                                    @foreach($custom_fields as $field)
                                        @if(!$field->is_admin || $isAdmin)
                                            <div class="{{'col-md-'.$field->grid_value}}">
                                                <div class="form-group">
                                                    <label>{{$field->name}}</label>
                                                    @if($field->type == 'text')
                                                        <input type="text" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control" @if($field->is_required){{'required'}}@endif>
                                                    @elseif($field->type == 'number')
                                                        <input type="number" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control" @if($field->is_required){{'required'}}@endif>
                                                    @elseif($field->type == 'textarea')
                                                        <textarea rows="5" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control" @if($field->is_required){{'required'}}@endif></textarea>
                                                    @elseif($field->type == 'checkbox')
                                                        <br>
                                                        <?php $option_values = explode(",", $field->option_value); ?>
                                                        @foreach($option_values as $value)
                                                            <label>
                                                                <input type="checkbox" name="{{str_replace(' ', '_', strtolower($field->name))}}[]" value="{{$value}}" @if($value == $field->default_value){{'checked'}}@endif @if($field->is_required){{'required'}}@endif> {{$value}}
                                                            </label>
                                                            &nbsp;
                                                        @endforeach
                                                    @elseif($field->type == 'radio_button')
                                                        <br>
                                                        <?php $option_values = explode(",", $field->option_value); ?>
                                                        @foreach($option_values as $value)
                                                            <label class="radio-inline">
                                                                <input type="radio" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$value}}" @if($value == $field->default_value){{'checked'}}@endif @if($field->is_required){{'required'}}@endif> {{$value}}
                                                            </label>
                                                            &nbsp;
                                                        @endforeach
                                                    @elseif($field->type == 'select')
                                                        <?php $option_values = explode(",", $field->option_value); ?>
                                                        <select class="form-control" name="{{str_replace(' ', '_', strtolower($field->name))}}" @if($field->is_required){{'required'}}@endif>
                                                            @foreach($option_values as $value)
                                                                <option value="{{$value}}" @if($value == $field->default_value){{'selected'}}@endif>{{$value}}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($field->type == 'multi_select')
                                                        <?php $option_values = explode(",", $field->option_value); ?>
                                                        <select class="form-control" name="{{str_replace(' ', '_', strtolower($field->name))}}[]" @if($field->is_required){{'required'}}@endif multiple>
                                                            @foreach($option_values as $value)
                                                                <option value="{{$value}}" @if($value == $field->default_value){{'selected'}}@endif>{{$value}}</option>
                                                            @endforeach
                                                        </select>
                                                    @elseif($field->type == 'date_picker')
                                                        <input type="text" name="{{str_replace(' ', '_', strtolower($field->name))}}" value="{{$field->default_value}}" class="form-control date" @if($field->is_required){{'required'}}@endif>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    <div class="col-md-12">
                                        <div class="search-box form-group">
                                            <input type="text" name="product_code_name" id="lims_productcodeSearch" placeholder="Scan/Search product by name/code" class="form-control"  />
                                        </div>
                                    </div>
                                </div>
                               
                                <div class="form-group">
                                    <div class="table-responsive">
                                        <table id="myTable" class="table table-hover table-striped order-list table-fixed">
                                            <thead>
                                                <tr>
                                                    <th class="col-sm-2">{{trans('file.product')}}</th>
                                                    <th class="col-sm-2">{{trans('file.Batch No')}}</th>
                                                    <th class="col-sm-2">{{trans('file.Price')}}</th>
                                                    <th class="col-sm-3">{{trans('file.Quantity')}}</th>
                                                    <th class="col-sm-3">{{trans('file.Subtotal')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                $temp_unit_name = [];
                                                $temp_unit_operator = [];
                                                $temp_unit_operation_value = [];
                                            ?>
                                                @foreach($lims_product_sale_data as $product_sale)
                                                    <?php
                                                        $product_data = \App\Models\Product::find($product_sale->product_id);
                                                        $product_batch_data = \App\Models\ProductBatch::select('batch_no', 'expired_date')->find($product_sale->product_batch_id);
                                                    ?>
                                                    @if(!$product_data)
                                                        @continue
                                                    @endif
                                                <tr>
                                                    <?php
                                                        if($product_sale->variant_id) {
                                                            $product_variant_data = \App\Models\ProductVariant::select('id', 'item_code')->FindExactProduct($product_data->id, $product_sale->variant_id)->first();
                                                            $product_data->code = $product_variant_data->item_code;
                                                        }

                                                        if($product_data->tax_method == 1){
                                                            $product_price = $product_sale->net_unit_price + ($product_sale->discount / $product_sale->qty);
                                                        }
                                                        elseif ($product_data->tax_method == 2) {
                                                            $product_price =($product_sale->total / $product_sale->qty) + ($product_sale->discount / $product_sale->qty);
                                                        }

                                                        $tax = DB::table('taxes')->where('rate',$product_sale->tax_rate)->first();
                                                        $unit_name = array();
                                                        $unit_operator = array();
                                                        $unit_operation_value = array();
                                                        if($product_data->type == 'standard'){
                                                            $units = DB::table('units')->where('base_unit', $product_data->unit_id)->orWhere('id', $product_data->unit_id)->get();

                                                            foreach($units as $unit) {
                                                                if($product_sale->sale_unit_id == $unit->id) {
                                                                    array_unshift($unit_name, $unit->unit_name);
                                                                    array_unshift($unit_operator, $unit->operator);
                                                                    array_unshift($unit_operation_value, $unit->operation_value);
                                                                }
                                                                else {
                                                                    $unit_name[]  = $unit->unit_name;
                                                                    $unit_operator[] = $unit->operator;
                                                                    $unit_operation_value[] = $unit->operation_value;
                                                                }
                                                            }

                                                            if($unit_operator[0] == '*'){
                                                                $product_price = $product_price / $unit_operation_value[0];
                                                            }
                                                            elseif($unit_operator[0] == '/'){
                                                                $product_price = $product_price * $unit_operation_value[0];
                                                            }
                                                        }
                                                        else {
                                                            $unit_name[] = 'n/a'. ',';
                                                            $unit_operator[] = 'n/a'. ',';
                                                            $unit_operation_value[] = 'n/a'. ',';
                                                        }
                                                        $temp_unit_name = $unit_name = implode(",",$unit_name) . ',';

                                                        $temp_unit_operator = $unit_operator = implode(",",$unit_operator) .',';

                                                        $temp_unit_operation_value = $unit_operation_value =  implode(",",$unit_operation_value) . ',';
                                                    ?>
                                                    <td class="col-sm-2 product-title"><strong>{{$product_data->name}}</strong> [{{$product_data->code}}] <button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal" data-wholesale-price="{{$product_data->wholesale_price}}" data-item-discount="{{$product_sale->discount}}" > <i class="dripicons-document-edit"></i></button> </td>
                                                    @if($product_batch_data)
                                                    <td class="col-sm-2">
                                                      <input type="text" class="form-control batch-no" value="{{$product_batch_data->batch_no}}" required />
                                                      <input type="hidden" class="product-batch-id" name="product_batch_id[]" value="{{$product_sale->product_batch_id}}"/>
                                                    </td>
                                                    @else
                                                    <td class="col-sm-2">
                                                      <input type="text" class="form-control batch-no" disabled/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/>
                                                    </td>
                                                    @endif
                                                    <td class="col-sm-2 product-price">{{ number_format((float)($product_sale->total / $product_sale->qty), $general_setting->decimal, '.', '')}}</td>
                                                    <td class="col-sm-3"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default minus"><span class="dripicons-minus"></span></button></span><input type="text" name="qty[]" class="form-control qty numkey input-number" value="{{$product_sale->qty}}" step="any" required><span class="input-group-btn"><button type="button" class="btn btn-default plus"><span class="dripicons-plus"></span></button></span></div></td>
                                                    <td class="col-sm-2 sub-total">{{ number_format((float)$product_sale->total, $general_setting->decimal, '.', '')}}</td>
                                                    <td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="dripicons-cross"></i></button></td>
                                                    <input type="hidden" class="product-code" name="product_code[]" value="{{$product_data->code}}"/>
                                                    <input type="hidden" class="product-id" name="product_id[]" value="{{$product_data->id}}"/>
                                                    <input type="hidden" class="product_price" name="product_price[]" value="{{$product_price}}"/>
                                                    <input type="hidden" class="net_unit_price" name="net_unit_price[]" value="{{$product_sale->net_unit_price}}" />
                                                    <input type="hidden" class="discount-value" name="discount[]" value="{{$product_sale->discount}}" />
                                                    <input type="hidden" class="tax-rate" name="tax_rate[]" value="{{$product_sale->tax_rate}}"/>
                                                    <input type="hidden" class="wholesale_price" name="wholesale_price[]" value="{{$product_data->wholesale_price}}" />
                                                    <!-- item discount from product_sale->discount -->
                                                     <input type="hidden" class="item_discount" name="item_discount[]" value="{{$product_sale->discount}}" />
                                                    @if($tax)
                                                    <input type="hidden" class="tax-name" value="{{$tax->name}}" />
                                                    @else
                                                    <input type="hidden" class="tax-name" value="No Tax" />
                                                    @endif
                                                    <input type="hidden" class="tax-method" value="{{$product_data->tax_method}}"/>
                                                    <input type="hidden" class="tax-value" name="tax[]" value="{{$product_sale->tax}}" />
                                                    <input type="hidden" class="total-discount" value="{{$product_sale->discount}}">
                                                    <input type="hidden" class="subtotal-value" name="subtotal[]" value="{{$product_sale->total}}" />
                                                    <input type="hidden" class="sale-unit" name="sale_unit[]" value="{{$unit_name}}"/>
                                                    <input type="hidden" class="sale-unit-operator" value="{{$unit_operator}}"/>
                                                    <input type="hidden" class="sale-unit-operation-value" value="{{$unit_operation_value}}"/>
                                                    <input type="hidden" class="imei-number" name="imei_number[]"  value="{{$product_sale->imei_number}}" />
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="tfoot active">
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="row"style="display: none;">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_qty" value="{{$lims_sale_data->total_qty}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_discount" value="{{$lims_sale_data->total_discount}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_tax" value="{{$lims_sale_data->total_tax}}"/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_price" value="{{$lims_sale_data->total_price}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="item" value="{{$lims_sale_data->item}}" />
                                            <input type="hidden" name="order_tax" value="{{$lims_sale_data->order_tax}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="grand_total" value="{{$lims_sale_data->grand_total}}" />
                                            <input type="hidden" name="used_points" />
                                            <input type="hidden" name="sale_status" value="1" />
                                            @if($lims_sale_data->coupon_id)
                                                @php
                                                    $coupon = \App\Models\Coupon::find($lims_sale_data->coupon_id)
                                                @endphp
                                                <input type="hidden" name="coupon_active" value="1">
                                            @else
                                                <input type="hidden" name="coupon_active">
                                            @endif
                                            <input type="hidden" name="coupon_id" value="{{$lims_sale_data->coupon_id}}">
                                            <input type="hidden" name="coupon_discount" value="{{$lims_sale_data->coupon_discount}}"/>
                                            <input type="hidden" name="pos" value="1" />
                                            <input type="hidden" name="sale_id" value="{{$lims_sale_data->id}}" />
                                            <input type="hidden" name="draft" value="1" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 totals" style="border-top: 2px solid #e4e6fc; padding-top: 10px;">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Items')}}</span><span id="item">{{$lims_sale_data->item}} ({{$lims_sale_data->total_qty}})</span>
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Total')}}</span><span id="subtotal">{{number_format((float)$lims_sale_data->total_price, $general_setting->decimal, '.', '')}}</span>
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Discount')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#order-discount"> <i class="dripicons-document-edit"></i></button></span>
                                            @if($lims_sale_data->order_discount)
                                            <span id="discount">{{number_format((float)$lims_sale_data->order_discount, $general_setting->decimal, '.', '')}}</span>
                                            @else
                                            <span id="discount">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Coupon')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#coupon-modal"><i class="dripicons-document-edit"></i></button></span>
                                            @if($lims_sale_data->coupon_discount)
                                              <span id="coupon-text">{{number_format((float)$lims_sale_data->coupon_discount, $general_setting->decimal, '.', '')}}</span>
                                            @else
                                              <span id="coupon-text">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Tax')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#order-tax"><i class="dripicons-document-edit"></i></button></span>
                                            @if($lims_sale_data->order_tax)
                                              <span id="tax">{{number_format((float)$lims_sale_data->order_tax, $general_setting->decimal, '.', '')}}</span>
                                            @else
                                              <span id="tax">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
                                            @endif
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Shipping')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#shipping-cost-modal"><i class="dripicons-document-edit"></i></button></span>
                                            @if($lims_sale_data->shipping_cost)
                                              <span id="shipping-cost">{{number_format((float)$lims_sale_data->shipping_cost, $general_setting->decimal, '.', '')}}</span>
                                            @else
                                              <span id="shipping-cost">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                   
                        <div class="payment-amount">
                            <h2>{{trans('file.grand total')}} <span id="grand-total">{{number_format((float)$lims_sale_data->grand_total, $general_setting->decimal, '.', '')}}</span></h2>
                        </div>
                    
                    <div class="payment-options"></div>
                
                    <div id="toggle-filters"
                            style="
                                position: fixed;
                                top: 50%;
                                right: 15px;
                                transform: translateY(-50%);
                                cursor: pointer;
                                background: #fff;
                                border-radius: 4px;
                                padding: 12px 15px;
                                box-shadow: 0 2px 6px rgba(0,0,0,0.2);
                                z-index: 1050;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                border: 1px solid #ddd;
                            ">
                            <i class="fa fa-shopping-cart" style="font-size: 24px; color: #333;"></i>
                    </div>
             
                    <!-- order_discount modal -->
                    <div id="order-discount" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                        <div role="document" class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{trans('file.Order Discount')}}</h5>
                                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <input type="text" name="order_discount" class="form-control numkey" step="any" value="{{number_format((float)$lims_sale_data->order_discount, $general_setting->decimal, '.', '')}}">
                                    </div>
                                    <button type="button" name="order_discount_btn" class="btn btn-primary" data-dismiss="modal">{{trans('file.submit')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- coupon modal -->
                    <div id="coupon-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                        <div role="document" class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{trans('file.Coupon Code')}}</h5>
                                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        @if($lims_sale_data->coupon_id)
                                            <input type="text" id="coupon-code" class="form-control" placeholder="Type Coupon Code..." value="{{$coupon->code}}" disabled>
                                        @else
                                            <input type="text" id="coupon-code" class="form-control" placeholder="Type Coupon Code...">
                                        @endif
                                    </div>
                                    <button type="button" class="btn btn-primary coupon-check" data-dismiss="modal">{{trans('file.submit')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- order_tax modal -->
                    <div id="order-tax" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                        <div role="document" class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{trans('file.Order Tax')}}</h5>
                                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <input type="hidden" name="order_tax_rate_hidden" value="{{$lims_sale_data->order_tax_rate}}">
                                        <select class="form-control" name="order_tax_rate">
                                            <option value="0">No Tax</option>
                                            @foreach($lims_tax_list as $tax)
                                            <option value="{{$tax->rate}}">{{$tax->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="button" name="order_tax_btn" class="btn btn-primary" data-dismiss="modal">{{trans('file.submit')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- shipping_cost modal -->
                    <div id="shipping-cost-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                        <div role="document" class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{trans('file.Shipping Cost')}}</h5>
                                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <input type="text" name="shipping_cost" class="form-control numkey" value="{{number_format((float)($lims_sale_data->shipping_cost), $general_setting->decimal, '.', '')}}" step="any">
                                    </div>
                                    <button type="button" name="shipping_cost_btn" class="btn btn-primary" data-dismiss="modal">{{trans('file.submit')}}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- payment modal -->
                    <div id="add-payment" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                        <div role="document" class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Finalize Sale')}}</h5>
                                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                                </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="row">
                                                    <div class="col-md-3 mt-1">
                                                        <label>{{trans('file.Recieved Amount')}} *</label>
                                                        <input type="text" name="paying_amount" class="form-control numkey" required step="any">
                                                    </div>
                                                    <div class="col-md-3 mt-1">
                                                        <label>{{trans('file.Paying Amount')}} *</label>
                                                        <input type="text" name="paid_amount" class="form-control numkey"  step="any">
                                                    </div>
                                                    <div class="col-md-3 mt-1">
                                                        <label>{{trans('file.Change')}} : </label>
                                                        <p id="change" class="ml-2">{{number_format(0, $general_setting->decimal, '.', '')}}</p>
                                                    </div>
                                                    <div class="col-md-3 mt-1">
                                                        <input type="hidden" name="paid_by_id">
                                                        <label>{{ trans('file.Paid By') }}</label>
                                                        <div class="d-flex align-items-center">
                                                            <select name="paid_by_id_select" class="form-control selectpicker">
                                                                @if(in_array("cash",$options))
                                                                <option value="1">Cash</option>
                                                                @endif
                                                                @if(in_array("gift_card",$options))
                                                                <option value="2">Gift Card</option>
                                                                @endif
                                                                @if(in_array("card",$options))
                                                                <option value="3">Credit Card</option>
                                                                @endif
                                                                @if(in_array("cheque",$options))
                                                                <option value="4">Cheque</option>
                                                                @endif
                                                                @if(in_array("paypal",$options) && (strlen(env('PAYPAL_LIVE_API_USERNAME'))>0) && (strlen(env('PAYPAL_LIVE_API_PASSWORD'))>0) && (strlen(env('PAYPAL_LIVE_API_SECRET'))>0))
                                                                <option value="5">Paypal</option>
                                                                @endif
                                                                @if(in_array("deposit",$options))
                                                                <option value="6">Deposit</option>
                                                                @endif
                                                                @if($lims_reward_point_setting_data && $lims_reward_point_setting_data->is_active)
                                                                <option value="7">Points</option>
                                                                @endif
                                                                @if(in_array("mobile_money",$options))
                                                                <option value="8">Mobile Money</option>
                                                                @endif
                                                            </select>
                                                            <i class="fa fa-plus text-success ml-2 cursor-pointer" id="multiplePaymentBtn" title="Add Split"></i>
                                                        </div>
                                                    </div>


                                                    {{-- Container for dynamically added splits --}}
                                                    <div class="col-md-12 mt-2 ml-2" id="paymentMethodsContainer"></div>
                                                    <div class="form-group col-md-12 mt-3 credit-card-fields">
                                                        <label>{{trans('file.Credit Card Payment')}} *</label>
                                                        <div class="card-element form-control">
                                                        </div>
                                                        <div class="card-errors" role="alert"></div>
                                                    </div>
                                                    
                                                    
                                                    <div class="form-group col-md-12 mobile_money_fields">
                                                        <label>{{trans('Mobile Money')}} *</label>
                                                        <select id="mobile_money_operator" name="mobile-op" class="form-control" data-live-search="true" data-live-search-style="begins" title="Mobile money operator">
                                                            <option value="mtn_mobile_money">MTN Mobile Money</option>
                                                            <option value="airteltigo_cash">AirtelTigo Cash</option>
                                                            <option value="telecel_cash">Telecel Cash</option>
                                                        </select>
                                                    </div>
                                                    <input type="hidden" id="selected_mobile_op" name="selected_mobile_op">

                                                    <div class="form-group col-md-12 mobile_money_fields">
                                                        <label>{{trans('Mobile Number')}} *</label>
                                                        <input type="number" name="mobile_number" class="form-control">
                                                    </div>
                                                    
                                                    
                                                    <div class="form-group col-md-12 gift-card">
                                                        <label> {{trans('file.Gift Card')}} *</label>
                                                        <input type="hidden" name="gift_card_id">
                                                        <select id="gift_card_id_select" name="gift_card_id_select" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select Gift Card..."></select>
                                                    </div>
                                                    <div class="form-group col-md-12 cheque">
                                                        <label>{{trans('file.Cheque Number')}} *</label>
                                                        <input type="text" name="cheque_no" class="form-control">
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label>{{trans('file.Payment Note')}}</label>
                                                        <textarea id="payment_note" rows="2" class="form-control" name="payment_note"></textarea>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                <div class="col-md-6 form-group">
                                                        <label>{{trans('file.Sale Note')}}</label>
                                                        <textarea rows="3" class="form-control" name="sale_note"></textarea>
                                                    </div>
                                                    <div class="col-md-6 form-group">
                                                        <label>{{trans('file.Staff Note')}}</label>
                                                        <textarea rows="3" class="form-control" name="staff_note"></textarea>
                                                    </div>
                                                </div>
                                                <div id="payment-error-alert" class="alert alert-danger d-none mt-2" role="alert">
                                                    <i class="dripicons-warning"></i>
                                                    <strong>Error:</strong> <span id="payment-error-message"></span>
                                                </div>
                                                <div class="mt-3">
                                                    <button id="submit-btn" type="button" class="btn btn-primary">{{trans('file.submit')}}</button>
                                                </div>
                                            </div>
                                            <div class="col-md-2 qc" data-initial="1">
                                                <h4><strong>{{trans('file.Quick Cash')}}</strong></h4>
                                                <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="10" type="button">10</button>
                                                <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="20" type="button">20</button>
                                                <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="50" type="button">50</button>
                                                <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="100" type="button">100</button>
                                                <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="500" type="button">500</button>
                                                <button class="btn btn-block btn-primary qc-btn sound-btn" data-amount="1000" type="button">1000</button>
                                                <button class="btn btn-block btn-danger qc-btn sound-btn" data-amount="0" type="button">{{trans('file.Clear')}}</button>
                                            </div>
                                        </div>
                                    </div>
                              
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <!-- product list -->
            <div class="col-md-5 order-1 order-md-3 d-none transition-all" id="filter-column">

                
                
           
            </div>
            <!-- product edit modal -->
            <!-- product edit modal --> 
            <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                <div role="document" class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="modal_header" class="modal-title"></h5>
                            <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                        </div>
                        <div class="modal-body">
                            <form>
                                <div class="row modal-element">
                                    <div class="col-md-4 form-group">
                                        <label>{{trans('file.Quantity')}}</label>
                                        <input type="text" name="edit_qty" class="form-control numkey">
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>{{trans('file.Unit Discount')}}</label>
                                        <input type="text" name="edit_discount" class="form-control numkey">
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Price Option</strong> </label>
                                            <div class="input-group">
                                                <select class="form-control selectpicker" name="price_option" class="price-option">
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group">
                                        <label>{{trans('file.Unit Price')}}</label>
                                        <input type="text" name="edit_unit_price" class="form-control numkey" step="any">
                                    </div>
                                        <?php
                                        $tax_name_all[] = 'No Tax';
                                        $tax_rate_all[] = 0;
                                        foreach ($lims_tax_list as $tax) {
                                            $tax_name_all[] = $tax->name;
                                            $tax_rate_all[] = $tax->rate;
                                        }
                                        ?>
                     
                                    <div id="edit_unit" class="col-md-4 form-group">
                                        <label>{{trans('file.Product Unit')}}</label>
                                        <select name="edit_unit" class="form-control selectpicker">
                                        </select>
                                    </div>
                                </div>
                                <button type="button" name="update_btn" class="btn btn-primary">{{trans('file.update')}}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
                                   
                        
            <!-- add customer modal -->
            <div id="addCustomer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                <div role="document" class="modal-dialog">
                  <div class="modal-content">
                    {!! Form::open(['route' => 'customer.store', 'method' => 'post', 'files' => true]) !!}
                    <div class="modal-header">
                      <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Customer')}}</h5>
                      <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                      <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        <div class="form-group">
                            <label>{{trans('file.Customer Group')}} *</strong> </label>
                            <select required class="form-control selectpicker" name="customer_group_id">
                                @foreach($lims_customer_group_all as $customer_group)
                                    <option value="{{$customer_group->id}}">{{$customer_group->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{trans('file.name')}} *</strong> </label>
                            <input type="text" name="name" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{trans('file.Email')}}</label>
                            <input type="text" name="email" placeholder="example@example.com" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{trans('file.Phone Number')}} *</label>
                            <input type="text" name="phone_number" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{trans('file.Address')}} *</label>
                            <input type="text" name="address" required class="form-control">
                        </div>
                        <div class="form-group">
                            <label>{{trans('file.City')}} *</label>
                            <input type="text" name="city" required class="form-control">
                        </div>
                        <div class="form-group">
                        <input type="hidden" name="pos" value="1">
                          <input type="submit" value="{{trans('file.submit')}}" class="btn btn-primary">
                        </div>
                    </div>
                    {{ Form::close() }}
                  </div>
                </div>
            </div>

            
            <!-- recent transaction modal -->
            <div id="recentTransaction" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                <div role="document" class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Recent Transaction')}} <div class="badge badge-primary">{{trans('file.latest')}} 10</div></h5>
                      <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <ul class="nav nav-tabs" role="tablist">
                          <li class="nav-item">
                            <a class="nav-link active" href="#sale-latest" role="tab" data-toggle="tab">{{trans('file.Sale')}}</a>
                          </li>
                          <li class="nav-item">
                            <a class="nav-link" href="#draft-latest" role="tab" data-toggle="tab">Hold</a>
                          </li>
                        </ul>
                        <div class="tab-content">
                          <div role="tabpanel" class="tab-pane show active" id="sale-latest">
                              <div class="table-responsive">
                                <table class="table">
                                  <thead>
                                    <tr>
                                      <th>{{trans('file.date')}}</th>
                                      <th>{{trans('file.reference')}}</th>
                                      <th>{{trans('file.customer')}}</th>
                                      <th>{{trans('file.grand total')}}</th>
                                      <th>{{trans('file.action')}}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($recent_sale as $sale)
                                    <?php $customer = DB::table('customers')->find($sale->customer_id); ?>
                                    <tr>
                                      <td>{{date('d-m-Y', strtotime($sale->created_at))}}</td>
                                      <td>{{$sale->reference_no}}</td>
                                      <td>{{$customer->name}}</td>
                                      <td>{{$sale->grand_total}}</td>
                                      <td>
                                        <div class="btn-group">
                                            @if(in_array("sales-edit", $all_permission))
                                            <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-success btn-sm" title="Edit"><i class="dripicons-document-edit"></i></a>&nbsp;
                                            @endif
                                            @if(in_array("sales-delete", $all_permission))
                                            {{ Form::open(['route' => ['sales.destroy', $sale->id], 'method' => 'DELETE'] ) }}
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirmDelete()" title="Delete"><i class="dripicons-trash"></i></button>
                                            {{ Form::close() }}
                                            @endif
                                        </div>
                                      </td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                              </div>
                          </div>
                          <div role="tabpanel" class="tab-pane fade" id="draft-latest">
                              <div class="table-responsive">
                                <table class="table">
                                  <thead>
                                    <tr>
                                      <th>{{trans('file.date')}}</th>
                                      <th>{{trans('file.reference')}}</th>
                                      <th>{{trans('file.customer')}}</th>
                                      <th>{{trans('file.grand total')}}</th>
                                      <th>{{trans('file.action')}}</th>
                                    </tr>
                                  </thead>
                                  <tbody>
                                    @foreach($recent_draft as $draft)
                                    <?php $customer = DB::table('customers')->find($draft->customer_id); ?>
                                    <tr>
                                      <td>{{date('d-m-Y', strtotime($draft->created_at))}}</td>
                                      <td>{{$draft->reference_no}}</td>
                                      <td>{{$customer->name}}</td>
                                      <td>{{$draft->grand_total}}</td>
                                      <td>
                                        <div class="btn-group">
                                            @if(in_array("sales-edit", $all_permission))
                                            <a href="{{url('sales/'.$draft->id.'/create') }}" class="btn btn-success btn-sm" title="Edit"><i class="dripicons-document-edit"></i></a>&nbsp;
                                            @endif
                                            @if(in_array("sales-delete", $all_permission))
                                            {{ Form::open(['route' => ['sales.destroy', $draft->id], 'method' => 'DELETE'] ) }}
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirmDelete()" title="Delete"><i class="dripicons-trash"></i></button>
                                            {{ Form::close() }}
                                            @endif
                                        </div>
                                      </td>
                                    </tr>
                                    @endforeach
                                  </tbody>
                                </table>
                              </div>
                          </div>
                        </div>
                    </div>
                  </div>
                </div>
            </div>
            <!-- add cash register modal -->
            <div id="cash-register-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                <div role="document" class="modal-dialog">
                  <div class="modal-content">
                    {!! Form::open(['route' => 'cashRegister.store', 'method' => 'post']) !!}
                    <div class="modal-header">
                      <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Add Cash Register')}}</h5>
                      <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                      <p class="italic"><small>{{trans('file.The field labels marked with * are required input fields')}}.</small></p>
                        <div class="row">
                          <div class="col-md-6 form-group warehouse-section">
                              <label>{{trans('file.Warehouse')}} *</strong> </label>
                              <select required name="warehouse_id" class="selectpicker form-control" data-live-search="true" data-live-search-style="begins" title="Select warehouse...">
                                  @foreach($lims_warehouse_list as $warehouse)
                                  <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                  @endforeach
                              </select>
                          </div>
                          <div class="col-md-6 form-group">
                              <label>{{trans('file.Cash in Hand')}} *</strong> </label>
                              <input type="number" step="any" name="cash_in_hand" required class="form-control">
                          </div>
                          <div class="col-md-12 form-group">
                              <button type="submit" class="btn btn-primary">{{trans('file.submit')}}</button>
                          </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                  </div>
                </div>
            </div>
            <!-- cash register details modal -->
            <div id="register-details-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                <div role="document" class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Cash Register Details')}}</h5>
                      <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                      <p>{{trans('file.Please review the transaction and payments.')}}</p>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                          <td>{{trans('file.Cash in Hand')}}:</td>
                                          <td id="cash_in_hand" class="text-right">0</td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Total Sale Amount')}}:</td>
                                          <td id="total_sale_amount" class="text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Total Payment')}}:</td>
                                          <td id="total_payment" class="text-right"></td>
                                        </tr>
                                        @if(in_array("cash",$options))
                                        <tr>
                                          <td>{{trans('file.Cash Payment')}}:</td>
                                          <td id="cash_payment" class="text-right"></td>
                                        </tr>
                                        @endif
                                        @if(in_array("card",$options))
                                        <tr>
                                          <td>{{trans('file.Credit Card Payment')}}:</td>
                                          <td id="credit_card_payment" class="text-right"></td>
                                        </tr>
                                        @endif
                                        @if(in_array("cheque",$options))
                                        <tr>
                                          <td>{{trans('file.Cheque Payment')}}:</td>
                                          <td id="cheque_payment" class="text-right"></td>
                                        </tr>
                                        @endif
                                        @if(in_array("gift_card",$options))
                                        <tr>
                                          <td>{{trans('file.Gift Card Payment')}}:</td>
                                          <td id="gift_card_payment" class="text-right"></td>
                                        </tr>
                                        @endif
                                        @if(in_array("deposit",$options))
                                        <tr>
                                          <td>{{trans('file.Deposit Payment')}}:</td>
                                          <td id="deposit_payment" class="text-right"></td>
                                        </tr>
                                        @endif
                                        @if(in_array("paypal",$options) && (strlen(env('PAYPAL_LIVE_API_USERNAME'))>0) && (strlen(env('PAYPAL_LIVE_API_PASSWORD'))>0) && (strlen(env('PAYPAL_LIVE_API_SECRET'))>0))
                                        <tr>
                                          <td>{{trans('file.Paypal Payment')}}:</td>
                                          <td id="paypal_payment" class="text-right"></td>
                                        </tr>
                                        @endif
                                        <tr>
                                          <td>{{trans('file.Total Sale Return')}}:</td>
                                          <td id="total_sale_return" class="text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Total Expense')}}:</td>
                                          <td id="total_expense" class="text-right"></td>
                                        </tr>
                                        <tr>
                                          <td><strong>{{trans('file.Total Cash')}}:</strong></td>
                                          <td id="total_cash" class="text-right"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6" id="closing-section">
                              <form action="{{route('cashRegister.close')}}" method="POST">
                                  @csrf
                                  <input type="hidden" name="cash_register_id">
                                  <button type="submit" class="btn btn-primary">{{trans('file.Close Register')}}</button>
                              </form>
                            </div>
                        </div>
                    </div>
                  </div>
                </div>
            </div>
            <!-- today sale modal -->
            <div id="today-sale-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                <div role="document" class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Today Sale')}}</h5>
                      <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                      <p>{{trans('file.Please review the transaction and payments.')}}</p>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                          <td>{{trans('file.Total Sale Amount')}}:</td>
                                          <td class="total_sale_amount text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Cash Payment')}}:</td>
                                          <td class="cash_payment text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Credit Card Payment')}}:</td>
                                          <td class="credit_card_payment text-right"></td>
                                        </tr>
                                        
                                        <tr>
                                            <td>{{trans('Mobile Money payment')}}:</td>
                                            <td class="mobile_payment text-right"></td>
                                          </tr>
                                        <tr>
                                        
                                          <td>{{trans('file.Cheque Payment')}}:</td>
                                          <td class="cheque_payment text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Gift Card Payment')}}:</td>
                                          <td class="gift_card_payment text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Deposit Payment')}}:</td>
                                          <td class="deposit_payment text-right"></td>
                                        </tr>
                                        @if(in_array("paypal",$options) && (strlen(env('PAYPAL_LIVE_API_USERNAME'))>0) && (strlen(env('PAYPAL_LIVE_API_PASSWORD'))>0) && (strlen(env('PAYPAL_LIVE_API_SECRET'))>0))
                                        <tr>
                                          <td>{{trans('file.Paypal Payment')}}:</td>
                                          <td class="paypal_payment text-right"></td>
                                        </tr>
                                        @endif
                                        <tr>
                                          <td>{{trans('file.Total Payment')}}:</td>
                                          <td class="total_payment text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Total Sale Return')}}:</td>
                                          <td class="total_sale_return text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Total Expense')}}:</td>
                                          <td class="total_expense text-right"></td>
                                        </tr>
                                        <tr>
                                          <td><strong>{{trans('file.Total Cash')}}:</strong></td>
                                          <td class="total_cash text-right"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                  </div>
                </div>
            </div>
            <!-- today profit modal -->
            <div id="today-profit-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                <div role="document" class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 id="exampleModalLabel" class="modal-title">{{trans('file.Today Profit')}}</h5>
                      <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <select required name="warehouseId" class="form-control">
                                    <option value="0">{{trans('file.All Warehouse')}}</option>
                                    @foreach($lims_warehouse_list as $warehouse)
                                    <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 mt-2">
                                <table class="table table-hover">
                                    <tbody>
                                        <tr>
                                          <td>{{trans('file.Product Revenue')}}:</td>
                                          <td class="product_revenue text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Product Cost')}}:</td>
                                          <td class="product_cost text-right"></td>
                                        </tr>
                                        <tr>
                                          <td>{{trans('file.Expense')}}:</td>
                                          <td class="expense_amount text-right"></td>
                                        </tr>
                                        <tr>
                                          <td><strong>{{trans('file.Profit')}}:</strong></td>
                                          <td class="profit text-right"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                  </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script type="text/javascript">

    $("ul#sale").siblings('a').attr('aria-expanded','true');
    $("ul#sale").addClass("show");
    $("ul#sale li").eq(1).addClass("active");

     @if(session()->get('message') == 'Sale successfully added to draft')
        localStorage.clear();
    @endif
   $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var public_key = <?php echo json_encode($lims_pos_setting_data->stripe_public_key) ?>;
    var valid;

// array data depend on warehouse
var lims_product_array = [];
var product_code = [];
var product_name = [];
var product_qty = [];
var product_type = [];
var product_id = [];
var product_list = [];
var qty_list = [];

// array data with selection
var product_price = [];
var product_shelf = [];
var product_discount = [];
var tax_rate = [];
var tax_name = [];
var tax_method = [];
var unit_name = [];
var unit_operator = [];
var unit_operation_value = [];
var is_imei = [];
var is_variant = [];
var gift_card_amount = [];
var gift_card_expense = [];

// temporary array
var temp_unit_name = [];
var temp_unit_operator = [];
var temp_unit_operation_value = [];

var deposit = <?php echo json_encode($deposit) ?>;
var points = <?php echo json_encode($points) ?>;
@if($lims_reward_point_setting_data)
var reward_point_setting = <?php echo json_encode($lims_reward_point_setting_data) ?>;
@endif
@if($lims_pos_setting_data)
var product_row_number = <?php echo json_encode($lims_pos_setting_data->product_number) ?>;
@endif
var rowindex;
var customer_group_rate = 0; // Initialize with default value
var row_product_price;
var pos;
var keyboard_active = <?php echo json_encode($keybord_active); ?>;
var role_id = <?php echo json_encode(\Auth::user()->role_id) ?>;
var coupon_list = <?php echo json_encode($lims_coupon_list) ?>;
var currency = <?php echo json_encode($currency) ?>;
var without_stock = <?php echo json_encode($general_setting->without_stock) ?>;
var currencyChange = false;
$('#currency').val(currency['id']);

$('#currency').change(function(){
    var rate = $(this).find(':selected').data('rate');
    var currency_id = $(this).val();
    $('#exchange_rate').val(rate);
    //$('input[name="currency_id"]').val(currency_id);
    currency['exchange_rate'] = rate;
    $("table.order-list tbody .qty").each(function(index) {
        rowindex = index;
        currencyChange = true;
        checkDiscount($(this).val(), true);
        couponDiscount();
    });
});
// $('#currency').change(function(){
//     var rate = $(this).find(':selected').data('rate');
//     var currency_id = $(this).val();
//     $('#exchange_rate').val(rate);
//     $('input[name="currency_id"]').val(currency_id);

//     currency['exchange_rate'] = rate;

//     var qty = 0;
//     $("table.order-list tbody .qty").each(function(index) {
//         rowindex = index;
//         if ($(this).val() == '') {
//             qty += 0;
//         } else {
//             qty += parseFloat($(this).val());
//         }

//         checkDiscount(qty, true);
//         couponDiscount();
//     });

// })
function saveValue(e) {
    var id = e.id;  // get the sender's id to save it.
    var val = e.value; // get the value.
    localStorage.setItem(id, val);// Every time user writing something, the localStorage's value will override.
}
//get the saved value function - return the value of "v" from localStorage.
function getSavedValue  (v) {
    if (!localStorage.getItem(v)) {
        return "";// You can change this to your defualt value.
    }
    return localStorage.getItem(v);
}

if(role_id > 2){
    $('#biller_id').addClass('d-none');
    $('#warehouse_id').addClass('d-none');
    $('select[name=warehouse_id]').val(warehouse_id);
    $('select[name=biller_id]').val(biller_id);
    isCashRegisterAvailable(warehouse_id);
}
else {
    if(getSavedValue("warehouse_id")){
      warehouse_id = getSavedValue("warehouse_id");
    }
    else {
      warehouse_id = $("input[name='warehouse_id_hidden']").val();
    }

    if(getSavedValue("biller_id")){
      biller_id = getSavedValue("biller_id");
    }
    else {
      biller_id = $("input[name='biller_id_hidden']").val();
    }
    $('select[name=warehouse_id]').val(warehouse_id);
    $('select[name=biller_id]').val(biller_id);
}

document.getElementById("toggle-filters").addEventListener("click", function () {
    let filterCol = document.getElementById("filter-column");
    let mainCol = document.getElementById("main-column");
    console.log(mainCol.classList);

    if (filterCol.classList.contains("d-none")) {
        // Show filter column (shrink main to 5 cols)
        filterCol.classList.remove("d-none");
        mainCol.classList.remove("col-md-10", "col-lg-10");
        mainCol.classList.add("col-md-5", "col-lg-5");
 
    } else {
        // Hide filter column (expand main to 10 cols)
        filterCol.classList.add("d-none");
        mainCol.classList.remove("col-md-5", "col-lg-5");
        mainCol.classList.add("col-md-10", "col-lg-10");
    }
});

var rownumber = $('table.order-list tbody tr:last').index();
for(rowindex  =0; rowindex <= rownumber; rowindex++){
    product_price.push(parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product_price').val()));
    var total_discount = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.total-discount').val());
    var quantity = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val());
    product_discount.push((total_discount / quantity).toFixed({{$general_setting->decimal}}));
    tax_rate.push(parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val()));
    tax_name.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-name').val());
    tax_method.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-method').val());
    temp_unit_name = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val().split(',');
    unit_name.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val());
    unit_operator.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit-operator').val());
    unit_operation_value.push($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit-operation-value').val());
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val(temp_unit_name[0]);
}

$('.selectpicker').selectpicker({
    style: 'btn-link',
});

if(keyboard_active==1){

    $("input.numkey:text").keyboard({
        usePreview: false,
        layout: 'custom',
        display: {
        'accept'  : '&#10004;',
        'cancel'  : '&#10006;'
        },
        customLayout : {
          'normal' : ['1 2 3', '4 5 6', '7 8 9','0 {dec} {bksp}','{clear} {cancel} {accept}']
        },
        restrictInput : true, // Prevent keys not in the displayed keyboard from being typed in
        preventPaste : true,  // prevent ctrl-v and right click
        autoAccept : true,
        css: {
            // input & preview
            // keyboard container
            container: 'center-block dropdown-menu', // jumbotron
            // default state
            buttonDefault: 'btn btn-default',
            // hovered button
            buttonHover: 'btn-primary',
            // Action keys (e.g. Accept, Cancel, Tab, etc);
            // this replaces "actionClass" option
            buttonAction: 'active'
        },
    });

    $('input[type="text"]').keyboard({
        usePreview: false,
        autoAccept: true,
        autoAcceptOnEsc: true,
        css: {
            // input & preview
            // keyboard container
            container: 'center-block dropdown-menu', // jumbotron
            // default state
            buttonDefault: 'btn btn-default',
            // hovered button
            buttonHover: 'btn-primary',
            // Action keys (e.g. Accept, Cancel, Tab, etc);
            // this replaces "actionClass" option
            buttonAction: 'active',
            // used when disabling the decimal button {dec}
            // when a decimal exists in the input area
            buttonDisabled: 'disabled'
        },
        change: function(e, keyboard) {
                keyboard.$el.val(keyboard.$preview.val())
                keyboard.$el.trigger('propertychange')
              }
    });

    $('textarea').keyboard({
        usePreview: false,
        autoAccept: true,
        autoAcceptOnEsc: true,
        css: {
            // input & preview
            // keyboard container
            container: 'center-block dropdown-menu', // jumbotron
            // default state
            buttonDefault: 'btn btn-default',
            // hovered button
            buttonHover: 'btn-primary',
            // Action keys (e.g. Accept, Cancel, Tab, etc);
            // this replaces "actionClass" option
            buttonAction: 'active',
            // used when disabling the decimal button {dec}
            // when a decimal exists in the input area
            buttonDisabled: 'disabled'
        },
        change: function(e, keyboard) {
                keyboard.$el.val(keyboard.$preview.val())
                keyboard.$el.trigger('propertychange')
              }
    });

    $('#lims_productcodeSearch').keyboard().autocomplete().addAutocomplete({
        // add autocomplete window positioning
        // options here (using position utility)
        position: {
          of: '#lims_productcodeSearch',
          my: 'top+18px',
          at: 'center',
          collision: 'flip'
        }
    });
}
var id = '{{ $lims_sale_data->customer_id ?? "" }}';
$('select[name=customer_id]').val(id);
$('select[name=warehouse_id]').val($("input[name='warehouse_id_hidden']").val());
$('select[name=biller_id]').val($("input[name='biller_id_hidden']").val());
$('select[name=order_tax_rate]').val($("input[name='order_tax_rate_hidden']").val());
$('.selectpicker').selectpicker('refresh');
//$('#customer_id').selectpicker('refresh');
// Get the default customer ID from PHP


// Only fetch if there's an ID
if (id) {
    $.get('../getcustomergroup/' + id, function(data) {
        var customer_group_rate = data ? data / 100 : 0;
        console.log('Customer Group Rate:', customer_group_rate);
        // You can now use customer_group_rate in your calculations
    });
}

// Optional: Update whenever the user changes the selection
$('select[name="customer_id"]').on('change', function() {
    var id = $(this).val();
    if (id) {
        $.get('../getcustomergroup/' + id, function(data) {
            var customer_group_rate = data ? data / 100 : 0;
            console.log('Customer Group Rate:', customer_group_rate);
        });
    }
});


var id = $('select[name="warehouse_id"]').val();
$.get('../getproduct/' + id, function(data) {
    lims_product_array = [];
    product_code = data[0];
    product_name = data[1];
    product_qty = data[2];
    product_type = data[3];
    product_id = data[4];
    product_list = data[5];
    qty_list = data[6];
    product_warehouse_price = data[7];
    batch_no = data[8];
    product_batch_id = data[9];
    is_embeded = data[11];
    product_shelf = data[12];
        $.each(product_code, function(index) {
            if(is_embeded[index])
              lims_product_array.push(
                    'Product Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index] +
                    ' | Embedded: ' + is_embeded[index]
                );
            else
            lims_product_array.push(
                'Product Name: ' + product_name[index] +
                ' | Price: ' + product_warehouse_price[index] +
                ' | Qty: ' + product_qty[index] +
                ' | Shelf: ' + product_shelf[index]
            );
        });
});
$("#submit-btn").on("click", function() {
    if(validatePaymentMethod()) {
        $('.payment-form').submit();
    }
});

// Validate payment method and required fields
function validatePaymentMethod() {
    var paid_amount = $('input[name="paid_amount"]').val();
    var paid_by_id = $('input[name="paid_by_id"]').val();
    var paid_by_id_select = $('select[name="paid_by_id_select"]').val();
    
    // Get the selected payment method
    var paymentMethod = paid_by_id_select || paid_by_id;
    
    // Validate paid amount
    if (!paid_amount || parseFloat(paid_amount) <= 0) {
        alert('Please enter a valid paid amount');
        return false;
    }
    
    // Check each payment method for required fields
    if (paymentMethod == 2) { // Gift Card
        var gift_card_id = $('select[name="gift_card_id_select"]').val() || $('input[name="gift_card_id"]').val();
        if (!gift_card_id) {
            alert('Please select a gift card');
            return false;
        }
    }
    
    // Credit Card (3): Stripe validates card details on form submit via checkout.js
    
    if (paymentMethod == 4) { // Cheque
        var cheque_no = $('input[name="cheque_no"]').val();
        if (!cheque_no) {
            alert('Please enter the cheque number');
            return false;
        }
    }
    
    if (paymentMethod == 6) { // Deposit
        var customer_id = $('#customer_id').val();
        if (!customer_id) {
            alert('Please select a customer for deposit payment');
            return false;
        }
        var customerDeposit = parseFloat(deposit[customer_id]) || 0;
        if (parseFloat(paid_amount) > customerDeposit) {
            alert('Amount exceeds customer deposit! Customer deposit: ' + customerDeposit);
            return false;
        }
    }
    
    if (paymentMethod == 7) { // Points
        var customer_id = $('#customer_id').val();
        if (!customer_id) {
            alert('Please select a customer for points payment');
            return false;
        }
        if (typeof reward_point_setting === 'undefined') {
            alert('Reward point setting is not available');
            return false;
        }
        var customerPoints = parseInt(points[customer_id], 10) || 0;
        var perPointAmount = parseFloat(reward_point_setting['per_point_amount']) || 1;
        var requiredPoints = Math.ceil(parseFloat(paid_amount) / perPointAmount);
        if (requiredPoints > customerPoints) {
            alert('Customer does not have sufficient points. Available points: ' + customerPoints);
            return false;
        }
    }
    
    if (paymentMethod == 8) { // Mobile Money
        var mobile_operator = $('#mobile_money_operator').val() || $('input[name="selected_mobile_op"]').val();
        var mobile_number = $('input[name="mobile_number"]').val();
        
        if (!mobile_operator) {
            alert('Please select a mobile money operator');
            return false;
        }
        
        if (!mobile_number) {
            alert('Please enter a mobile number');
            return false;
        }
    }
    
    // Check split payments if any
    var splitPayments = $('input[name="split_amount[]"]');
    if (splitPayments.length > 0) {
        var totalSplit = getTotalSplitAmount();
        var mainPaid = parseFloat(paid_amount) || 0;
        var totalPaid = totalSplit + mainPaid;
        var payingAmount = parseFloat($('input[name="paying_amount"]').val()) || 0;
        
        if (totalPaid > payingAmount) {
            alert('Total paid (main + split) cannot be greater than paying amount');
            return false;
        }
    }
    
    return true;
}
isCashRegisterAvailable(id);

function  isCashRegisterAvailable(warehouse_id) {
    $.ajax({
        url: 'cash-register/check-availability/'+warehouse_id,
        type: "GET",
        success:function(data) {
            if(data == 'false') {
              $("#register-details-btn").addClass('d-none');
              $('#cash-register-modal select[name=warehouse_id]').val(warehouse_id);

              if(role_id <= 2)
                $("#cash-register-modal .warehouse-section").removeClass('d-none');
              else
                $("#cash-register-modal .warehouse-section").addClass('d-none');

              $('.selectpicker').selectpicker('refresh');
              $("#cash-register-modal").modal('show');
            }
            else
              $("#register-details-btn").removeClass('d-none');
        }
    });
}
if(keyboard_active==1){
    $('#lims_productcodeSearch').bind('keyboardChange', function (e, keyboard, el) {
        var customer_id = $('#customer_id').val();
        var warehouse_id = $('select[name="warehouse_id"]').val();
        temp_data = $('#lims_productcodeSearch').val();
        if(!customer_id){
            $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
            alert('Please select Customer!');
        }
        else if(!warehouse_id){
            $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
            alert('Please select Warehouse!');
        }
    });
}
else{
    $('#lims_productcodeSearch').on('input', function(){
        var customer_id = $('#customer_id').val();
        var warehouse_id = $('#warehouse_id').val();
        temp_data = $('#lims_productcodeSearch').val();
        if(!customer_id){
            $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
            alert('Please select Customer!');
        }
        else if(!warehouse_id){
            $('#lims_productcodeSearch').val(temp_data.substring(0, temp_data.length - 1));
            alert('Please select Warehouse!');
        }

    });
}

$(document).ready(function () {
    
    // Remove payment row
    $(document).on("click", ".remove-payment", function () {
        $(this).closest(".payment-row").remove();
        // refreshPaymentMethodOptions();
        calculateTotal();
         // Get updated values
        let payingAmount = parseFloat($('input[name="paying_amount"]').val()) || 0;
        let mainPaid = parseFloat($('input[name="paid_amount"]').val()) || 0;
        let totalSplit = getTotalSplitAmount();
        let totalPaid = mainPaid + totalSplit;

        // Update change
        change(payingAmount, totalPaid);
        
        // Update payment fields visibility after removal
        updateSplitPaymentFields();
    });

    function refreshPaymentMethodOptions() {
        let usedMethods = getSelectedPaymentMethods();

        let options = [
            { value: 1, label: 'Cash' },
            { value: 2, label: 'Gift Card' },
            { value: 3, label: 'Credit Card' },
            { value: 4, label: 'Cheque' },
            { value: 5, label: 'Paypal' },
            { value: 6, label: 'Deposit' },
            { value: 7, label: 'Points' },
            { value: 8, label: 'Mobile Money' }
        ];

        $(".payment-method-select").each(function () {
            let currentVal = $(this).val();
            let selectOptions = options.filter(opt => {
                return !usedMethods.includes(opt.value) || opt.value == currentVal;
            }).map(opt => {
                let selected = opt.value == currentVal ? 'selected' : '';
                return `<option value="${opt.value}" ${selected}>${opt.label}</option>`;
            }).join('');

            $(this).html(selectOptions).selectpicker('refresh');
        });
    }

});

$("#print-btn").on("click", function(){
      var divToPrint=document.getElementById('sale-details');
      var newWin=window.open('','Print-Window');
      newWin.document.open();
      newWin.document.write('<link rel="stylesheet" href="<?php echo asset('vendor/bootstrap/css/bootstrap.min.css') ?>" type="text/css"><style type="text/css">@media print {.modal-dialog { max-width: 1000px;} }</style><body onload="window.print()">'+divToPrint.innerHTML+'</body>');
      newWin.document.close();
      setTimeout(function(){newWin.close();},10);
});

$('body').on('click', function(e){
    $('.filter-window').hide('slide', {direction: 'right'}, 'fast');
});



$('select[name="paid_by_id_select"]').on('change', function() {
    var paymentMethod = $(this).val();
    $('input[name="paid_by_id"]').val(paymentMethod);
    
    // First hide all payment method fields
    hideAllPaymentFields();
    
    // Then show the selected one
    showPaymentFieldsByMethod(paymentMethod);
});

function hideAllPaymentFields() {
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".credit-card-fields").hide();
    $("#cheque").hide();
    $(".cheque").hide();
    $("#gift-card").hide();
    $(".gift-card").hide();
    $(".mobile_money_fields").hide();
    $('input[name="cheque_no"]').attr('required', false);
    $('div.qc').hide();
}

function showPaymentFieldsByMethod(method) {
    // Check if there are split payments
    var hasSplitPayments = $('select[name="paid_by_id[]"]').length > 0;
    
    switch(parseInt(method)) {
        case 1: // Cash
            $('div.qc').show();
            break;
        case 2: // Gift Card
            $(".gift-card").show();
            giftCard();
            break;
        case 3: // Credit Card
            $(".credit-card-fields").show();
            $(".card-element").show();
            $(".card-errors").show();
            creditCard();
            break;
        case 4: // Cheque
            $(".cheque").show();
            $('input[name="cheque_no"]').attr('required', true);
            cheque();
            break;
        case 6: // Deposit
            deposits();
            break;
        case 7: // Points
            pointCalculation();
            break;
        case 8: // Mobile Money
            $(".mobile_money_fields").show();
            mobile_money();
            break;
    }
    
    // If there are split payments, show their fields too
    if (hasSplitPayments) {
        $('select[name="paid_by_id[]"]').each(function() {
            var splitMethod = $(this).val();
            if(splitMethod) {
                showPaymentFieldsByMethod(splitMethod);
            }
        });
    }
}
$('select[name="biller_id"]').on('change', function() {
    saveValue(this);
});

$('select[name="warehouse_id"]').on('change', function() {
    saveValue(this);
    var id = $(this).val();
    $.get('../getproduct/' + id, function(data) {
        lims_product_array = [];
        product_code = data[0];
        product_name = data[1];
        product_qty = data[2];
        product_type = data[3];
        product_id = data[4];
        product_list = data[5];
        qty_list = data[6];
        product_warehouse_price = data[7];
        batch_no = data[8];
        product_batch_id = data[9];
        is_embeded = data[11];
        product_shelf = data[12];
        $.each(product_code, function(index) {
            if(is_embeded[index])
              lims_product_array.push(
                    'Product Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index] +
                    ' | Embedded: ' + is_embeded[index]
                );
            else
              lims_product_array.push(
                    'Product Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index] 
                );
        });
    });
    isCashRegisterAvailable(warehouse_id)
});

// Replace the lims_productcodeSearch autocomplete section with this:

var lims_productcodeSearch = $('#lims_productcodeSearch');

lims_productcodeSearch.autocomplete({
    source: function(request, response) {
        var matcher = new RegExp(".?" + $.ui.autocomplete.escapeRegex(request.term), "i");
        response($.grep(lims_product_array, function(item) {
            return matcher.test(item);
        }));
    },
    response: function(event, ui) {
        if (ui.content.length == 1) {
            // Extract ONLY the product name/code from the first item
            var fullData = ui.content[0].value;
            var productCode = extractProductCode(fullData);
            $(this).autocomplete("close");
            productSearch(productCode + '?' + $('#customer_id').val() + '?' + 1);
        } 
        else if(ui.content.length == 0 && $('#lims_productcodeSearch').val().length == 13) {
            productSearch($('#lims_productcodeSearch').val()+'?'+$('#customer_id').val()+'?'+1);
        }
    },
    select: function(event, ui) {
        var fullData = ui.item.value;
        var productCode = extractProductCode(fullData);
        $(this).autocomplete("close");
        productSearch(productCode + '?' + $('#customer_id').val() + '?' + 1);
        return false;
    }
});

/**
 * Extract product code from autocomplete formatted string
 * Input: "Product Name: Power Cable | Price: 2200 | Qty: 19 | Shelf: NA"
 * Output: "Power Cable"
 */
function extractProductCode(fullString) {
    // Remove "Product Name: " prefix and get everything before the first " | "
    var match = fullString.match(/^Product Name:\s*(.+?)\s*\|/);
    if (match && match[1]) {
        return match[1].trim();
    }
    // Fallback: just return the full string if format is unexpected
    return fullString;
}

$('#myTable').keyboard({
        accepted : function(event, keyboard, el) {
            checkQuantity(el.value, true);
      }
    });

$("#myTable").on('click', '.plus', function() {
    rowindex = $(this).closest('tr').index();
    var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
    if(!qty)
      qty = 1;
    else
      qty = parseFloat(qty) + 1;
    if(is_variant[rowindex])
        checkQuantity(String(qty), true);
    else
        checkDiscount(qty, true);
});

   $("select[name=price_option]").on("change", function () {
        console.log($(this).val());
        $("#editModal input[name=edit_unit_price]").val($(this).val());
    });

$("#myTable").on('click', '.minus', function() {
    rowindex = $(this).closest('tr').index();
    var qty = parseFloat($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) - 1;
    if (qty > 0) {
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
    } else {
        qty = 1;
    }
    if(is_variant[rowindex])
        checkQuantity(String(qty), true);
    else
        checkDiscount(qty, true);
});

$("#myTable").on("change", ".batch-no", function () {
    rowindex = $(this).closest('tr').index();
    var product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-id').val();
    var warehouse_id = $('#warehouse_id').val();
    $.get('../check-batch-availability/' + product_id + '/' + $(this).val() + '/' + warehouse_id, function(data) {
        if(data['message'] != 'ok') {
            alert(data['message']);
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.batch-no').val('');
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val('');
        }
        else {
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-batch-id').val(data['product_batch_id']);
            code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
            pos = product_code.indexOf(code);
            product_qty[pos] = data['qty'];
        }
    });
});

//Change quantity
$("#myTable").on('input', '.qty', function() {
    rowindex = $(this).closest('tr').index();
    if($(this).val() < 0 && $(this).val() != '') {
      $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
      alert("Quantity can't be less than 0");
    }
    if(is_variant[rowindex])
        checkQuantity($(this).val(), true);
    else
        checkDiscount($(this).val(), true);
});

$("#myTable").on('click', '.qty', function() {
    rowindex = $(this).closest('tr').index();
});

$(document).on('click', '.sound-btn', function() {
    var audio = $("#mysoundclip1")[0];
    audio.play();
});

$(document).on('click', '.product-img', function() {
    var customer_id = $('#customer_id').val();
    var warehouse_id = $('select[name="warehouse_id"]').val();
    if(!customer_id)
        alert('Please select Customer!');
    else if(!warehouse_id)
        alert('Please select Warehouse!');
    else{
        var data = $(this).data('product');
        product_info = data.split(" ");
        pos = product_code.indexOf(product_info[0]);
        if(pos < 0)
            alert('Product is not avaialable in the selected warehouse');
        else{
            productSearch(data);
        }
    }
});

//Delete product
$("table.order-list tbody").on("click", ".ibtnDel", function(event) {
    var audio = $("#mysoundclip2")[0];
    audio.play();
    rowindex = $(this).closest('tr').index();
    product_price.splice(rowindex, 1);
    product_discount.splice(rowindex, 1);
    tax_rate.splice(rowindex, 1);
    tax_name.splice(rowindex, 1);
    tax_method.splice(rowindex, 1);
    unit_name.splice(rowindex, 1);
    unit_operator.splice(rowindex, 1);
    unit_operation_value.splice(rowindex, 1);
    // Local Storage Oerations
    $(this).closest("tr").remove();
    calculateTotal();
});

//Edit product
$("table.order-list").on("click", ".edit-product", function() {
    rowindex = $(this).closest('tr').index();
    edit();
});

//Update product
$('button[name="update_btn"]').on("click", function() {
    if(is_imei[rowindex]) {
        var imeiNumbers = $("#editModal input[name=imei_numbers]").val();
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
    }
    var edit_discount = $('input[name="edit_discount"]').val();
    var edit_qty = $('input[name="edit_qty"]').val();
    console.log(edit_qty);
    var edit_unit_price = $('input[name="edit_unit_price"]').val();

    if (parseFloat(edit_discount) > parseFloat(edit_unit_price)) {
        alert('Invalid Discount Input!');
        return;
    }

    var tax_rate_all = <?php echo json_encode($tax_rate_all) ?>;

    tax_rate[rowindex] = parseFloat(tax_rate_all[$('select[name="edit_tax_rate"]').val()]);
    tax_name[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();

    product_discount[rowindex] = $('input[name="edit_discount"]').val();
    if(product_type[pos] == 'standard'){
        console.log('standard');
        var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
        var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));
        if (row_unit_operator == '*') {
            product_price[rowindex] = $('input[name="edit_unit_price"]').val() / row_unit_operation_value;
        } else {
            product_price[rowindex] = $('input[name="edit_unit_price"]').val() * row_unit_operation_value;
        }
        var position = $('select[name="edit_unit"]').val();
        var temp_operator = temp_unit_operator[position];
        var temp_operation_value = temp_unit_operation_value[position];
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val(temp_unit_name[position]);
        temp_unit_name.splice(position, 1);
        temp_unit_operator.splice(position, 1);
        temp_unit_operation_value.splice(position, 1);

        temp_unit_name.unshift($('select[name="edit_unit"] option:selected').text());
        temp_unit_operator.unshift(temp_operator);
        temp_unit_operation_value.unshift(temp_operation_value);

        unit_name[rowindex] = temp_unit_name.toString() + ',';
        unit_operator[rowindex] = temp_unit_operator.toString() + ',';
        unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';

        // LocalStorage Operations
    }
    else{
        product_price[rowindex] = $('input[name="edit_unit_price"]').val();
    }
        if (edit_discount > 0) {
        checkDiscount(edit_qty, true, edit_discount);
    } else {
        checkDiscount(edit_qty, false);
    }
    //hide modal
    $('#editModal').modal('hide');
});

$('button[name="order_discount_btn"]').on("click", function() {
    calculateGrandTotal();
});

$('button[name="shipping_cost_btn"]').on("click", function() {
    calculateGrandTotal();
});

$('button[name="order_tax_btn"]').on("click", function() {
    calculateGrandTotal();
});

$(".coupon-check").on("click",function() {
    couponDiscount();
});

$(".payment-btn").on("click", function() {
    var audio = $("#mysoundclip2")[0];
    audio.play();
    $('input[name="paid_amount"]').val($("#grand-total").text());
    $('input[name="paying_amount"]').val($("#grand-total").text());
    $('.qc').data('initial', 1);
    
    // Hide all payment method fields initially
    hideAllPaymentFields();
    
    // Set default payment method to Cash (if not already set)
    var currentMethod = $('select[name="paid_by_id_select"]').val();
    if (!currentMethod) {
        $('select[name="paid_by_id_select"]').val(1); // Set to Cash
        $('select[name="paid_by_id_select"]').selectpicker('refresh');
    }
    
    // Show the selected payment method fields
    showPaymentFieldsByMethod(currentMethod || 1);
});

$("#draft-btn").on("click",function(){
    var audio = $("#mysoundclip2")[0];
    audio.play();
    $('input[name="sale_status"]').val(3);
    $('input[name="paying_amount"]').prop('required',false);
    $('input[name="paid_amount"]').prop('required',false);
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
    }
    else
        $('.payment-form').submit();
});
// Set Submit Btn Here
$("#multiplePaymentBtn").on("click", function () {
    let usedMethods = getSelectedPaymentMethods();

    let options = [
        { value: 1, label: 'Cash' },
        { value: 2, label: 'Gift Card' },
        { value: 3, label: 'Credit Card' },
        { value: 4, label: 'Cheque' },
        { value: 5, label: 'Paypal' },
        { value: 6, label: 'Deposit' },
        { value: 7, label: 'Points' },
        { value: 8, label: 'Mobile Money' }
    ];

    let availableOptions = options.filter(opt => !usedMethods.includes(opt.value));

    if (availableOptions.length === 0) {
        alert("All payment methods have been used.");
        return;
    }

    let selectOptions = availableOptions.map(opt => `<option value="${opt.value}">${opt.label}</option>`).join('');

    let paymentRow = `
        <div class="row mt-2 payment-row align-items-center">
            <div class="col-md-4">
                <label>Payment Amount *</label>
                <input type="number" name="split_amount[]" class="form-control numkey" step="any" required>
            </div>
            <div class="col-md-4">
                <label>Payment Method</label>
                <div class="d-flex align-items-center">
                    <select name="paid_by_id[]" class="form-control selectpicker payment-method-select">
                        ${selectOptions}
                    </select>
                    <button type="button" class="btn btn-danger ml-2 remove-payment"><i class="fa fa-trash"></i></button>
                </div>
            </div>
        </div>
    `;

    $("#paymentMethodsContainer").append(paymentRow);
    $('.selectpicker').selectpicker('refresh');
});

// Use event delegation for split payment method changes
$(document).on('change', 'select[name="paid_by_id[]"]', function() {
    updateSplitPaymentFields();
});

function updateSplitPaymentFields() {
    // Get all selected split payment methods
    var splitMethods = [];
    $('select[name="paid_by_id[]"]').each(function() {
        var val = $(this).val();
        if(val) splitMethods.push(parseInt(val));
    });
    
    // Hide all payment method fields first
    hideAllPaymentFields();
    
    // Show main payment method fields
    var mainMethod = $('select[name="paid_by_id_select"]').val();
    if(mainMethod) {
        showPaymentFieldsByMethod(mainMethod);
    }
    
    // Show all split payment method fields
    splitMethods.forEach(function(method) {
        showPaymentFieldsByMethod(method);
    });
}
function getSelectedPaymentMethods() {
    let methods = [];

    let mainMethod = $("select[name='paid_by_id_select']").val();
    if (mainMethod) methods.push(parseInt(mainMethod));

    $("select[name='paid_by_id[]']").each(function () {
        let val = $(this).val();
        if (val) methods.push(parseInt(val));
    });

    return methods;
}

$("#gift-card-btn").on("click",function(){
    $('select[name="paid_by_id_select"]').val(2).change();
    $('input[name="paid_by_id"]').val(2);
    $('.selectpicker').selectpicker('refresh');
});

$("#credit-card-btn").on("click",function(){
    $('select[name="paid_by_id_select"]').val(3).change();
    $('input[name="paid_by_id"]').val(3);
    $('.selectpicker').selectpicker('refresh');
});

$("#cheque-btn").on("click",function(){
    $('select[name="paid_by_id_select"]').val(4).change();
    $('input[name="paid_by_id"]').val(4);
    $('.selectpicker').selectpicker('refresh');
});

$("#cash-btn").on("click",function(){
    $('select[name="paid_by_id_select"]').val(1).change();
    $('input[name="paid_by_id"]').val(1);
    $('.selectpicker').selectpicker('refresh');
});

$("#paypal-btn").on("click",function(){
    $('select[name="paid_by_id_select"]').val(5).change();
    $('input[name="paid_by_id"]').val(5);
    $('.selectpicker').selectpicker('refresh');
});

$("#deposit-btn").on("click",function() {
    $('select[name="paid_by_id_select"]').val(6).change();
    $('input[name="paid_by_id"]').val(6);
    $('input[name="paid_amount"]').val($("#grand-total").text());
    $('input[name="paying_amount"]').val($("#grand-total").text());
    $('.selectpicker').selectpicker('refresh');
});

$("#point-btn").on("click",function() {
    $('select[name="paid_by_id_select"]').val(7).change();
    $('input[name="paid_by_id"]').val(7);
    $('.selectpicker').selectpicker('refresh');
});

$("#mobile_money-btn").on("click",function() {
    $('select[name="paid_by_id_select"]').val(8).change();
    $('input[name="paid_by_id"]').val(8);
    $('.selectpicker').selectpicker('refresh');
});

$('.payment-option').on("change", function() {
    var id = $(this).val();
    $('input[name="paid_by_id"]').val(id);
    $(".payment-form").off("submit");
    if(id == 2) {
        $('div.qc').hide();
        giftCard();
    }
    else if (id == 3) {
        $('div.qc').hide();
        creditCard();
    }
    else if (id == 4) {
        $('div.qc').hide();
        cheque();
    }
    else {
        hide();
        if(id == 1)
            $('div.qc').show();
        else if(id == 6) {
            $('div.qc').hide();
            deposits();
        }
        else if(id == 7) {
            $('div.qc').hide();
            pointCalculation();
        }
    }
});

// $('select[name="paid_by_id_select"]').on("change", function() {
//     var id = $(this).val();
//     $(".payment-form").off("submit");
//     if(id == 2) {
//         $('div.qc').hide();
//         giftCard();
//     }
//     else if (id == 3) {
//         $('div.qc').hide();
//         creditCard();
//     }
//     else if (id == 4) {
//         $('div.qc').hide();
//         cheque();
//     }
//     else {
//         hide();
//         if(id == 1)
//             $('div.qc').show();
//         else if(id == 6) {
//             $('div.qc').hide();
//             deposits();
//         }
//         else if(id == 7) {
//             $('div.qc').hide();
//             pointCalculation();
//         }
//     }
//         $("#paymentMethodsContainer").empty();
//     refreshPaymentMethodOptions();
// });


$('#add-payment input[name="paying_amount"]').on("input", function() {
    change($(this).val(), $('input[name="paid_amount"]').val());
});

$('#add-payment select[name="gift_card_id_select"]').on("change", function() {
    var balance = gift_card_amount[$(this).val()] - gift_card_expense[$(this).val()];
    $('#add-payment input[name="gift_card_id"]').val($(this).val());
    if($('input[name="paid_amount"]').val() > balance){
        alert('Amount exceeds card balance! Gift Card balance: '+ balance);
    }
});

$('input[name="paid_amount"]').on("input", function() {
    if( $(this).val() > parseFloat($('input[name="paying_amount"]').val()) ) {
        alert('Paying amount cannot be bigger than recieved amount');
        $(this).val('');
    }
    else if( $(this).val() > parseFloat($('#grand-total').text()) ){
        alert('Paying amount cannot be bigger than grand total');
        $(this).val('');
    }

    change( $('input[name="paying_amount"]').val(), $(this).val() );
    var id = $('input[name="paid_by_id"]').val();
    if(id == 2){
        var balance = gift_card_amount[$("#gift_card_id_select").val()] - gift_card_expense[$("#gift_card_id_select").val()];
        if($(this).val() > balance)
            alert('Amount exceeds card balance! Gift Card balance: '+ balance);
    }
    else if(id == 6){
        if( $('input[name="paid_amount"]').val() > deposit[$('#customer_id').val()] )
            alert('Amount exceeds customer deposit! Customer deposit : '+ deposit[$('#customer_id').val()]);
    }
});

$('.transaction-btn-plus').on("click", function() {
    $(this).addClass('d-none');
    $('.transaction-btn-close').removeClass('d-none');
});

$('.transaction-btn-close').on("click", function() {
    $(this).addClass('d-none');
    $('.transaction-btn-plus').removeClass('d-none');
});

$('.coupon-btn-plus').on("click", function() {
    $(this).addClass('d-none');
    $('.coupon-btn-close').removeClass('d-none');
});

$('.coupon-btn-close').on("click", function() {
    $(this).addClass('d-none');
    $('.coupon-btn-plus').removeClass('d-none');
});


$(document).on('click', '.qc-btn', function(e) {
    if($(this).data('amount')) {
        if($('.qc').data('initial')) {
            $('input[name="paying_amount"]').val( $(this).data('amount').toFixed({{$general_setting->decimal}}) );
            $('.qc').data('initial', 0);
        }
        else {
            $('input[name="paying_amount"]').val( (parseFloat($('input[name="paying_amount"]').val()) + $(this).data('amount')).toFixed({{$general_setting->decimal}}) );
        }
    }
    else
        $('input[name="paying_amount"]').val('{{number_format(0, $general_setting->decimal, '.', '')}}');
    change( $('input[name="paying_amount"]').val(), $('input[name="paid_amount"]').val() );
});

$(document).on("input", 'input[name="split_amount[]"]', function() {
    let payingAmount = parseFloat($('input[name="paying_amount"]').val()) || 0;
    let totalSplit = getTotalSplitAmount();
    let mainPaid = parseFloat($('input[name="paid_amount"]').val()) || 0;
    let totalPaid = totalSplit + mainPaid;

    if (totalPaid > payingAmount) {
        alert('Total paid (main + split) cannot be greater than paying amount');
        $(this).val('');
        totalSplit = getTotalSplitAmount(); // recalculate after clearing this input
        totalPaid = totalSplit + mainPaid;
    }

    change(payingAmount, totalPaid);
});



function change(paying_amount, paid_amount) {
    $("#change").text( parseFloat(paying_amount - paid_amount).toFixed({{$general_setting->decimal}}) );
}


function getTotalSplitAmount() {
    let total = 0;
    $('input[name="split_amount[]"]').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    return total;
}

function confirmDelete() {
    if (confirm("Are you sure want to delete?")) {
        return true;
    }
    return false;
}

function productSearch(data){
    var product_info = data.split(" ");
    var product_code = product_info[0];
    var pre_qty = 0;
    $(".product-code").each(function(i) {
        if ($(this).val() == product_code) {
            rowindex = i;
            pre_qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
        }
    });
    data += '?'+$('#customer_id').val()+'?'+(parseFloat(pre_qty) + 1);
    $.ajax({
        type: 'GET',
        async: false,
        url: '../lims_product_search',
        data: {
            data: data
        },
        success: function(data) {
            //console.log(data);
            var flag = 1;
            if (pre_qty > 0) {
                /*if(pre_qty)
                    var qty = parseFloat(pre_qty) + data[15];
                else*/
                    var qty = data[15];
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
                pos = product_code.indexOf(data[1]);
                if(!data[11] && product_warehouse_price[pos]) {
                    product_price[rowindex] = parseFloat(product_warehouse_price[pos] * currency['exchange_rate']) + parseFloat(product_warehouse_price[pos] * currency['exchange_rate'] * customer_group_rate);
                }
                else{
                    product_price[rowindex] = parseFloat(data[2] * currency['exchange_rate']) + parseFloat(data[2] * currency['exchange_rate'] * customer_group_rate);
                }
                flag = 0;
                checkQuantity(String(qty), true);
                flag = 0;
            }
            $("input[name='product_code_name']").val('');
            if(flag){
                addNewProduct(data);
            }
        }
   });
}


function addNewProduct(data){
    var newRow = $("<tr>");
    var cols = '';
    temp_unit_name = (data[6]).split(',');
    pos = product_code.indexOf(data[1]);
    cols += '<td class="col-sm-2 product-title"><button type="button" class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"><strong><span class="product-name">' + data[0] + '</span></strong></button><br>' + data[1] + '<p>In Stock: <span class="in-stock"></span></p></td>';
    if(data[12]) {
        cols += '<td class="col-sm-2"><input type="text" class="form-control batch-no" value="'+batch_no[pos]+'" required/> <input type="hidden" class="product-batch-id" name="product_batch_id[]" value="'+product_batch_id[pos]+'"/> </td>';
    }
    else {
        cols += '<td class="col-sm-2"><input type="text" class="form-control batch-no" disabled/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/> </td>';
    }
    cols += '<td class="col-sm-2 product-price">' + parseFloat(data[2]).toFixed({{$general_setting->decimal}}) + '</td>';
    cols += '<td class="col-sm-3"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default minus"><span class="dripicons-minus"></span></button></span><input type="text" name="qty[]" class="form-control qty numkey input-number" step="any" value="'+data[15]+'" required><span class="input-group-btn"><button type="button" class="btn btn-default plus"><span class="dripicons-plus"></span></button></span></div></td>';
    cols += '<td class="col-sm-2 sub-total">0.00</td>';
    cols += '<td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="dripicons-cross"></i></button></td>';
    cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
    cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
    cols += '<input type="hidden" class="product_price" name="product_price[]" value="' + parseFloat(data[2]).toFixed({{$general_setting->decimal}}) + '"/>';
    cols += '<input type="hidden" class="sale-unit" name="sale_unit[]" value="' + temp_unit_name[0] + '"/>';
    cols += '<input type="hidden" class="net_unit_price" name="net_unit_price[]" value="0"/>';
    cols += '<input type="hidden" class="discount-value" name="discount[]" value="0"/>';
    cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + parseFloat(data[3]).toFixed({{$general_setting->decimal}}) + '"/>';
    cols += '<input type="hidden" class="tax-value" name="tax[]" value="0"/>';
    cols += '<input type="hidden" class="tax-name" value="'+data[4]+'" />';
    cols += '<input type="hidden" class="tax-method" value="'+data[5]+'" />';
    cols += '<input type="hidden" class="sale-unit-operator" value="'+data[7]+'" />';
    cols += '<input type="hidden" class="sale-unit-operation-value" value="'+data[8]+'" />';
    cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" value="0"/>';
    cols += '<input type="hidden" class="item_discount" name="item_discount[]" value="0" />';
    cols += '<input type="hidden" class="wholesale_price" name="wholesale_price[]" value="' + (data[16] || 0) + '" />';
    cols += '<input type="hidden" class="imei-number" name="imei_number[]" />';

    //set product price variable
   


    newRow.append(cols);
    if(keyboard_active==1) {
        $("table.order-list tbody").prepend(newRow).find('.qty').keyboard({usePreview: false, layout: 'custom', display: { 'accept'  : '&#10004;', 'cancel'  : '&#10006;' }, customLayout : {
          'normal' : ['1 2 3', '4 5 6', '7 8 9','0 {dec} {bksp}','{clear} {cancel} {accept}']}, restrictInput : true, preventPaste : true, autoAccept : true, css: { container: 'center-block dropdown-menu', buttonDefault: 'btn btn-default', buttonHover: 'btn-primary',buttonAction: 'active', buttonDisabled: 'disabled'},});
    }
    else
        $("table.order-list tbody").prepend(newRow);

    rowindex = newRow.index();

    // Ensure currency exchange rate is valid
    var exchangeRate = parseFloat(currency['exchange_rate']) || 1;
    var groupRate = parseFloat(customer_group_rate) || 0;

    if(!data[11] && product_warehouse_price[pos]) {
        var warehousePrice = parseFloat(product_warehouse_price[pos]) || 0;
        product_price.splice(rowindex, 0, (warehousePrice * exchangeRate) + (warehousePrice * exchangeRate * groupRate));
    }
    else {
        var basePrice = parseFloat(data[2]) || 0;
        product_price.splice(rowindex, 0, (basePrice * exchangeRate) + (basePrice * exchangeRate * groupRate));
    }
    product_discount.splice(rowindex, 0, 0);
    tax_rate.splice(rowindex, 0, parseFloat(data[3]));
    tax_name.splice(rowindex, 0, data[4]);
    tax_method.splice(rowindex, 0, data[5]);
    unit_name.splice(rowindex, 0, data[6]);
    unit_operator.splice(rowindex, 0, data[7]);
    unit_operation_value.splice(rowindex, 0, data[8]);
    is_imei.splice(rowindex, 0, data[13]);
    is_variant.splice(rowindex, 0, data[14]);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product_price').val(product_price[rowindex]);

    // Set LocalStorage here
    checkQuantity(data[15], true);
    checkDiscount(data[15], true);

    if(data[13]) {
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.edit-product').click();
    }
}
function edit(){
    console.log("clicked");
    $(".imei-section").remove();
    if(is_imei[rowindex]) {
        var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();

        htmlText = '<div class="col-md-12 form-group imei-section"><label>IMEI or Serial Numbers</label><input type="text" name="imei_numbers" value="'+imeiNumbers+'" class="form-control imei_number" placeholder="Type imei or serial numbers and separate them by comma. Example:1001,2001" step="any"></div>';
        $("#editModal .modal-element").append(htmlText);
    }

    var row_product_name_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
    $('#modal_header').text(row_product_name_code);

    var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
    $('input[name="edit_qty"]').val(qty);
     var item_discount = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.item_discount').val();
     var disc = parseFloat(item_discount).toFixed({{$general_setting->decimal}});
     var prod_disc = parseFloat(product_discount[rowindex]).toFixed({{$general_setting->decimal}});
     if (disc != prod_disc) {
         $('input[name="edit_discount"]').val(disc);
     }
     else {
         $('input[name="edit_discount"]').val(prod_disc);
     }
    // $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed({{$general_setting->decimal}}));

    var tax_name_all = <?php echo json_encode($tax_name_all) ?>;
    pos = tax_name_all.indexOf(tax_name[rowindex]);
    $('select[name="edit_tax_rate"]').val(pos);

    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
    pos = product_code.indexOf(row_product_code);
    
    // Get wholesale price from hidden input
    var wholesale_price = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.wholesale_price').val();
    
    // Populate price_option select
  
    
    if(product_type[pos] == 'standard'){
        unitConversion();
        temp_unit_name = (unit_name[rowindex]).split(',');
        temp_unit_name.pop();
        temp_unit_operator = (unit_operator[rowindex]).split(',');
        temp_unit_operator.pop();
        temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
        temp_unit_operation_value.pop();
        $('select[name="edit_unit"]').empty();
        $.each(temp_unit_name, function(key, value) {
            $('select[name="edit_unit"]').append('<option value="' + key + '">' + value + '</option>');
        });
        $("#edit_unit").show();
    }
    else{
        row_product_price = product_price[rowindex];
        $("#edit_unit").hide();
    }


    $('input[name="edit_unit_price"]').val(row_product_price.toFixed({{$general_setting->decimal}}));
    $('select[name="price_option"]').empty();

    // Normal price
    let normal = parseFloat(row_product_price).toFixed({{$general_setting->decimal}});
    $('select[name="price_option"]').append(
        '<option value="' + normal + '">' + normal + '</option>'
    );

    // Wholesale price
    if (wholesale_price && parseFloat(wholesale_price) > 0) {
        let wholesale = parseFloat(wholesale_price).toFixed({{$general_setting->decimal}});
        $('select[name="price_option"]').append(
            '<option value="' + wholesale + '">' + wholesale + '</option>'
        );
    }

    // Select the current price
    $('select[name="price_option"]').val(normal);  
    $('.selectpicker').selectpicker('refresh');

}


function couponDiscount() {
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
    }
    else if($("#coupon-code").val() != ''){
        valid = 0;
        $.each(coupon_list, function(key, value) {
            if($("#coupon-code").val() == value['code']){
                valid = 1;
                todyDate = <?php echo json_encode(date('Y-m-d'))?>;
                if(parseFloat(value['quantity']) <= parseFloat(value['used']))
                    alert('This Coupon is no longer available');
                else if(todyDate > value['expired_date'])
                    alert('This Coupon has expired!');
                else if(value['type'] == 'fixed'){
                    if(parseFloat($('input[name="grand_total"]').val()) >= value['minimum_amount']) {
                        $('input[name="grand_total"]').val($('input[name="grand_total"]').val() - (value['amount'] * currency['exchange_rate']));
                        $('#grand-total').text(parseFloat($('input[name="grand_total"]').val()).toFixed({{$general_setting->decimal}}));
                        if(!$('input[name="coupon_active"]').val())
                            alert('Congratulation! You got '+(value['amount'] * currency['exchange_rate'])+' '+currency['code']+' discount');
                        $(".coupon-check").prop("disabled",true);
                        $("#coupon-code").prop("disabled",true);
                        $('input[name="coupon_active"]').val(1);
                        $('input[name="coupon_id"]').val(value['id']);
                        $('input[name="coupon_discount"]').val(value['amount'] * currency['exchange_rate']);
                        $('#coupon-text').text(parseFloat(value['amount'] * currency['exchange_rate']).toFixed({{$general_setting->decimal}}));
                        $('#coupon-modal').modal('hide');
                    }
                    else
                        alert('Grand Total is not sufficient for discount! Required '+value['minimum_amount']+' '+currency);
                }
                else{
                    var grand_total = $('input[name="grand_total"]').val();
                    var coupon_discount = grand_total * (value['amount'] / 100);
                    grand_total = grand_total - coupon_discount;
                    $('input[name="grand_total"]').val(grand_total);
                    $('#grand-total').text(parseFloat(grand_total).toFixed({{$general_setting->decimal}}));
                    if(!$('input[name="coupon_active"]').val())
                            alert('Congratulation! You got '+value['amount']+'% discount');
                    $(".coupon-check").prop("disabled",true);
                    $("#coupon-code").prop("disabled",true);
                    $('input[name="coupon_active"]').val(1);
                    $('input[name="coupon_id"]').val(value['id']);
                    $('input[name="coupon_discount"]').val(coupon_discount);
                    $('#coupon-text').text(parseFloat(coupon_discount).toFixed({{$general_setting->decimal}}));
                    $('#coupon-modal').modal('hide');
                }
            }
        });
        if(!valid)
            alert('Invalid coupon code!');
    }
}



function checkDiscount(qty, flag, manualDiscount = null) {
    console.log("Discount identifier");
    var customer_id = $('#customer_id').val();
    var product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-id').val();

    if (flag) {
        console.log("Checking discount for qty: " + qty + ", customer_id: " + customer_id + ", product_id: " + product_id);
        $.ajax({
            type: 'GET',
            async: false,
            url: 'sales/check-discount?qty=' + qty + '&customer_id=' + customer_id + '&product_id=' + product_id,
            success: function(data) {
                console.log(data);
                pos = product_code.indexOf($('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-code').val());
                var exchangeRate = parseFloat(currency['exchange_rate']) || 1;
                var groupRate = parseFloat(customer_group_rate) || 0;
                var discountedPrice = parseFloat(data[0]) || 0;
                product_price[rowindex] = (discountedPrice * exchangeRate) + (discountedPrice * exchangeRate * groupRate);
                   let serverDiscount = data[2];
                    let finalDiscount = (serverDiscount !== null && !isNaN(serverDiscount))
                        ? parseFloat(serverDiscount)
                        : parseFloat(manualDiscount || 0);
                    console.log("Final discount to apply:", finalDiscount);

                    // Sanitize current discount value
                    let discountText = $('#discount').text().replace(/,/g, '').trim();
                    let productDiscount = parseFloat(discountText);
                    if (isNaN(productDiscount)) {
                        console.warn("Resetting non-numeric discount:", discountText);
                        productDiscount = 0;
                    }

                    // Apply the new discount
                    let updatedDiscount = productDiscount;
                    if (flag === true)
                        updatedDiscount = productDiscount + finalDiscount;
                    else if (flag === false)
                        updatedDiscount = productDiscount - (finalDiscount * qty);
                    else if (flag === 'input')
                        updatedDiscount = productDiscount - finalDiscount * previousqty + finalDiscount * qty;
                    else
                        updatedDiscount = productDiscount - finalDiscount;

                    // Ensure it's numeric and formatted
                    if (isNaN(updatedDiscount)) updatedDiscount = 0;

                    $('#discount').text(updatedDiscount.toFixed(decimals));
                    console.log("Updated discount:", updatedDiscount);
            }
        });
    }

    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
    flag = true;
    console.log("Re-Checking quantity for qty: " + qty);
    checkQuantity(String(qty), flag);
    localStorage.setItem("tbody-id", $("table.order-list tbody").html());
}

function checkQuantity(sale_qty, flag) {
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
    pos = product_code.indexOf(row_product_code);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.in-stock').text(product_qty[pos]);
    if(without_stock == 'no') {
      if(product_type[pos] == 'standard') {
          var operator = unit_operator[rowindex].split(',');
          var operation_value = unit_operation_value[rowindex].split(',');
          if(operator[0] == '*')
              total_qty = sale_qty * operation_value[0];
          else if(operator[0] == '/')
              total_qty = sale_qty / operation_value[0];
          if (total_qty > parseFloat(product_qty[pos])) {
              alert('Quantity exceeds stock quantity!');
              if (flag) {
                  sale_qty = sale_qty.substring(0, sale_qty.length - 1);
                  $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                  checkQuantity(sale_qty, true);
              } else {
                  edit();
                  return;
              }
          }
      }
      else if(product_type[pos] == 'combo'){
          child_id = product_list[pos].split(',');
          child_qty = qty_list[pos].split(',');
          $(child_id).each(function(index) {
              var position = product_id.indexOf(parseInt(child_id[index]));
              if( parseFloat(sale_qty * child_qty[index]) > product_qty[position] ) {
                  alert('Quantity exceeds stock quantity!');
                  if (flag) {
                      sale_qty = sale_qty.substring(0, sale_qty.length - 1);
                      $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                  }
                  else {
                      edit();
                      flag = true;
                      return false;
                  }
              }
          });
      }
    }
    else {
      $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
    }
    if(!flag){
        $('#editModal').modal('hide');
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
    }
    calculateRowProductData(sale_qty);
}



function calculateRowProductData(quantity) {
    if(product_type[pos] == 'standard')
        unitConversion();
    else
        row_product_price = product_price[rowindex];

    // Ensure row_product_price is a valid number
    if (isNaN(row_product_price) || row_product_price === undefined) {
        row_product_price = 0;
        console.warn("Invalid row_product_price detected, resetting to 0");
    }

    let rate = parseFloat(tax_rate[rowindex]);
    if (isNaN(rate)) {
        rate = 0;
        tax_rate[rowindex] = 0;
    }
    
    if(product_discount[rowindex]){
        if (tax_method[rowindex] == 1) {
            var net_unit_price = row_product_price - product_discount[rowindex];
            var tax = net_unit_price * quantity * (rate / 100);
            var sub_total = (net_unit_price * quantity) + tax;

            var sub_total_unit = quantity ? (sub_total / quantity) : sub_total;
        }
        else {
            var sub_total_unit = row_product_price - product_discount[rowindex];
            var net_unit_price = (100 / (100 + rate)) * sub_total_unit;
            var tax = (sub_total_unit - net_unit_price) * quantity;
            var sub_total = sub_total_unit * quantity;
        }

    } else{
        console.log(row_product_price);

        var sub_total_unit = row_product_price;
        var net_unit_price = (100 / (100 + rate)) * sub_total_unit;
        var tax = (sub_total_unit - net_unit_price) * quantity;
        var sub_total = sub_total_unit * quantity;      

    }

    // Ensure all calculated values are valid numbers
    if (isNaN(net_unit_price)) net_unit_price = 0;
    if (isNaN(tax)) tax = 0;
    if (isNaN(sub_total)) sub_total = 0;
    if (isNaN(sub_total_unit)) sub_total_unit = 0;

    //log and set value
    console.log("Row Index:" + rowindex);
    console.log("Quantity:" + quantity);
    console.log("Product Discount:" + product_discount[rowindex]);
    console.log("Tax Rate:" + tax_rate[rowindex]);
    console.log("Net Unit Price:" + net_unit_price);
    console.log("Tax:" + tax);
    console.log("Sub Total Unit:" + sub_total_unit);


    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-price').text(sub_total_unit.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed({{$general_setting->decimal}}));

    calculateTotal();
}

function unitConversion() {
    var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(","));
    var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(","));

    if (row_unit_operator == '*') {
        row_product_price = product_price[rowindex] * row_unit_operation_value;
    } else {
        row_product_price = product_price[rowindex] / row_unit_operation_value;
    }
}

function calculateTotal() {
    //Sum of quantity
    var total_qty = 0;
    $("table.order-list tbody .qty").each(function(index) {
        if ($(this).val() == '') {
            total_qty += 0;
        } else {
            total_qty += parseFloat($(this).val());
        }
    });
    $('input[name="total_qty"]').val(total_qty);

    //Sum of discount
    var total_discount = 0;
    $("table.order-list tbody .discount-value").each(function() {
        total_discount += parseFloat($(this).val());
    });

    $('input[name="total_discount"]').val(total_discount.toFixed({{$general_setting->decimal}}));

    //Sum of tax
    var total_tax = 0;
    $(".tax-value").each(function() {
        total_tax += parseFloat($(this).val());
    });

    $('input[name="total_tax"]').val(total_tax.toFixed({{$general_setting->decimal}}));

    //Sum of subtotal
    var total = 0;
    $(".sub-total").each(function() {
        total += parseFloat($(this).text());
    });
    $('input[name="total_price"]').val(total.toFixed({{$general_setting->decimal}}));

    calculateGrandTotal();
}

function calculateGrandTotal() {

    var item = $('table.order-list tbody tr:last').index();

    var total_qty = parseFloat($('input[name="total_qty"]').val());
    var subtotal = parseFloat($('input[name="total_price"]').val());
    var order_tax = parseFloat($('select[name="order_tax_rate"]').val());
    var order_discount = parseFloat($('input[name="order_discount"]').val()*currency['exchange_rate']);
    if (!order_discount)
        order_discount = {{number_format(0, $general_setting->decimal, '.', '')}};
    $("#discount").text(order_discount.toFixed({{$general_setting->decimal}}));

    var shipping_cost = parseFloat($('input[name="shipping_cost"]').val()*currency['exchange_rate']);
    if (!shipping_cost)
        shipping_cost = {{number_format(0, $general_setting->decimal, '.', '')}};

    item = ++item + '(' + total_qty + ')';
    order_tax = (subtotal - order_discount) * (order_tax / 100);
    var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;
    $('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));

    couponDiscount();
    var coupon_discount = parseFloat($('input[name="coupon_discount"]').val());
    if (!coupon_discount)
        coupon_discount = {{number_format(0, $general_setting->decimal, '.', '')}};
    grand_total -= coupon_discount;

    $('#item').text(item);
    $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
    $('#subtotal').text(subtotal.toFixed({{$general_setting->decimal}}));
    $('#tax').text(order_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="order_tax"]').val(order_tax.toFixed({{$general_setting->decimal}}));
    $('#shipping-cost').text(shipping_cost.toFixed({{$general_setting->decimal}}));
    $('#grand-total').text(grand_total.toFixed({{$general_setting->decimal}}));
    $('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));
}

function hide() {
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".credit-card-fields").hide();
    $("#cheque").hide();
    $("#gift-card").hide();
    $(".mobile_money_fields").hide();
    $('input[name="cheque_no"]').attr('required', false);
}

function giftCard() {
    $("#gift-card").show();
    $.ajax({
        url: '../get_gift_card',
        type: "GET",
        dataType: "json",
        success:function(data) {
            $('#add-payment select[name="gift_card_id_select"]').empty();
            $.each(data, function(index) {
                gift_card_amount[data[index]['id']] = data[index]['amount'];
                gift_card_expense[data[index]['id']] = data[index]['expense'];
                $('#add-payment select[name="gift_card_id_select"]').append('<option value="'+ data[index]['id'] +'">'+ data[index]['card_no'] +'</option>');
            });
            $('.selectpicker').selectpicker('refresh');
            $('.selectpicker').selectpicker();
        }
    });
    $(".credit-card-fields").hide();
    $(".card-element").hide();
    $(".card-errors").hide();
    $("#cheque").hide();
    $('input[name="cheque_no"]').attr('required', false);
}

function mobile_money() {
    $(".mobile_money_fields").show();
    $(".gift-card").hide();
    $(".credit-card-fields").hide();
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".cheque").hide();
    $('input[name="cheque_no"]').attr('required', false);
}

function cheque() {
    $("#cheque").show();
    $(".credit-card-fields").hide();
    $(".card-element").hide();
    $(".card-errors").hide();
    $("#gift-card").hide();
    $('input[name="cheque_no"]').attr('required', true);
}

function creditCard() {
    $.getScript("{{ asset('vendor/stripe/checkout.js') }}");
    $(".credit-card-fields").show();
    $(".card-element").show();
    $(".card-errors").show();
    $("#cheque").hide();
    $("#gift-card").hide();
    $('input[name="cheque_no"]').attr('required', false);
}

function deposits() {
    if($('input[name="paid_amount"]').val() > deposit[$('#customer_id').val()]){
        alert('Amount exceeds customer deposit! Customer deposit : '+ deposit[$('#customer_id').val()]);
    }
    $('input[name="cheque_no"]').attr('required', false);
}

function pointCalculation() {
    paid_amount = $('input[name=paid_amount]').val();
    required_point = Math.ceil(paid_amount / reward_point_setting['per_point_amount']);
    if(required_point > points[$('#customer_id').val()]) {
      alert('Customer does not have sufficient points. Available points: '+points[$('#customer_id').val()]);
    }
    else {
      $("input[name=used_points]").val(required_point);
    }
}

function cancel(rownumber) {
    while(rownumber >= 0) {
        product_price.pop();
        product_discount.pop();
        tax_rate.pop();
        tax_name.pop();
        tax_method.pop();
        unit_name.pop();
        unit_operator.pop();
        unit_operation_value.pop();
        $('table.order-list tbody tr:last').remove();
        rownumber--;
    }
    $('input[name="shipping_cost"]').val('');
    $('input[name="order_discount"]').val('');
    $('select[name="order_tax_rate"]').val(0);
    calculateTotal();
}

function confirmCancel() {
    var audio = $("#mysoundclip2")[0];
    audio.play();
    if (confirm("Are you sure want to cancel?")){
        cancel($('table.order-list tbody tr:last').index());
    }
    return false;
}

document.addEventListener("DOMContentLoaded", function () {
    const paidBySelect = document.querySelector('[name="paid_by_id_select"]');
    const mobileMoneyFields = document.querySelectorAll('.mobile_money_fields');
    function toggleMobileMoneyFields() {
        if (paidBySelect.value === '8') {
            mobileMoneyFields.forEach(field => field.style.display = 'block');
        } else {
            mobileMoneyFields.forEach(field => field.style.display = 'none');
        }
    }
    toggleMobileMoneyFields();
    paidBySelect.addEventListener('change', toggleMobileMoneyFields);
});

document.addEventListener("DOMContentLoaded", function () {
    const dropdown = document.getElementById("mobile_money_operator");
    const hiddenField = document.getElementById("selected_mobile_op");
    dropdown.addEventListener("change", function () {
        hiddenField.value = dropdown.value;
    });
    hiddenField.value = dropdown.value;
});

$(document).on('submit', '.payment-form', function(e) {
    $('#payment-error-alert').addClass('d-none');
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!");
        e.preventDefault();
        return;
    }
    // Check if change is negative (insufficient payment)
    var changeVal = parseFloat($("#change").text().replace(/,/g, '')) || 0;
    if (changeVal < 0) {
        e.preventDefault();
        $('#payment-error-message').text('Insufficient payment - the amount received is less than the total due. Please collect the full amount before submitting.');
        $('#payment-error-alert').removeClass('d-none');
        $('#add-payment').modal('show');
        return;
    }
    $("#submit-button").prop('disabled', true);
});




</script>
<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endpush
