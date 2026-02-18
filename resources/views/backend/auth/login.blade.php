@extends('backend.layout.auth')

@section('content')
<div class="page-wrapper">

   @include('reusables.header')

    <!-- Info Section -->
    <div class="login-section">
      <!-- Replace this with a better one -->
      <div class="image-layer" style="background-image: url(<?php echo url('/')?>/board2/images/background/12.jpg);">
          <div class="row">
              <div class="col-sm-6 offset-sm-3">
                  <div class="card bg-white" style="height:100px; margin-top:200px;">
                      <center>
                          <h6 class="mb-2 text-black-50 mt-2">Powered By</h6>
                           <img src="{{asset('/images/msoft.png')}}"  width="200px" height="200px"/>
                      </center>
                      
                  </div>
              </div>
          </div>
      </div>
      <div class="outer-box">
        <!-- Login Form -->
        <div class="login-form default-form">
          <div class="form-inner">
              <center>
               <div class="logo-box">
              <div class="logo"><a href="{{url('/')}}">
              
               @if($general_setting->site_logo)
                <img src="{{url('logo', $general_setting->site_logo)}}" width="200px" height="100px">
                @else
                <span>{{$general_setting->site_title}}</span>
                @endif
              </a></div>
            </div>
            <br>
           <!-- <h3>{{env('APP_NAME')}}</h3>-->
            </center>

             <form method="POST" action="{{ route('login') }}">
                        @csrf
              

               <div class="form-group">
                <label>Username</label>
                 <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name">

                                @if(session()->has('error'))
                    <p>
                        <strong>{{ session()->get('error') }}</strong>
                    </p>
                @endif
              </div>

              <div class="form-group">
                <label>Password</label>
               <input id="password-field" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="new-password">
  @if(session()->has('error'))
                    <p>
                        <strong>{{ session()->get('error') }}</strong>
                    </p>
                @endif
              </div>

                

              <div class="form-group">
                <button class="theme-btn btn-style-one " type="submit" name="Register">Login</button>
              </div>
            </form>

            <div class="bottom-box">
                 @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="float-end" style="border-color:transparent !important;">  {{ __('Forgot Your Password?') }}</a>
                                @endif
                                <br>
                                @if(session('verification'))
    <div class="alert alert-success" role="alert">
        A verification email has been sent to your email address. Please click the link in the email to verify your account.
    </div>
@endif

             <!-- <div class="divider"><span>or</span></div>
              <div class="btn-box row">
                <div class="col-lg-6 col-6">
                  <a href="#" class="theme-btn social-btn-two facebook-btn bg-primary text-white" style="border-color:transparent !important;"><i class="fab fa-facebook-f"></i> Log In via Facebook</a>
                </div>
                <div class="col-lg-6 col-6">
                  <a href="#" class="theme-btn social-btn-two google-btn bg-danger text-white" style="border-color:transparent !important;"><i class="fab fa-google"></i> Log In via Gmail</a>
                </div>
                 <div class="col-lg-6 col-6">
                  <a href="#" class="theme-btn social-btn-two facebook-btn bg-info text-white" style="border-color:transparent !important;"><i class="fab fa-twitter"></i> Log In via Twitter</a>
                </div>
                <div class="col-lg-6 col-6">
                  <a href="#" class="theme-btn social-btn-two google-btn bg-info text-white" style="border-color:transparent !important;"><i class="fab fa-linkedin-in"></i> Log In via LinkedIn</a>
                </div>
              </div>-->
            </div>
          </div>
        </div>
        <!--End Login Form -->
      </div>
    </div>
    <!-- End Info Section -->


  </div><!-- End Page Wrapper -->




@endsection

