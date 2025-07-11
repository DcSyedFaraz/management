<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.reset_password.title') }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #1F2937;
            background-color: #F3F4F6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .email-card {
            background-color: #FFFFFF;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #E5E7EB;
            margin-bottom: 25px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background-color: #3B82F6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .logo-icon {
            font-size: 40px;
            color: white;
        }

        h1 {
            color: #1F2937;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .content {
            text-align: center;
        }

        p {
            color: #4B5563;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .otp-code {
            font-size: 32px;
            font-weight: 700;
            color: #3B82F6;
            letter-spacing: 4px;
            background-color: #EFF6FF;
            padding: 15px 25px;
            border-radius: 6px;
            margin: 25px 0;
            display: inline-block;
        }

        .expiry {
            font-size: 14px;
            color: #DC2626;
            background-color: #FEF2F2;
            padding: 8px 12px;
            border-radius: 4px;
            display: inline-block;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            color: #6B7280;
            font-size: 14px;
            border-top: 1px solid #E5E7EB;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="email-card">
            <div class="header">
                <div class="logo">
                    <span class="logo-icon">🔒</span>
                </div>
                <h1>{{ __('messages.reset_password.title') }}</h1>
            </div>

            <div class="content">
                <p>{{ __('messages.reset_password.greeting') }}</p>
                <p>{{ __('messages.reset_password.intro') }}</p>

                <div class="otp-code">{{ $otp }}</div>

                <p>{{ __('messages.reset_password.security') }}</p>
                <div class="expiry">{{ __('messages.reset_password.valid_for') }}</div>

                <p style="margin-top: 25px;">{{ __('messages.reset_password.footer') }}</p>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} Box4pflege. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>

</html>
