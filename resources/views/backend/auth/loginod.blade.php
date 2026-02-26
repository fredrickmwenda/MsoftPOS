@extends('backend.layout.auth')

@section('content')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #1e7e34 0%, #27a745 50%, #20c997 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-wrapper {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }

    .login-section {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .image-layer {
        display: none;
    }

    .outer-box {
        width: 100%;
        max-width: 450px;
    }

    .login-form {
        background: white;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    .form-inner {
        padding: 50px 40px;
    }

    .logo-box {
        margin-bottom: 30px;
        text-align: center;
    }

    .logo a {
        display: inline-block;
        text-decoration: none;
    }

    .logo img {
        max-width: 100%;
        height: auto;
        width: 180px;
    }

    .logo span {
        color: #1e7e34;
        font-size: 28px;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #333;
        font-weight: 600;
        font-size: 14px;
    }

    .form-group .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
        background-color: #f8f9fa;
    }

    .form-group .form-control:focus {
        outline: none;
        border-color: #27a745;
        background-color: #fff;
        box-shadow: 0 0 0 4px rgba(39, 167, 69, 0.1);
    }

    .form-group .form-control.is-invalid {
        border-color: #dc3545;
    }

    .form-group p {
        color: #dc3545;
        font-size: 13px;
        margin-top: 5px;
        font-weight: 500;
    }

    .form-group button {
        width: 100%;
        padding: 12px 24px;
        background: linear-gradient(135deg, #1e7e34 0%, #27a745 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-group button:hover {
        background: linear-gradient(135deg, #155d2f 0%, #1e7e34 100%);
        box-shadow: 0 8px 20px rgba(30, 126, 52, 0.3);
        transform: translateY(-2px);
    }

    .form-group button:active {
        transform: translateY(0);
    }

    .bottom-box {
        text-align: center;
        margin-top: 25px;
        padding-top: 25px;
        border-top: 2px solid #f0f0f0;
    }

    .bottom-box a {
        color: #27a745;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .bottom-box a:hover {
        color: #1e7e34;
        text-decoration: underline;
    }

    .alert {
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 0;
        border: none;
    }

    .alert-success {
        background-color: #d4edda;
        color: #155724;
        border-left: 4px solid #27a745;
    }

    /* Responsive Design */
    @media (max-width: 576px) {
        .form-inner {
            padding: 40px 25px;
        }

        .logo img {
            width: 140px;
        }

        .logo span {
            font-size: 24px;
        }
    }
</style>

<div class="page-wrapper">

    <!-- Info Section -->
    <div class="login-section">
        <div class="outer-box">
            <!-- Login Form -->
            <div class="login-form default-form">
                <div class="form-inner">
                    <div class="logo-box">
                        <div class="logo">
                            <a href="{{url('/')}}">
                                @if($general_setting->site_logo)
                                    <img src="{{url('logo', $general_setting->site_logo)}}" alt="Logo">
                                @else
                                    <span>{{$general_setting->site_title}}</span>
                                @endif
                            </a>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group">
                            <label for="email">Username</label>
                            <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Enter your username">
                            @if(session()->has('error'))
                                <p>
                                    <strong>{{ session()->get('error') }}</strong>
                                </p>
                            @endif
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <input id="password-field" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Enter your password">
                            @if(session()->has('error'))
                                <p>
                                    <strong>{{ session()->get('error') }}</strong>
                                </p>
                            @endif
                        </div>

                        <div class="form-group">
                            <button type="submit" name="Register">Login</button>
                        </div>
                    </form>

                    <div class="bottom-box">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">{{ __('Forgot Your Password?') }}</a>
                        @endif
                        <br>
                        @if(session('verification'))
                            <div class="alert alert-success" role="alert" style="margin-top: 15px;">
                                A verification email has been sent to your email address. Please click the link in the email to verify your account.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!--End Login Form -->
        </div>
    </div>
    <!-- End Info Section -->

</div><!-- End Page Wrapper -->




@endsection

