<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer login - arkandstar.com</title>
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
        .login-card {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            border: 1px solid #f3f4f6;
            width: 100%;
            max-width: 420px;
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
            /* Gold color filter for the SVG */
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
            margin-bottom: 20px;
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

    <div class="login-card">
        <!-- Header -->
        <div class="header-section">
            <!-- Placeholder SVG for Golden Ark Icon -->
            <!-- Replace this <svg> with <img src="{{ asset('images/your-logo.png') }}" class="logo-icon" alt="Logo"> if you have an image -->
            <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L2 12h3v8h14v-8h3L12 2zm0 2.83L17.17 10H6.83L12 4.83zM12 16H8v-2h4v2zm4-2h-2v2h2v-2z"/>
            </svg>
            
            <h2>Customer login</h2>
            <p>View your orders and payment history.</p>
        </div>

        <!-- Form -->
        <form action="{{ route('customer.login.post') }}" method="POST">
            @csrf
            
            <!-- Display Validation Errors -->
            @if ($errors->any())
                <div class="error-msg">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="form-group">
                <input type="email" name="email" id="email" placeholder="Email" value="{{ old('email') }}" required autofocus>
            </div>

            <div class="form-group">
                <input type="password" name="password" id="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn-submit">Sign in</button>
        </form>

        <!-- Links -->
        <div class="links-section">
            <a href="{{ route('customer.register') }}">Create a customer account</a>
            <a href="{{ route('shop.index') }}">&larr; Back to store</a>
        </div>
    </div>

    <div class="footer">
        Powered by Msoft Ghana
    </div>

</body>
</html>