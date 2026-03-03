@extends('backend.layout.top-header')
@section('content')
<link rel="stylesheet" href="{{ asset('css/pos-layout.css') }}" type="text/css">

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
        

    </style>
    {!! ToastMagic::styles() !!}
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
                {{-- Use cached permission checks from controller --}}
                @if($permissionChecks['category'])
                <li class="dropdown-item"><a data-toggle="modal" data-target="#category-modal">{{__('file.Add Category')}}</a></li>
                @endif
                @if($permissionChecks['products-add'])
                <li class="dropdown-item"><a href="{{route('products.create')}}">{{__('file.add_product')}}</a></li>
                @endif
                @if($permissionChecks['purchases-add'])
                <li class="dropdown-item"><a href="{{route('purchases.create')}}">{{trans('file.Add Purchase')}}</a></li>
                @endif
                @if($permissionChecks['sales-add'])
                <li class="dropdown-item"><a href="{{route('sales.create')}}">{{trans('file.Add Sale')}}</a></li>
                @endif
                @if($permissionChecks['expenses-add'])
                <li class="dropdown-item"><a data-toggle="modal" data-target="#expense-modal"> {{trans('file.Add Expense')}}</a></li>
                @endif
                @if($permissionChecks['quotes-add'])
                <li class="dropdown-item"><a href="{{route('quotations.create')}}">{{trans('file.Add Quotation')}}</a></li>
                @endif
                @if($permissionChecks['transfers-add'])
                <li class="dropdown-item"><a href="{{route('transfers.create')}}">{{trans('file.Add Transfer')}}</a></li>
                @endif
                @if($permissionChecks['returns-add'])
                <li class="dropdown-item"><a href="#" data-toggle="modal" data-target="#add-sale-return"> {{trans('file.Add Return')}}</a></li>
                @endif
                @if($permissionChecks['purchase-return-add'])
                <li class="dropdown-item"><a href="#" data-toggle="modal" data-target="#add-purchase-return"> {{trans('file.Add Purchase Return')}}</a></li>
                @endif
                @if($permissionChecks['users-add'])
                <li class="dropdown-item"><a href="{{route('user.create')}}">{{trans('file.Add User')}}</a></li>
                @endif
                @if($permissionChecks['customers-add'])
                <li class="dropdown-item"><a href="{{route('customer.create')}}">{{trans('file.Add Customer')}}</a></li>
                @endif
                @if($permissionChecks['billers-add'])
                <li class="dropdown-item"><a href="{{route('biller.create')}}">{{trans('file.Add Biller')}}</a></li>
                @endif
                @if($permissionChecks['suppliers-add'])
                <li class="dropdown-item"><a href="{{route('supplier.create')}}">{{trans('file.Add Supplier')}}</a></li>
                @endif
                </ul>
            </div>
            <li class="nav-item ml-4"><a id="btnFullscreen" data-toggle="tooltip" title="Full Screen"><i class="dripicons-expand"></i></a></li>
            {{-- Use cached permission checks from controller instead of DB calls --}}
            @if($permissionChecks['pos_setting'])
            <li class="nav-item"><a class="dropdown-item" data-toggle="tooltip" href="{{route('setting.pos')}}" title="{{trans('file.POS Setting')}}"><i class="dripicons-gear"></i></a> </li>
            @endif
            <li class="nav-item">
                <a href="{{route('sales.printLastReciept')}}" data-toggle="tooltip" title="{{trans('file.Print Last Reciept')}}"><i class="dripicons-print"></i></a>
            </li>
            <li class="nav-item">
                <a href="" id="register-details-btn" data-toggle="tooltip" title="{{trans('file.Cash Register Details')}}"><i class="dripicons-briefcase"></i></a>
            </li>
            {{-- Pre-computed permission checks from controller cache --}}
            @if($permissionChecks['today_sale'])
            <li class="nav-item">
                <a href="" id="today-sale-btn" data-toggle="tooltip" title="{{trans('file.Today Sale')}}"><i class="dripicons-shopping-bag"></i></a>
            </li>
            @endif
            @if($permissionChecks['today_profit'])
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
                    @if($permissionChecks['general_setting'])
                    <li>
                        <a href="{{route('setting.general')}}"><i class="dripicons-gear"></i> {{trans('file.settings')}}</a>
                    </li>
                    @endif
                    <li>
                        <a href="{{url('my-transactions/'.date('Y').'/'.date('m'))}}"><i class="dripicons-swap"></i> {{trans('file.My Transaction')}}</a>
                    </li>
                    @if(Auth::user()->role_id != 5)
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
                <div class="row" style="padding: 10px;"> 
                     
                        @if(in_array("cash",$options))
                        <div class="column-5 col-6">
                            <button  style="background: #f5f5f0 !important; color:black !important;" type="button" class="btn btn-sm btn-custom payment-btn" data-toggle="modal" data-target="#add-payment" id="cash-btn"><i class="fa fa-money"></i> <br> {{trans('file.Cash')}}</button>
                        </div>
                        @endif
               
                        @if(in_array("card",$options))
                        <div class="column-5 col-6 ">
                            <button style="background: #f5f5f0 !important; color:black !important;" type="button" class="btn btn-sm btn-custom payment-btn" data-toggle="modal" data-target="#add-payment" id="credit-card-btn"><i class="fa fa-credit-card fa-2x"></i><br> {{trans('file.Card')}}</button>
                        </div>
                        @endif
                        
                       
       
                        @if(in_array("paypal",$options) && $lims_pos_setting_data && (strlen($lims_pos_setting_data->paypal_live_api_username)>0) && (strlen($lims_pos_setting_data->paypal_live_api_password)>0) && (strlen($lims_pos_setting_data->paypal_live_api_secret)>0))
                        <div class="column-5 col-6">
                            <button style="background: #f5f5f0 !important; color:black !important; border-color:#99b3e6;" type="button" class="btn btn-sm btn-custom payment-btn" data-toggle="modal" data-target="#add-payment" id="paypal-btn"><i class="fa fa-paypal fa-2x"></i><br> {{trans('file.PayPal')}}</button>
                        </div>
                        @endif
                        <div class="column-5 col-6">
                            <button style="background: #f5f5f0 !important; color:black !important; border-color:#99b3e6;" type="button" class="btn btn-sm btn-custom" id="draft-btn"><i class="dripicons-flag"></i> <br> Hold</button>

                        </div>
                        @if(in_array("cheque",$options))
                        <div class="column-5 col-6">
                            <button style="background: #f5f5f0 !important; color:black !important; border-color:#99b3e6;" type="button" class="btn btn-sm btn-custom payment-btn" data-toggle="modal" data-target="#add-payment" id="cheque-btn"><i class="fa fa-money"></i><br> {{trans('file.Cheque')}}</button>
                        </div>
                        @endif
                        
                        @if(in_array("gift_card",$options))
                        <div class="column-5 col-6">
                            <button style="background: #f5f5f0 !important; color:black !important; border-color:#99b3e6;" class="btn btn-sm btn-custom payment-btn" data-toggle="modal" data-target="#add-payment" id="gift-card-btn"><i class="fa fa-credit-card-alt"></i><br> {{trans('file.Gift Card')}}</button>
                        </div>
                        @endif
                        
                        @if(in_array("deposit",$options))
                        <div class="column-5 col-6">
                            <button style="background: #f5f5f0 !important; color:black !important; border-color:#99b3e6;" type="button" class="btn btn-sm btn-custom payment-btn" data-toggle="modal" data-target="#add-payment" id="deposit-btn"><i class="fa fa-university"></i><br> {{trans('file.Deposit')}}</button>
                        </div>
                        @endif
                        
                        @if(in_array("mobile_money",$options))
                        <div class="column-5 col-6">
                            <button style="background: #f5f5f0 !important; color:black !important; border-color:#99b3e6;" type="button" class="btn btn-sm btn-custom payment-btn" data-toggle="modal" data-target="#add-payment" id="mobile_money-btn"><i class="fa fa-credit-card-alt"></i>
                                Mobile
                                <br> 
                                Money</button>
                        </div>
                        @endif
                        
                        @if($lims_reward_point_setting_data && $lims_reward_point_setting_data->is_active && in_array("points",$options))
                        <div class="column-5 col-6">
                            <button style="background: transparent; color:black !important; border-color:#99b3e6;" type="button" class="btn btn-sm btn-custom payment-btn" data-toggle="modal" data-target="#add-payment" id="point-btn"><i class="dripicons-rocket"></i><br> {{trans('file.Points')}}</button>
                        </div>
                        @endif
                         <div class="column-5 col-6">
                            <button style="background-color: #d63031;" type="button" class="btn btn-sm btn-custom" id="cancel-btn" onclick="return confirmCancel()"><i class="fa fa-close"></i> <br>{{trans('file.Cancel')}}</button>
                        </div>
                         <div class="column-5 col-sm-12">
                            <button style="background-color: #ffc107;" type="button" class="btn btn-sm btn-custom" data-toggle="modal" data-target="#recentTransaction"><i class="dripicons-clock"></i><br> {{trans('file.Recent Transaction')}}</button>
                        </div>
                    
                </div>
            </div>
            <div class="col-md-10 col-lg-10 order-2 order-md-2 transition-all" id="main-column"  style="background-color:#f5f5f0 !important;">
                <div class="card " style="margin-top: 10px;"  style="background-color:#f5f5f0 !important;">
                    <div class="card-body" style="padding-bottom: 0px !important;">
                        {!! Form::open(['route' => 'sales.store', 'method' => 'post', 'files' => true, 'class' => 'payment-form']) !!}
                        @php
                            if($lims_pos_setting_data)
                                $keybord_active = $lims_pos_setting_data->keybord_active;
                            else
                                $keybord_active = 0;

                            
                            $customer_active = $permissionChecks['customers-add'];
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
                                    <div class="col-md-3">
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
                                   <div class="col-md-4">   <!-- You may change this to col-md-5 if needed -->
    <div class="form-group">
        @if($lims_pos_setting_data)
            <input type="hidden" name="customer_id_hidden" value="{{$lims_pos_setting_data->customer_id}}">
        @endif

        <!-- Flex container: keeps select and button in one row -->
        <div class="d-flex align-items-center">
            <select required name="customer_id" id="customer_id"
                    class="selectpicker form-control"
                    data-live-search="true"
                    title="Select customer..."
                    style="flex: 1; min-width: 0;">   <!-- flex:1 fills space; min-width:0 prevents overflow -->
                <?php
                    $deposit = [];
                    $points = [];
                ?>
                @foreach($lims_customer_list as $customer)
                    @php
                        $deposit[$customer->id] = $customer->deposit - $customer->expense;
                        $points[$customer->id] = $customer->points;
                    @endphp
                    <option value="{{$customer->id}}">{{$customer->name . ' (' . $customer->phone_number . ')'}}</option>
                @endforeach
            </select>

            @if($customer_active)
                <a href="#" data-toggle="modal" data-target="#addCustomer"
                   class="btn btn-default btn-sm ml-2">   <!-- ml-2 adds a small left margin -->
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
                                        @if(!$field->is_admin || \Auth::user()->role_id == 1)
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
                                    <div class="table-responsive transaction-list">
                                        <table id="myTable" class="table table-hover table-striped order-list table-fixed">
                                            <thead>
                                                <tr>
                                                    <th class="col-sm-3">{{trans('file.product')}}</th>
                                                    <th class="col-sm-2">{{trans('file.Batch No')}}</th>
                                                    <th class="col-sm-2">{{trans('file.Price')}}</th>
                                                    <th class="col-sm-2">{{trans('file.Quantity')}}</th>
                                                    <th class="col-sm-3">{{trans('file.Subtotal')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbody-id">
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="row" style="display: none;">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_qty" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_discount" value="{{number_format(0, $general_setting->decimal, '.', '')}}" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_tax" value="{{number_format(0, $general_setting->decimal, '.', '')}}"/>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="total_price" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="item" />
                                            <input type="hidden" name="order_tax" />
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <input type="hidden" name="grand_total" />
                                            <input type="hidden" name="used_points" />
                                            <input type="hidden" name="coupon_discount" />
                                            <input type="hidden" name="sale_status" value="1" />
                                            <input type="hidden" name="coupon_active">
                                            <input type="hidden" name="coupon_id">
                                            <input type="hidden" name="coupon_discount" />

                                            <input type="hidden" name="pos" value="1" />
                                            <input type="hidden" name="draft" value="0" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 totals" style="border-top: 2px solid #e4e6fc; padding-top: 10px; color: #13bd60;">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Items')}}</span><span id="item">0</span>
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Total')}}</span><span id="subtotal">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Discount')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#order-discount-modal"> <i class="dripicons-document-edit"></i></button></span>
                                            <span id="discount">{{ number_format(0, $general_setting->decimal ?? 2, '.', '') }}</span>

                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Coupon')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#coupon-modal"><i class="dripicons-document-edit"></i></button></span><span id="coupon-text">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Tax')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#order-tax"><i class="dripicons-document-edit"></i></button></span><span id="tax">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
                                        </div>
                                        <div class="col-sm-4">
                                            <span class="totals-title">{{trans('file.Shipping')}} <button type="button" class="btn btn-link btn-sm" data-toggle="modal" data-target="#shipping-cost-modal"><i class="dripicons-document-edit"></i></button></span><span id="shipping-cost">{{number_format(0, $general_setting->decimal, '.', '')}}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <div class="payment-amount">
                            <h2>{{trans('file.grand total')}} <span id="grand-total">{{number_format(0, $general_setting->decimal, '.', '')}}</span></h2>
                        </div>
                        <div class="payment-options"></div>
                
                        
        
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
                                                        <input type="text" name="paid_amount" class="form-control numkey"  step="any" readonly>
                                                    </div>
                                                    <div class="col-md-3 mt-1">
                                                        <label>{{trans('file.Change')}} : </label>
                                                        <p id="change" class="ml-2">{{number_format(0, $general_setting->decimal, '.', '')}}</p>
                                                    </div>
                                                    <div class="col-md-3 mt-1">
                                                        <input type="hidden" name="paid_by_id">
                                                        <label>{{ trans('file.Paid By') }}</label>
                                                        <div class="d-flex align-items-center">
                                                            <select name="paid_by_id" class="form-control selectpicker">
                                                                <option value="1">Cash</option>
                                                                <option value="2">Gift Card</option>
                                                                <option value="3">Credit Card</option>
                                                                <option value="4">Cheque</option>
                                                                <option value="5">Paypal</option>
                                                                <option value="6">Deposit</option>
                                                                <option value="7">Points</option>
                                                                <option value="8">Mobile Money</option>
                                                            </select>
                                                            <i class="fa fa-plus text-success ml-2 cursor-pointer" id="multiplePaymentBtn" title="Add Split"></i>
                                                        </div>
                                                    </div>


                                                    {{-- Container for dynamically added splits --}}
                                                    <div class="col-md-12 mt-2 ml-2" id="paymentMethodsContainer"></div>
                                                    <div class="form-group col-md-12 mt-3">
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
                        <!-- order_discount modal -->
                        <div id="order-discount-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                            <div role="document" class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">{{trans('file.Order Discount')}}</h5>
                                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6 form-group">
                                                <label>{{trans('file.Order Discount Type')}}</label>
                                                <select id="order-discount-type" name="order_discount_type_select" class="form-control">
                                                <option value="Percentage">{{trans('file.Percentage')}}</option>
                                                <option value="Flat">{{trans('file.Flat')}}</option>
                                                
                                                </select>
                                                <input type="hidden" name="order_discount_type">
                                            </div>
                                            <div class="col-md-6 form-group">
                                                <label>{{trans('file.Value')}}</label>
                                                <input type="text" name="order_discount_value" class="form-control numkey" id="order-discount-val" onkeyup='saveValue(this);'>
                                                <input type="hidden" name="order_discount" class="form-control" id="order-discount" onkeyup='saveValue(this);'>
                                            </div>
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
                                            <input type="text" id="coupon-code" class="form-control" placeholder="Type Coupon Code...">
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
                                            <input type="hidden" name="order_tax_rate">
                                            <select class="form-control" name="order_tax_rate_select" id="order-tax-rate-select">
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
                                            <input type="text" name="shipping_cost" class="form-control numkey" id="shipping-cost-val" step="any" onkeyup='saveValue(this);'>
                                        </div>
                                        <button type="button" name="shipping_cost_btn" class="btn btn-primary" data-dismiss="modal">{{trans('file.submit')}}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
            
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <!-- product list -->
            
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
                    {!! Form::open(['route' => 'customer.storeCustomer', 'method' => 'post', 'files' => true, 'id' => 'customer-form']) !!}
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
                            <input type="text" name="customer_name" required class="form-control">
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
                            <button type="button" class="btn btn-primary customer-submit-btn">{{trans('file.submit')}}</button>
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
                                    <tr>
                                      <td>{{date('d-m-Y', strtotime($sale->created_at))}}</td>
                                      <td>{{$sale->reference_no}}</td>
                                      <td>{{$sale->customer->name ?? 'N/A'}}</td>
                                      <td>{{$sale->grand_total}}</td>
                                      <td>
                                        <div class="btn-group">
                                            @if($permissionChecks['sales-edit'] ?? false)
                                            <a href="{{ route('sales.edit', $sale->id) }}" class="btn btn-success btn-sm" title="Edit"><i class="dripicons-document-edit"></i></a>&nbsp;
                                            @endif
                                            @if($permissionChecks['sales-delete'] ?? false)
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
                                    <tr>
                                      <td>{{date('d-m-Y', strtotime($draft->created_at))}}</td>
                                      <td>{{$draft->reference_no}}</td>
                                      <td>{{$draft->customer->name ?? 'N/A'}}</td>
                                      <td>{{$draft->grand_total}}</td>
                                      <td>
                                        <div class="btn-group">
                                            @if($permissionChecks['sales-edit'] ?? false)
                                            <a href="{{url('sales/'.$draft->id.'/create') }}" class="btn btn-success btn-sm" title="Edit"><i class="dripicons-document-edit"></i></a>&nbsp;
                                            @endif
                                            @if($permissionChecks['sales-delete'] ?? false)
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
    $("ul#sale #sale-pos-menu").addClass("active");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    @if(config('database.connections.saleprosaas_landlord'))
        numberOfInvoice = <?php echo json_encode($numberOfInvoice)?>;
        $.ajax({
            type: 'GET',
            async: false,
            url: '{{route("package.fetchData", $general_setting->package_id)}}',
            success: function(data) {
                if(data['number_of_invoice'] > 0 && data['number_of_invoice'] <= numberOfInvoice) {
                    localStorage.setItem("message", "You don't have permission to create another invoice as you already exceed the limit! Subscribe to another package if you wants more!");
                    location.href = "{{route('sales.index')}}";
                }
            }
        });
    @endif

    @if(session()->get('message') == 'Sale successfully added to Hold')
        localStorage.clear();
    @endif

    @if($lims_pos_setting_data)
    var public_key = <?php echo json_encode($lims_pos_setting_data->stripe_public_key) ?>;
    @endif
    var without_stock = <?php echo json_encode($general_setting->without_stock) ?>;
    var alert_product = <?php echo json_encode($alert_product) ?>;
    var currency = <?php echo json_encode($currency) ?>;
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
var wholesale_price = [];
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
var reward_point_setting = <?php echo json_encode($lims_reward_point_setting_data) ?>;

@if($lims_pos_setting_data)
var product_row_number = <?php echo json_encode($lims_pos_setting_data->product_number) ?>;
@endif
var rowindex;
var customer_group_rate;
var row_product_price;
var pos;
var keyboard_active = <?php echo json_encode($keybord_active); ?>;
var role_id = <?php echo json_encode(\Auth::user()->role_id) ?>;
var warehouse_id = <?php echo json_encode(\Auth::user()->warehouse_id) ?>;
var biller_id = <?php echo json_encode(\Auth::user()->biller_id) ?>;
var coupon_list = <?php echo json_encode($lims_coupon_list) ?>;
var currency = <?php echo json_encode($currency) ?>;
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

var localStorageQty = [];
var localStorageProductId = [];
var localStorageProductDiscount = [];
var localStorageTaxRate = [];
var localStorageNetUnitPrice = [];
var localStorageTaxValue = [];
var localStorageTaxName = [];
var localStorageTaxMethod = [];
var localStorageSubTotalUnit = [];
var localStorageSubTotal = [];
var localStorageProductCode = [];
var localStorageSaleUnit = [];
var localStorageTempUnitName = [];
var localStorageSaleUnitOperator = [];
var localStorageSaleUnitOperationValue = [];

$("#reference-no").val(getSavedValue("reference-no"));
$("#order-discount").val(getSavedValue("order-discount"));
$("#order-discount-val").val(getSavedValue("order-discount-val"));
$("#order-discount-type").val(getSavedValue("order-discount-type"));
$("#order-tax-rate-select").val(getSavedValue("order-tax-rate-select"));


$("#shipping-cost-val").val(getSavedValue("shipping-cost-val"));

if(localStorage.getItem("tbody-id")) {
  $("#tbody-id").html(localStorage.getItem("tbody-id"));
}

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

if(getSavedValue("localStorageQty")) {
  localStorageQty = getSavedValue("localStorageQty").split(",");
  localStorageProductDiscount = getSavedValue("localStorageProductDiscount").split(",");
  localStorageTaxRate = getSavedValue("localStorageTaxRate").split(",");
  localStorageNetUnitPrice = getSavedValue("localStorageNetUnitPrice").split(",");
  localStorageTaxValue = getSavedValue("localStorageTaxValue").split(",");
  localStorageTaxName = getSavedValue("localStorageTaxName").split(",");
  localStorageTaxMethod = getSavedValue("localStorageTaxMethod").split(",");
  localStorageSubTotalUnit = getSavedValue("localStorageSubTotalUnit").split(",");
  localStorageSubTotal = getSavedValue("localStorageSubTotal").split(",");
  localStorageProductId = getSavedValue("localStorageProductId").split(",");
  localStorageProductCode = getSavedValue("localStorageProductCode").split(",");
  localStorageSaleUnit = getSavedValue("localStorageSaleUnit").split(",");
  localStorageTempUnitName = getSavedValue("localStorageTempUnitName").split(",,");
  localStorageSaleUnitOperator = getSavedValue("localStorageSaleUnitOperator").split(",,");
  localStorageSaleUnitOperationValue = getSavedValue("localStorageSaleUnitOperationValue").split(",,");
  /*localStorageQty.pop();
  localStorage.setItem("localStorageQty", localStorageQty);*/
  for(var i = 0; i < localStorageQty.length; i++) {
    
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ') .qty').val(localStorageQty[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.discount-value').val(localStorageProductDiscount[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.tax-rate').val(localStorageTaxRate[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.net_unit_price').val(localStorageNetUnitPrice[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.tax-value').val(localStorageTaxValue[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.tax-name').val(localStorageTaxName[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.tax-method').val(localStorageTaxMethod[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.product-price').text(localStorageSubTotalUnit[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.sub-total').text(localStorageSubTotal[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.subtotal-value').val(localStorageSubTotal[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.product-id').val(localStorageProductId[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.product-code').val(localStorageProductCode[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.sale-unit').val(localStorageSaleUnit[i]);
    if(i==0) {
      localStorageTempUnitName[i] += ',';
      localStorageSaleUnitOperator[i] += ',';
      localStorageSaleUnitOperationValue[i] += ',';
    }
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.sale-unit-operator').val(localStorageSaleUnitOperator[i]);
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.sale-unit-operation-value').val(localStorageSaleUnitOperationValue[i]);

    product_price.push(parseFloat($('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.product_price').val()));
    var quantity = parseFloat($('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.qty').val());
    product_discount.push(parseFloat(localStorageProductDiscount[i] / localStorageQty[i]).toFixed({{$general_setting->decimal}}));
    tax_rate.push(parseFloat($('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.tax-rate').val()));
    tax_name.push($('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.tax-name').val());
    tax_method.push($('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.tax-method').val());
    temp_unit_name = $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.sale-unit').val().split(',');
    unit_name.push(localStorageTempUnitName[i]);
    unit_operator.push($('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.sale-unit-operator').val());
    unit_operation_value.push($('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.sale-unit-operation-value').val());
    $('table.order-list tbody tr:nth-child(' + (i + 1) + ')').find('.sale-unit').val(temp_unit_name[0]);
    calculateTotal();
    //calculateRowProductData(localStorageQty[i]);
  }
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


$('.customer-submit-btn').on("click", function(e) {
    e.preventDefault();
    const toastMagic = new ToastMagic();

    $.ajax({
        type: 'POST',
        url: '{{ route('customer.storeCustomer') }}',
        data: $("#customer-form").serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            // Success message
            toastMagic.success('Customer added successfully!');

            // Append new customer to select
            let key = response['id'];
            let value = response['name'] + ' [' + response['phone_number'] + ']';
            $('select[name="customer_id"]').append('<option value="' + key + '">' + value + '</option>');
            $('select[name="customer_id"]').val(key);
            $('.selectpicker').selectpicker('refresh');

            // Hide modal
            $("#addCustomer").modal('hide');

            // Optionally reset the form
            // $('#customer-form')[0].reset();
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                // Validation error
                const errors = xhr.responseJSON.errors;
                $.each(errors, function(field, messages) {
                    toastMagic.error(messages[0]); // Show the first validation message per field
                });
            } else {
                // Other errors (e.g. 500)
                toastMagic.error('An unexpected error occurred. Please try again.');
                console.error('Error details:', xhr.responseText);
            }
        }
    });
});


  $("li#notification-icon").on("click", function (argument) {
      $.get('notifications/mark-as-read', function(data) {
          $("span.notification-number").text(alert_product);
      });
  });

  $("#register-details-btn").on("click", function (e) {
      e.preventDefault();
      $.ajax({
          url: 'cash-register/showDetails/'+warehouse_id,
          type: "GET",
          success:function(data) {
              $('#register-details-modal #cash_in_hand').text(data['cash_in_hand']);
              $('#register-details-modal #total_sale_amount').text(data['total_sale_amount']);
              $('#register-details-modal #total_payment').text(data['total_payment']);
              $('#register-details-modal #cash_payment').text(data['cash_payment']);
              $('#register-details-modal #credit_card_payment').text(data['credit_card_payment']);
              $('#register-details-modal #cheque_payment').text(data['cheque_payment']);
              $('#register-details-modal #gift_card_payment').text(data['gift_card_payment']);
              $('#register-details-modal #deposit_payment').text(data['deposit_payment']);
              $('#register-details-modal #paypal_payment').text(data['paypal_payment']);
              $('#register-details-modal #total_sale_return').text(data['total_sale_return']);
              $('#register-details-modal #total_expense').text(data['total_expense']);
              $('#register-details-modal #total_cash').text(data['total_cash']);
              $('#register-details-modal input[name=cash_register_id]').val(data['id']);
          }
      });
      $('#register-details-modal').modal('show');
  });

  $("#today-sale-btn").on("click", function (e) {
      e.preventDefault();
      $.ajax({
          url: 'sales/today-sale/',
          type: "GET",
          success:function(data) {
              $('#today-sale-modal .total_sale_amount').text(data['total_sale_amount']);
              $('#today-sale-modal .total_payment').text(data['total_payment']);
              $('#today-sale-modal .cash_payment').text(data['cash_payment']);
              $('#today-sale-modal .credit_card_payment').text(data['credit_card_payment']);
              $('#today-sale-modal .cheque_payment').text(data['cheque_payment']);
              $('#today-sale-modal .gift_card_payment').text(data['gift_card_payment']);
              $('#today-sale-modal .deposit_payment').text(data['deposit_payment']);
              $('#today-sale-modal .paypal_payment').text(data['paypal_payment']);
              $('#today-sale-modal .total_sale_return').text(data['total_sale_return']);
              $('#today-sale-modal .total_expense').text(data['total_expense']);
              $('#today-sale-modal .total_cash').text(data['total_cash']);
            //   changes by yogesh
              $('#today-sale-modal .mobile_payment').text(data['mobile_payment']);
              //   changes end by yogesh
          }
      });
      $('#today-sale-modal').modal('show');
  });

  $("#today-profit-btn").on("click", function (e) {
      e.preventDefault();
      calculateTodayProfit(0);
  });

  $("#today-profit-modal select[name=warehouseId]").on("change", function() {
      calculateTodayProfit($(this).val());
  });

  function calculateTodayProfit(warehouse_id) {
      $.ajax({
            url: 'sales/today-profit/' + warehouse_id,
            type: "GET",
            success:function(data) {
                $('#today-profit-modal .product_revenue').text(data['product_revenue']);
                $('#today-profit-modal .product_cost').text(data['product_cost']);
                $('#today-profit-modal .expense_amount').text(data['expense_amount']);
                $('#today-profit-modal .profit').text(data['profit']);
            }
        });
      $('#today-profit-modal').modal('show');
  }

if(role_id > 2){
    $('#biller_id').addClass('d-none');
    $('#warehouse_id').addClass('d-none');
    $('select[name=warehouse_id]').val(warehouse_id);
    $('select[name=biller_id]').val(biller_id);
    isCashRegisterAvailable(warehouse_id);
}
else {
    // Try to get warehouse from localStorage first, then from pos_setting default, then from user
    if(getSavedValue("warehouse_id")){
      warehouse_id = getSavedValue("warehouse_id");
    }
    else if($("input[name='warehouse_id_hidden']").val()){
      warehouse_id = $("input[name='warehouse_id_hidden']").val();
    }

    // Try to get biller from localStorage first, then from pos_setting default, then from user
    if(getSavedValue("biller_id")){
      biller_id = getSavedValue("biller_id");
    }
    else if($("input[name='biller_id_hidden']").val()){
      biller_id = $("input[name='biller_id_hidden']").val();
    }
    
    $('select[name=warehouse_id]').val(warehouse_id);
    $('select[name=biller_id]').val(biller_id);
}

  // Try to get customer from localStorage first, then from pos_setting default
  if(getSavedValue("customer_id")) {
    $('select[name=customer_id]').val(getSavedValue("customer_id"));
  }
  else if($("input[name='customer_id_hidden']").val()){
    $('select[name=customer_id]').val($("input[name='customer_id_hidden']").val());
  }

  // Auto-set defaults: if only one warehouse/biller/customer, select it
  // If first option is a placeholder, use the second option
  function setDefaultSelectValues() {
    // Get pos_setting defaults if available
    var posSettingWarehouse = $("input[name='warehouse_id_hidden']").val();
    var posSettingBiller = $("input[name='biller_id_hidden']").val();
    var posSettingCustomer = $("input[name='customer_id_hidden']").val();

    // Set warehouse default - prefer pos_setting, then first available
    var warehouseOptions = $('select[name=warehouse_id] option');
    if(posSettingWarehouse && $('select[name=warehouse_id] option[value="' + posSettingWarehouse + '"]').length) {
      $('select[name=warehouse_id]').val(posSettingWarehouse);
    }
    else if(warehouseOptions.length > 0) {
      var firstOption = warehouseOptions.eq(0);
      if(firstOption.val() === '' || firstOption.val() === null) {
        if(warehouseOptions.length > 1) {
          $('select[name=warehouse_id]').val(warehouseOptions.eq(1).val());
        }
      } else {
        $('select[name=warehouse_id]').val(firstOption.val());
      }
    }

    // Set biller default - prefer pos_setting, then first available
    var billerOptions = $('select[name=biller_id] option');
    if(posSettingBiller && $('select[name=biller_id] option[value="' + posSettingBiller + '"]').length) {
      $('select[name=biller_id]').val(posSettingBiller);
    }
    else if(billerOptions.length === 1 || (billerOptions.length === 2 && $('select[name=biller_id] option:first').val() === '')) {
      var firstRealOption = billerOptions.length === 1 ? billerOptions.eq(0) : billerOptions.eq(1);
      $('select[name=biller_id]').val(firstRealOption.val());
    }

    // Set customer default - prefer pos_setting, then first available
    var customerOptions = $('select[name=customer_id] option');
    if(posSettingCustomer && $('select[name=customer_id] option[value="' + posSettingCustomer + '"]').length) {
      $('select[name=customer_id]').val(posSettingCustomer);
    }
    else if(customerOptions.length > 0) {
      var firstOption = customerOptions.eq(0);
      if(firstOption.val() === '' || firstOption.val() === null) {
        if(customerOptions.length > 1) {
          $('select[name=customer_id]').val(customerOptions.eq(1).val());
        }
      } else {
        $('select[name=customer_id]').val(firstOption.val());
      }
    }
  }

  setDefaultSelectValues();

$('.selectpicker').selectpicker('refresh');

var id = $("#customer_id").val();
$.get('sales/getcustomergroup/' + id, function(data) {
    customer_group_rate = (data / 100);
});

// Use DOM value or fallback to warehouse_id (selectpicker may not have updated the select yet)
var id = $("#warehouse_id").val() || $('select[name="warehouse_id"]').val() || warehouse_id;
if (id) {
    $.get('sales/getproduct/' + id, function(data) {
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
                     'Code: ' + product_code[index] +
                    ' | Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index] +
                    ' | Embeded: ' + is_embeded[index] 
               );
            else
                lims_product_array.push(
                     'Code: ' + product_code[index] +
                    ' | Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index] 
               );
        });
    });
}
if (id) {
    isCashRegisterAvailable(id);
}

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

// $('#category-filter').on('click', function(e){
//     e.stopPropagation();
//     $('.filter-window').show('slide', {direction: 'right'}, 'fast');
//     $('.category').show();
//     $('.brand').hide();
// });

// $('.category-img').on('click', function(){
//     var category_id = $(this).data('category');
//     var brand_id = 0;

//     $(".table-container").children().remove();
//     $.get('sales/getproduct/' + category_id + '/' + brand_id, function(data) {
//         populateProduct(data);
//     });
// });

// $('#brand-filter').on('click', function(e){
//     e.stopPropagation();
//     $('.filter-window').show('slide', {direction: 'right'}, 'fast');
//     $('.brand').show();
//     $('.category').hide();
// });

// $('.brand-img').on('click', function(){
//     var brand_id = $(this).data('brand');
//     var category_id = 0;

//     $(".table-container").children().remove();
//     $.get('sales/getproduct/' + category_id + '/' + brand_id, function(data) {
//         populateProduct(data);
//     });
// });

// $('#featured-filter').on('click', function(){
//     $(".table-container").children().remove();
//     $.get('sales/getfeatured', function(data) {
//         populateProduct(data);
//     });
// });

// function populateProduct(data) {
//     var tableData = '<table id="product-table" class="table no-shadow product-list"> <thead class="d-none"> <tr> <th></th> <th></th> <th></th> <th></th> <th></th> </tr></thead> <tbody><tr>';

//     if (Object.keys(data).length != 0) {
//         $.each(data['name'], function(index) {
//             var product_info = data['code'][index]+' (' + data['name'][index] + ')';
            
//             // Determine the correct image path
//             var imagePath = getProductImagePath(data['image'][index]);
            
//             if(index % 5 == 0 && index != 0)
//                 tableData += '</tr><tr><td class="product-img sound-btn" title="'+data['name'][index]+'" data-product = "'+product_info+'"><img src="' + imagePath + '" width="100%" /><p>'+data['name'][index]+'</p><span>'+data['code'][index]+'</span></td>';
//             else
//                 tableData += '<td class="product-img sound-btn" title="'+data['name'][index]+'" data-product = "'+product_info+'"><img src="' + imagePath + '" width="100%" /><p>'+data['name'][index]+'</p><span>'+data['code'][index]+'</span></td>';
//         });

//         if(data['name'].length % 5){
//             var number = 5 - (data['name'].length % 5);
//             while(number > 0)
//             {
//                 tableData += '<td style="border:none;"></td>';
//                 number--;
//             }
//         }

//         tableData += '</tr></tbody></table>';
//         $(".table-container").html(tableData);
//         $('#product-table').DataTable( {
//           "order": [],
//           'pageLength': product_row_number,
//            'language': {
//               'paginate': {
//                   'previous': '<i class="fa fa-angle-left"></i>',
//                   'next': '<i class="fa fa-angle-right"></i>'
//               }
//           },
//           dom: 'tp'
//         });
//         $('table.product-list').hide();
//         $('table.product-list').show(500);
//     }
//     else{
//         tableData += '<td class="text-center">No data avaialable</td></tr></tbody></table>'
//         $(".table-container").html(tableData);
//     }
// }

/**
 * Helper function to determine the correct image path
 * Priority: small > medium > product > default fallback
 */
// function getProductImagePath(imageName) {
//     if (!imageName) {
//         return 'images/product/zummXD2dvAtI.png'; // Default fallback
//     }
    
//     // Try small folder first
//     return 'images/product/small/' + imageName;
//     // Note: In a real implementation, you'd want to check if the file exists
//     // For now, we'll assume small exists, or you can use the AJAX approach below
// }

/**
 * Alternative: Async approach using AJAX to check file existence
 * Uncomment if you want to check file existence before displaying
 */
// function getProductImagePathAsync(imageName, callback) {
//     if (!imageName) {
//         callback('images/product/zummXD2dvAtI.png');
//         return;
//     }
    
//     var paths = [
//         'images/product/small/' + imageName,
//         'images/product/medium/' + imageName,
//         'images/product/' + imageName,
//         'images/product/zummXD2dvAtI.png' // fallback
//     ];
    
//     function checkPath(index) {
//         if (index >= paths.length) {
//             callback(paths[paths.length - 1]); // Use fallback
//             return;
//         }
        
//         var img = new Image();
//         img.onload = function() {
//             callback(paths[index]); // Image found
//         };
//         img.onerror = function() {
//             checkPath(index + 1); // Try next path
//         };
//         img.src = paths[index];
//     }
    
//     checkPath(0);
// }

$('select[name="customer_id"]').on('change', function() {
    saveValue(this);
    var id = $(this).val();
    $.get('sales/getcustomergroup/' + id, function(data) {
        customer_group_rate = (data / 100);
    });
});

$('select[name="biller_id"]').on('change', function() {
    saveValue(this);
});

$('select[name="warehouse_id"]').on('change', function() {
    saveValue(this);
    warehouse_id = $(this).val();
    $.get('sales/getproduct/' + warehouse_id, function(data) {
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
                    'Code: ' + product_code[index] +
                    ' | Name: ' + product_name[index] +
                    ' | Price: ' + product_warehouse_price[index] +
                    ' | Qty: ' + product_qty[index] +
                    ' | Shelf: ' + product_shelf[index] +
                    ' | Embeded: ' + is_embeded[index] 
                );
            else
                lims_product_array.push(
                        'Code: ' + product_code[index] +
                        ' | Name: ' + product_name[index] +
                        ' | Price: ' + product_warehouse_price[index] +
                        ' | Qty: ' + product_qty[index] +
                        ' | Shelf: ' + product_shelf[index] 

                );
        });
    });

    isCashRegisterAvailable(warehouse_id);
});

var lims_productcodeSearch = $('#lims_productcodeSearch');

lims_productcodeSearch.autocomplete({
    minLength: 1,
    source: function(request, response) {
        var term = (request.term || '').trim();
        if (!term) {
            response([]);
            return;
        }
        var matcher = new RegExp(".*" + $.ui.autocomplete.escapeRegex(term) + ".*", "i");
        response($.grep(lims_product_array, function(item) {
            return matcher.test(item);
        }));
    },
    response: function(event, ui) {
        if (ui.content.length == 1) {
            var data = ui.content[0].value;
            $(this).autocomplete( "close" );
            productSearch(data);
        } 
        else if(ui.content.length == 0 && $('#lims_productcodeSearch').val().length == 13) {
          productSearch($('#lims_productcodeSearch').val()+'|'+1);
        }
    },
    select: function(event, ui) {
        var data = ui.item.value;
        ui.item.value = '';
        productSearch(data);
    },
});

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

   $("select[name=price_option]").on("change", function () {
        console.log($(this).val());
        $("#editModal input[name=edit_unit_price]").val($(this).val());
    });

$("#myTable").on("change", ".batch-no", function () {
    rowindex = $(this).closest('tr').index();
    var product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-id').val();
    var warehouse_id = $('#warehouse_id').val();
    $.get('check-batch-availability/' + product_id + '/' + $(this).val() + '/' + warehouse_id, function(data) {
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
    wholesale_price.splice(rowindex, 1);
    product_discount.splice(rowindex, 1);
    tax_rate.splice(rowindex, 1);
    tax_name.splice(rowindex, 1);
    tax_method.splice(rowindex, 1);
    unit_name.splice(rowindex, 1);
    unit_operator.splice(rowindex, 1);
    unit_operation_value.splice(rowindex, 1);

    localStorageProductId.splice(rowindex, 1);
    localStorageQty.splice(rowindex, 1);
    localStorageSaleUnit.splice(rowindex, 1);
    localStorageProductDiscount.splice(rowindex, 1);
    localStorageTaxRate.splice(rowindex, 1);
    localStorageNetUnitPrice.splice(rowindex, 1);
    localStorageTaxValue.splice(rowindex, 1);
    localStorageSubTotalUnit.splice(rowindex, 1);
    localStorageSubTotal.splice(rowindex, 1);
    localStorageProductCode.splice(rowindex, 1);

    localStorageTaxName.splice(rowindex, 1);
    localStorageTaxMethod.splice(rowindex, 1);
    localStorageTempUnitName.splice(rowindex, 1);
    localStorageSaleUnitOperator.splice(rowindex, 1);
    localStorageSaleUnitOperationValue.splice(rowindex, 1);

    localStorage.setItem("localStorageProductId", localStorageProductId);
    localStorage.setItem("localStorageQty", localStorageQty);
    localStorage.setItem("localStorageSaleUnit", localStorageSaleUnit);
    localStorage.setItem("localStorageProductCode", localStorageProductCode);
    localStorage.setItem("localStorageProductDiscount", localStorageProductDiscount);
    localStorage.setItem("localStorageTaxRate", localStorageTaxRate);
    localStorage.setItem("localStorageTaxName", localStorageTaxName);
    localStorage.setItem("localStorageTaxMethod", localStorageTaxMethod);
    localStorage.setItem("localStorageTempUnitName", localStorageTempUnitName);
    localStorage.setItem("localStorageSaleUnitOperator", localStorageSaleUnitOperator);
    localStorage.setItem("localStorageSaleUnitOperationValue", localStorageSaleUnitOperationValue);
    localStorage.setItem("localStorageNetUnitPrice", localStorageNetUnitPrice);
    localStorage.setItem("localStorageTaxValue", localStorageTaxValue);
    localStorage.setItem("localStorageSubTotalUnit", localStorageSubTotalUnit);
    localStorage.setItem("localStorageSubTotal", localStorageSubTotal);

    $(this).closest("tr").remove();
    localStorage.setItem("tbody-id", $("table.order-list tbody").html());
    calculateTotal();
});

//Edit product
$("table.order-list").on("click", ".edit-product", function() {
    rowindex = $(this).closest('tr').index();
    edit();
});
 
//Update product
$('button[name="update_btn"]').on("click", function() {
    // Always coerce to numbers safely
    let edit_discount = parseFloat($('#editModal input[name="edit_discount"]').val()) || 0;
    let edit_qty = parseFloat($('#editModal input[name="edit_qty"]').val()) || 0;
    let edit_unit_price = parseFloat($('#editModal input[name="edit_unit_price"]').val()) || 0;

    let new_unit_price = edit_unit_price - edit_discount;
    console.log("New unit price:", new_unit_price);

    if (edit_discount > edit_unit_price) {
        alert('Invalid Discount Input!');
        return;
    }

    if (edit_qty <= 0) {
        $('input[name="edit_qty"]').val(1);
        edit_qty = 1;
        alert("Quantity can't be less than 0");
    }

    var tax_rate_all = <?php echo json_encode($tax_rate_all) ?>;
    let selectedTaxId = $('select[name="edit_tax_rate"]').val();
    let currentTaxRate = parseFloat(tax_rate_all[selectedTaxId]) || 0;

    tax_rate[rowindex] = localStorageTaxRate[rowindex] = currentTaxRate;
    tax_name[rowindex] = localStorageTaxName[rowindex] = $('select[name="edit_tax_rate"] option:selected').text();

    // ✅ Always store as numeric values
    product_discount[rowindex] = edit_discount;
    product_price[rowindex] = new_unit_price;

    console.log({
        edit_qty,
        edit_unit_price,
        edit_discount,
        new_unit_price,
        row_product_price: product_price[rowindex]
    });

    // Call checkDiscount
    if (edit_discount > 0) {
        checkDiscount(edit_qty, true, edit_discount);
    } else {
        checkDiscount(edit_qty, false);
    }

    // ✅ After discount changes, recalc total & tax safely
    calculateTotal();
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
});

$("#draft-btn").on("click",function(){
    console.log('draft');
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
      console.log('submit');
        $('.payment-form').submit();
});

$("#submit-btn").on("click", function() {
    $('.payment-form').submit();
});

document.addEventListener("DOMContentLoaded", function () {
    // ✅ Function: show or hide global .mobile_money_fields depending on selections
    function toggleGlobalMobileMoneyFields() {
        // Check if any select currently has value '8'
        const hasMobileMoney = Array.from(document.querySelectorAll('select[name="paid_by_id_select[]"]'))
            .some(select => select.value === '8');
        console.log('Has Mobile Money:', hasMobileMoney);

        const fields = document.querySelectorAll('.mobile_money_fields');
        fields.forEach(field => {
            field.style.display = hasMobileMoney ? 'block' : 'none';
        });
    }

    // ✅ Listen to change event on all current & future selects
    $(document).on("change", 'select[name="paid_by_id_select[]"].payment-method-select', function () {
        toggleGlobalMobileMoneyFields();
    });

    // ✅ Initialize state on load
    toggleGlobalMobileMoneyFields();

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
                        <select name="paid_by_id_select[]" class="form-control selectpicker payment-method-select">
                            ${selectOptions}
                        </select>
                        <button type="button" class="btn btn-danger ml-2 remove-payment"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `;

        $("#paymentMethodsContainer").append(paymentRow);
        $('.selectpicker').selectpicker('refresh');
        toggleGlobalMobileMoneyFields();
    });
    
});
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

$("#gift-card-btn").on("click",function() {
    $('select[name="paid_by_id"]').val(2);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    $('.mobile_money').hide();
    
    giftCard();
});

$("#credit-card-btn").on("click",function() {
    $('select[name="paid_by_id"]').val(3);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    creditCard();
});

$("#cheque-btn").on("click",function() {
    $('select[name="paid_by_id_select"]').val(4);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    cheque();
});

$("#cash-btn").on("click",function() {
    $('select[name="paid_by_id"]').val(1);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').show();
    hide();
});

$("#paypal-btn").on("click",function() {
    $('select[name="paid_by_id"]').val(5);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    hide();
});

$("#deposit-btn").on("click",function() {
    $('select[name="paid_by_id"]').val(6);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    hide();
    deposits();
});

$("#point-btn").on("click",function() {
    $('select[name="paid_by_id"]').val(7);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    hide();
    pointCalculation();
});


$("#mobile_money-btn").on("click",function() {
    $('select[name="paid_by_id"]').val(8);
    $('.selectpicker').selectpicker('refresh');
    $('div.qc').hide();
    hide();
    mobile_money();
});


$('select[name="paid_by_id"]').on("change", function() {
    var id = $(this).val();
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
    else if (id == 8) {
        $('div.qc').hide();
        mobile_money();
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
        $("#paymentMethodsContainer").empty();
    refreshPaymentMethodOptions();
});

$('#add-payment select[name="gift_card_id_select"]').on("change", function() {
    var balance = gift_card_amount[$(this).val()] - gift_card_expense[$(this).val()];
    $('#add-payment input[name="gift_card_id"]').val($(this).val());
    if($('input[name="paid_amount"]').val() > balance){
        alert('Amount exceeds card balance! Gift Card balance: '+ balance);
    }
});

$('#add-payment input[name="paying_amount"]').on("input", function() {
    change($(this).val(), $('input[name="paid_amount"]').val());
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
    var id = $('select[name="paid_by_id"]').val();
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
    console.log('Paying Amount:', payingAmount);
    let totalSplit = getTotalSplitAmount();
    let mainPaid = parseFloat($('input[name="paid_amount"]').val()) || 0;
    console.log('Main Paid:', mainPaid);
    let totalPaid = totalSplit + payingAmount;
    console.log('Total Paid (main + split):', totalPaid);

    if (totalPaid > mainPaid) {
        alert('Total paid (main + split) cannot be greater than paying amount');
        $(this).val('');
        totalSplit = getTotalSplitAmount(); // recalculate after clearing this input
        totalPaid = totalSplit + payingAmount;
    }

    change(mainPaid, totalPaid);
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
    $("table.order-list tbody .qty").each(function(index) {
        if ($(this).val() == '') {
            alert('One of products has no quantity!');
            e.preventDefault();
        }
    });
    var rownumber = $('table.order-list tbody tr:last').index();
    if (rownumber < 0) {
        alert("Please insert product to order table!")
        e.preventDefault();
    }
    else if(parseFloat($('input[name="total_qty"]').val()) <= 0) {
        alert('Product quantity is 0');
        e.preventDefault();
    }
    // else if( parseFloat( $('input[name="paying_amount"]').val() ) < parseFloat( $('input[name="paid_amount"]').val() ) ){
    //     alert('Paying amount cannot be bigger than recieved amount');
    //     e.preventDefault();
    // }
    else {
        $("#submit-button").prop('disabled', true);
    }
    $('input[name="paid_by_id"]').val($('select[name="paid_by_id"]').val());
    $('input[name="order_tax_rate"]').val($('select[name="order_tax_rate_select"]').val());

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

function productSearch(data) {
    var code_match = data.match(/Code:\s*([^|]+)/);
    console.log(code_match);
    var product_info = data.split(" ");
     var product_code = code_match ? code_match[1].trim() : data.split(" ")[0];
     
    if (product_code.length < 1) {
        alert('Please insert product code!');
        return;
    }
    var pre_qty = 0;
    $(".product-code").each(function(i) {
        if ($(this).val() == product_code) {
            rowindex = i;
            pre_qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
        }
    });
    var ajaxData = product_code + '?' + $('#customer_id').val() + '?' + (parseFloat(pre_qty) + 1);
    console.log(ajaxData);
    //  var ajaxData = 'Code: ' + product_code + '?' + $('#customer_id').val() + '?' + (parseFloat(pre_qty) + 1);
    // data += '?'+$('#customer_id').val()+'?'+(parseFloat(pre_qty) + 1);
    $.ajax({
        type: 'GET',
        async: false,
        url: 'sales/lims_product_search',
        data: {
            data: ajaxData
        },
        success: function(data) {
            var flag = 1;
            if (pre_qty > 0) {
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
                localStorage.setItem("tbody-id", $("table.order-list tbody").html());
            }
            $("input[name='product_code_name']").val('');
            if(flag){
                addNewProduct(data);
            }
            else if(data[18] != 'null' && data[18] != '') {
                var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();
                if(imeiNumbers)
                    imeiNumbers += ','+data[18];
                else
                    imeiNumbers = data[18];
                $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val(imeiNumbers);
            }
        }
    });
}

function addNewProduct(data){
    var newRow = $('<tr id='+ data[1] +'>');
    var cols = '';
    temp_unit_name = (data[6]).split(',');
    pos = product_code.indexOf(data[1]);
    cols += '<td class="col-sm-3 product-title"><strong class="edit-product btn btn-link" data-toggle="modal" data-target="#editModal"><span style="margin-left: -19px; white-space: break-spaces;"><strong>' + data[0] + ' <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg></strong><br><span>' + data[1]+ '</span>' + '<p>In Stock: <span class="in-stock"></span></p></td>';
    // if(data[12]) {
    //     cols += '<td class="col-sm-2"><input type="text" class="form-control batch-no" value="'+batch_no[pos]+'" required/> <input type="hidden" class="product-batch-id" name="product_batch_id[]" value="'+product_batch_id[pos]+'"/> </td>';
    // }
    // else {
    //     cols += '<td class="col-sm-2"><input type="text" class="form-control batch-no" disabled/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/> </td>';
    // }
    cols += '<td class="col-sm-2"><input type="text" class="form-control batch-no" disabled/> <input type="hidden" class="product-batch-id" name="product_batch_id[]"/> </td>';

    cols += '<td class="col-sm-2 product-price"></td>';
    cols += '<td class="col-sm-2"><div class="input-group"><span class="input-group-btn"><button type="button" class="btn btn-default minus"><span class="dripicons-minus"></span></button></span><input type="text" name="qty[]" class="form-control qty numkey input-number" step="any" value="'+data[15]+'" required><span class="input-group-btn"><button type="button" class="btn btn-default plus"><span class="dripicons-plus"></span></button></span></div></td>';
    cols += '<td class="col-sm-2 sub-total"></td>';
    cols += '<td class="col-sm-1"><button type="button" class="ibtnDel btn btn-danger btn-sm"><i class="dripicons-cross"></i></button></td>';
    cols += '<input type="hidden" class="product-code" name="product_code[]" value="' + data[1] + '"/>';
    cols += '<input type="hidden" class="product-id" name="product_id[]" value="' + data[9] + '"/>';
    cols += '<input type="hidden" class="product_price" />';
    cols += '<input type="hidden" class="sale-unit" name="sale_unit[]" value="' + temp_unit_name[0] + '"/>';
    cols += '<input type="hidden" class="net_unit_price" name="net_unit_price[]" />';
    cols += '<input type="hidden" class="discount-value" name="discount[]" />';
    cols += '<input type="hidden" class="tax-rate" name="tax_rate[]" value="' + data[3] + '"/>';
    cols += '<input type="hidden" class="tax-value" name="tax[]" />';
    cols += '<input type="hidden" class="tax-name" value="'+data[4]+'" />';
    cols += '<input type="hidden" class="tax-method" value="'+data[5]+'" />';
    cols += '<input type="hidden" class="sale-unit-operator" value="'+data[7]+'" />';
    cols += '<input type="hidden" class="sale-unit-operation-value" value="'+data[8]+'" />';
    cols += '<input type="hidden" class="subtotal-value" name="subtotal[]" />';
    cols += '<input type="hidden" class="imei-number" name="imei_number[]" />';

    newRow.append(cols);
    if(keyboard_active==1) {
        $("table.order-list tbody").prepend(newRow).find('.qty').keyboard({usePreview: false, layout: 'custom', display: { 'accept'  : '&#10004;', 'cancel'  : '&#10006;' }, customLayout : {
          'normal' : ['1 2 3', '4 5 6', '7 8 9','0 {dec} {bksp}','{clear} {cancel} {accept}']}, restrictInput : true, preventPaste : true, autoAccept : true, css: { container: 'center-block dropdown-menu', buttonDefault: 'btn btn-default', buttonHover: 'btn-primary',buttonAction: 'active', buttonDisabled: 'disabled'},});
    }
    else
        $("table.order-list tbody").prepend(newRow);

    rowindex = newRow.index();

    if(!data[11] && product_warehouse_price[pos]) {
        product_price.splice(rowindex, 0, parseFloat(product_warehouse_price[pos] * currency['exchange_rate']) + parseFloat(product_warehouse_price[pos] * currency['exchange_rate'] * customer_group_rate));
    }
    else {
        product_price.splice(rowindex, 0, parseFloat(data[2] * currency['exchange_rate']) + parseFloat(data[2] * currency['exchange_rate'] * customer_group_rate));
    }

    if(data[16])
        wholesale_price.splice(rowindex, 0, parseFloat(data[16] * currency['exchange_rate']) + parseFloat(data[16] * currency['exchange_rate'] * customer_group_rate));
    else
        wholesale_price.splice(rowindex, 0, '{{number_format(0, $general_setting->decimal, '.', '')}}');

    //cost.splice(rowindex, 0, parseFloat(data[17] * currency['exchange_rate']));
    product_discount.splice(rowindex, 0, '{{number_format(0, $general_setting->decimal, '.', '')}}');
    tax_rate.splice(rowindex, 0, parseFloat(data[3]));
    tax_name.splice(rowindex, 0, data[4]);
    tax_method.splice(rowindex, 0, data[5]);
    unit_name.splice(rowindex, 0, data[6]);
    unit_operator.splice(rowindex, 0, data[7]);
    unit_operation_value.splice(rowindex, 0, data[8]);
    is_imei.splice(rowindex, 0, data[13]);
    is_variant.splice(rowindex, 0, data[14]);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product_price').val(product_price[rowindex]);
    localStorageQty.splice(rowindex, 0, data[15]);
    localStorageProductId.splice(rowindex, 0, data[9]);
    localStorageProductCode.splice(rowindex, 0, data[1]);
    localStorageSaleUnit.splice(rowindex, 0, temp_unit_name[0]);
    localStorageProductDiscount.splice(rowindex, 0, product_discount[rowindex]);
    localStorageTaxRate.splice(rowindex, 0, tax_rate[rowindex].toFixed({{$general_setting->decimal}}));
    localStorageTaxName.splice(rowindex, 0, data[4]);
    localStorageTaxMethod.splice(rowindex, 0, data[5]);
    localStorageTempUnitName.splice(rowindex, 0, data[6]);
    localStorageSaleUnitOperator.splice(rowindex, 0, data[7]);
    localStorageSaleUnitOperationValue.splice(rowindex, 0, data[8]);
    //put some dummy value
    localStorageNetUnitPrice.splice(rowindex, 0, '{{number_format(0, $general_setting->decimal, '.', '')}}');
    localStorageTaxValue.splice(rowindex, 0, '{{number_format(0, $general_setting->decimal, '.', '')}}');
    localStorageSubTotalUnit.splice(rowindex, 0, '{{number_format(0, $general_setting->decimal, '.', '')}}');
    localStorageSubTotal.splice(rowindex, 0, '{{number_format(0, $general_setting->decimal, '.', '')}}');

    localStorage.setItem("localStorageProductId", localStorageProductId);
    localStorage.setItem("localStorageSaleUnit", localStorageSaleUnit);
    localStorage.setItem("localStorageProductCode", localStorageProductCode);
    localStorage.setItem("localStorageTaxName", localStorageTaxName);
    localStorage.setItem("localStorageTaxMethod", localStorageTaxMethod);
    localStorage.setItem("localStorageTempUnitName", localStorageTempUnitName);
    localStorage.setItem("localStorageSaleUnitOperator", localStorageSaleUnitOperator);
    localStorage.setItem("localStorageSaleUnitOperationValue", localStorageSaleUnitOperationValue);
    checkQuantity(data[15], true);
    checkDiscount(data[15], true);
    localStorage.setItem("tbody-id", $("table.order-list tbody").html());
    if(data[16]) {
        populatePriceOption();
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.edit-product').click();
    }
}


function populatePriceOption() {
    $('#editModal select[name=price_option]').empty();
    $('#editModal select[name=price_option]').append('<option value="'+ product_price[rowindex] +'">'+ product_price[rowindex] +'</option>');
    if(wholesale_price[rowindex] > 0)
        $('#editModal select[name=price_option]').append('<option value="'+ wholesale_price[rowindex] +'">'+ wholesale_price[rowindex] +'</option>');
    $('.selectpicker').selectpicker('refresh');
}

function edit(){
    console.log("Editing row index: " + rowindex);
    $(".imei-section").remove();
    if(is_imei[rowindex]) {
        var imeiNumbers = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.imei-number').val();

        if(imeiNumbers.length) {
            imeiArrays = [...new Set(imeiNumbers.split(","))];
            htmlText = `<div class="col-md-8 form-group imei-section">
                        <label>IMEI or Serial Numbers</label>
                        <div class="table-responsive">
                            <table id="imei-table" class="table table-hover">
                                <tbody>`;
            for (var i = 0; i < imeiArrays.length; i++) {
                htmlText += `<tr>
                                <td>
                                    <input type="text" class="form-control imei-numbers" name="imei_numbers[]" value="`+imeiArrays[i]+`" />
                                </td>
                                <td>
                                    <button type="button" class="imei-del btn btn-sm btn-danger">X</button>
                                </td>
                            </tr>`;
            }
            htmlText += `</tbody>
                            </table>
                        </div>
                    </div>`;
            $("#editModal .modal-element").append(htmlText);
        }
    } 


    populatePriceOption();
    //$("#product-cost").text(cost[rowindex]);
    var row_product_name_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
    $('#modal_header').text(row_product_name_code);

    var qty = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
    $('input[name="edit_qty"]').val(qty);

    cur_product_id = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ') .product-id').val();

    $('input[name="edit_discount"]').val(parseFloat(product_discount[rowindex]).toFixed({{$general_setting->decimal}}));



    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
    pos = product_code.indexOf(row_product_code);
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
                        $("#coupon-modal").modal('hide');
                        $('input[name="coupon_id"]').val(value['id']);
                        $('input[name="coupon_discount"]').val(value['amount'] * currency['exchange_rate']);
                        $('#coupon-text').text(parseFloat(value['amount'] * currency['exchange_rate']).toFixed({{$general_setting->decimal}}));
                    }
                    else
                        alert('Grand Total is not sufficient for discount! Required '+value['minimum_amount']+' '+currency['code']);
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
                    $("#coupon-modal").modal('hide');
                    $('input[name="coupon_id"]').val(value['id']);
                    $('input[name="coupon_discount"]').val(coupon_discount);
                    $('#coupon-text').text(parseFloat(coupon_discount).toFixed({{$general_setting->decimal}}));
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
                product_price[rowindex] = parseFloat(data[0] * currency['exchange_rate']) + parseFloat(data[0] * currency['exchange_rate'] * customer_group_rate);
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
    checkQuantity(String(qty), flag);
    localStorage.setItem("tbody-id", $("table.order-list tbody").html());
}


function checkQuantity(sale_qty, flag) {
    var row_product_code = $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-code').val();
    pos = product_code.indexOf(row_product_code);
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.in-stock').text(product_qty[pos]);
    localStorageQty[rowindex] = sale_qty;
    localStorage.setItem("localStorageQty", localStorageQty);
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
                    localStorageQty[rowindex] = sale_qty;
                    localStorage.setItem("localStorageQty", localStorageQty);
                    checkQuantity(sale_qty, true);
                }
                else {
                    localStorageQty[rowindex] = sale_qty;
                    localStorage.setItem("localStorageQty", localStorageQty);
                    edit();
                    return;
                }
            }
            $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
        }
        else if(product_type[pos] == 'combo'){
            child_id = product_list[pos].split(',');
            child_qty = qty_list[pos].split(',');
            $(child_id).each(function(index) {
                var position = product_id.indexOf(parseInt(child_id[index]));
                //console.log(position);
                if( position == -1 || parseFloat(sale_qty * child_qty[index]) > product_qty[position] ) {
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
    else
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
    if(!flag) {
        $('#editModal').modal('hide');
        $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
    }
    calculateRowProductData(sale_qty);
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

function calculateRowProductData(quantity) {
    if(product_type[pos] == 'standard')
        unitConversion();
    else
        row_product_price = product_price[rowindex];
    if (tax_method[rowindex] == 1) {
        var net_unit_price = row_product_price - product_discount[rowindex];
        var tax = net_unit_price * quantity * (tax_rate[rowindex] / 100);
        var sub_total = (net_unit_price * quantity) + tax;

        if(parseFloat(quantity))
            var sub_total_unit = sub_total / quantity;
        else
            var sub_total_unit = sub_total;
    }
    else {
        var sub_total_unit = row_product_price - product_discount[rowindex];
        var net_unit_price = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
        var tax = (sub_total_unit - net_unit_price) * quantity;
        var sub_total = sub_total_unit * quantity;
    }

    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-price').text(sub_total_unit.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed({{$general_setting->decimal}}));
    $('table.order-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed({{$general_setting->decimal}}));

    localStorageProductDiscount.splice(rowindex, 1, (product_discount[rowindex] * quantity).toFixed({{$general_setting->decimal}}));
    localStorageTaxRate.splice(rowindex, 1, tax_rate[rowindex].toFixed({{$general_setting->decimal}}));
    localStorageNetUnitPrice.splice(rowindex, 1, net_unit_price.toFixed({{$general_setting->decimal}}));
    localStorageTaxValue.splice(rowindex, 1, tax.toFixed({{$general_setting->decimal}}));
    localStorageSubTotalUnit.splice(rowindex, 1, sub_total_unit.toFixed({{$general_setting->decimal}}));
    localStorageSubTotal.splice(rowindex, 1, sub_total.toFixed({{$general_setting->decimal}}));
    localStorage.setItem("localStorageProductDiscount", localStorageProductDiscount);
    localStorage.setItem("localStorageTaxRate", localStorageTaxRate);
    localStorage.setItem("localStorageNetUnitPrice", localStorageNetUnitPrice);
    localStorage.setItem("localStorageTaxValue", localStorageTaxValue);
    localStorage.setItem("localStorageSubTotalUnit", localStorageSubTotalUnit);
    localStorage.setItem("localStorageSubTotal", localStorageSubTotal);

    calculateTotal();
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
    var order_tax = parseFloat($('select[name="order_tax_rate_select"]').val());
    var order_discount_type = $('select[name="order_discount_type_select"]').val();
    var order_discount_value = parseFloat($('input[name="order_discount_value"]').val());

    if (!order_discount_value)
        order_discount_value = {{number_format(0, $general_setting->decimal, '.', '')}};

    if(order_discount_type == 'Flat') {
        if(!currencyChange) {
            var order_discount = parseFloat(order_discount_value);
        }
        else
            var order_discount = parseFloat(order_discount_value*currency['exchange_rate']);
    }
    else
        var order_discount = parseFloat(subtotal * (order_discount_value / 100));

    localStorage.setItem("order-tax-rate-select", order_tax);
    localStorage.setItem("order-discount-type", order_discount_type);
    $("#discount").text(order_discount.toFixed({{$general_setting->decimal}}));
    $('input[name="order_discount"]').val(order_discount);
    $('input[name="order_discount_type"]').val(order_discount_type);
    if(!currencyChange)
        var shipping_cost = parseFloat($('input[name="shipping_cost"]').val());
    else
        var shipping_cost = parseFloat($('input[name="shipping_cost"]').val() * currency['exchange_rate']);
    if (!shipping_cost)
        shipping_cost = {{number_format(0, $general_setting->decimal, '.', '')}};

    item = ++item + '(' + total_qty + ')';
    order_tax = (subtotal - order_discount) * (order_tax / 100);
    var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;
    $('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));

    couponDiscount();
    if(!currencyChange)
        var coupon_discount = parseFloat($('input[name="coupon_discount"]').val());
    else
        var coupon_discount = parseFloat($('input[name="coupon_discount"]').val() * currency['exchange_rate']);
    if (!coupon_discount)
        coupon_discount = {{number_format(0, $general_setting->decimal, '.', '')}};
    grand_total -= coupon_discount;

    $('#item').text(item);
    $('input[name="item"]').val($('table.order-list tbody tr:last').index() + 1);
    $('#subtotal').text(subtotal.toFixed({{$general_setting->decimal}}));
    $('#tax').text(order_tax.toFixed({{$general_setting->decimal}}));
    $('input[name="order_tax"]').val(order_tax.toFixed({{$general_setting->decimal}}));
    $('#shipping-cost').text(shipping_cost.toFixed({{$general_setting->decimal}}));
    $('input[name="shipping_cost"]').val(shipping_cost);
    $('#grand-total').text(grand_total.toFixed({{$general_setting->decimal}}));
    $('input[name="grand_total"]').val(grand_total.toFixed({{$general_setting->decimal}}));
    currencyChange = false;
}

function hide() {
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".cheque").hide();
    $(".gift-card").hide();
    $(".mobile_money_fields").hide();
    
    $('input[name="cheque_no"]').attr('required', false);
}

function giftCard() {
    $(".gift-card").show();
    $.ajax({
        url: 'sales/get_gift_card',
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
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".cheque").hide();
    $('input[name="cheque_no"]').attr('required', false);
}


function mobile_money() {
    $(".mobile_money_fields").show();
    $(".gift-card").hide();
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".cheque").hide();
    $('input[name="cheque_no"]').attr('required', false);
}

function cheque() {
    $(".cheque").show();
    $('input[name="cheque_no"]').attr('required', true);
    $(".card-element").hide();
    $(".card-errors").hide();
    $(".gift-card").hide();
}

function creditCard() {
    @if($lims_pos_setting_data && (strlen($lims_pos_setting_data->stripe_public_key)>0) && (strlen($lims_pos_setting_data->stripe_secret_key )>0))
    $.getScript( "vendor/stripe/checkout.js" );
    $(".card-element").show();
    $(".card-errors").show();
    @endif
    $(".cheque").hide();
    $(".gift-card").hide();
    $('input[name="cheque_no"]').attr('required', false);
}

function deposits() {
    if($('input[name="paid_amount"]').val() > deposit[$('#customer_id').val()]){
        alert('Amount exceeds customer deposit! Customer deposit : '+ deposit[$('#customer_id').val()]);
    }
    $('input[name="cheque_no"]').attr('required', false);
    $('#add-payment select[name="gift_card_id_select"]').attr('required', false);
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
        wholesale_price.pop();
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
    $('input[name="order_discount_value"]').val('');
    $('select[name="order_tax_rate_select"]').val(0);
    calculateTotal();
}

function confirmCancel() {
    var audio = $("#mysoundclip2")[0];
    audio.play();
    if (confirm("Are you sure want to cancel?")) {
        cancel($('table.order-list tbody tr:last').index());
    }
    return false;
}

function confirmDelete() {
    if (confirm("Are you sure want to delete?")) {
        return true;
    }
    return false;
}




$('#product-table').DataTable( {
    "order": [],
    'pageLength': product_row_number,
     'language': {
        'paginate': {
            'previous': '<i class="fa fa-angle-left"></i>',
            'next': '<i class="fa fa-angle-right"></i>'
        }
    },
    dom: 'tp'
});
</script>
    {!! ToastMagic::scripts() !!}

<script type="text/javascript" src="https://js.stripe.com/v3/"></script>
@endpush
