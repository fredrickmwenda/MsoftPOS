<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Register - arkandstar.com</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f9fafb;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }

        /* Main Card Container */
        .register-card {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            width: 100%;
            max-width: 450px;
        }

        /* Logo & Header Section */
        .header-section {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 15px;
            display: block;
            filter: invert(69%) sepia(65%) saturate(336%) hue-rotate(8deg) brightness(92%) contrast(91%);
        }

        .header-section h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .header-section p {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            font-size: 14px;
            color: #1f2937;
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        .form-group input::placeholder {
            color: #9ca3af;
        }

        .form-group input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        }

        /* Error Message Style */
        .error-msg {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 10px;
            border-radius: 6px;
            font-size: 13px;
            text-align: center;
            margin-bottom: 20px;
            border: 1px solid #fecaca;
        }

        /* Field specific error highlight */
        .input-error {
            border-color: #ef4444 !important;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 12px;
            font-size: 15px;
            font-weight: 600;
            color: #ffffff;
            background-color: #16a34a;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background-color: #15803d;
        }

        /* Links Section */
        .links-section {
            text-align: center;
            margin-top: 25px;
        }

        .links-section a {
            display: block;
            text-decoration: none;
            font-size: 14px;
            color: #4b5563;
            margin-bottom: 10px;
            transition: color 0.2s;
        }

        .links-section a:hover {
            color: #1f2937;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="register-card">
        <!-- Header -->
        <div class="header-section">
            <!-- Placeholder SVG for Golden Ark Icon -->
            <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 12h3v8h14v-8h3L12 2zm0 2.83L17.17 10H6.83L12 4.83zM12 16H8v-2h4v2zm4-2h-2v2h2v-2z"/>
            </svg>
            
            <h2>Create an account</h2>
            <p>Sign up to start shopping and track your orders.</p>
        </div>

        <!-- Form -->
        <form action="{{ route('customer.register.post') }}" method="POST">
            @csrf
            
            <!-- Display Validation Errors -->
            @if ($errors->any())
                <div class="error-msg">
                    Please fix the errors highlighted below.
                </div>
            @endif

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" name="name" id="name" placeholder="John Doe" value="{{ old('name') }}" required class="@error('name') input-error @enderror">
                @error('name') <small style="color: #ef4444; font-size: 12px;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="you@example.com" value="{{ old('email') }}" required class="@error('email') input-error @enderror">
                @error('email') <small style="color: #ef4444; font-size: 12px;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="tel" name="phone_number" id="phone_number" placeholder="+233 123 456 789" value="{{ old('phone_number') }}" required class="@error('phone_number') input-error @enderror">
                @error('phone_number') <small style="color: #ef4444; font-size: 12px;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="Minimum 8 characters" required class="@error('password') input-error @enderror">
                @error('password') <small style="color: #ef4444; font-size: 12px;">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Re-type password" required>
            </div>

            <button type="submit" class="btn-submit">Create Account</button>
        </form>

        <!-- Links -->
        <div class="links-section">
            <a href="{{ route('customer.login') }}">&larr; Already have an account? Sign in</a>
            <a href="{{ route('shop.index') }}">&larr; Back to store</a>
        </div>
    </div>

    <div class="footer">
        Powered by Msoft Ghana
    </div>

</body>
</html>