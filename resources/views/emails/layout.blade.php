<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Notification SOLELEC' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: #374151;
            line-height: 1.6;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #2D2D2D 0%, #1a1a1a 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255, 140, 66, 0.1) 0%, rgba(255, 140, 66, 0.05) 100%);
        }

        .logo {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            font-weight: 700;
            color: #FF8C42;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .subtitle {
            font-family: 'Inter', sans-serif;
            color: #E5E7EB;
            margin: 8px 0 0 0;
            font-size: 14px;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px 30px;
        }

        .title {
            font-family: 'Poppins', sans-serif;
            font-size: 24px;
            font-weight: 600;
            color: #1F2937;
            margin: 0 0 20px 0;
            text-align: center;
        }

        .message {
            font-size: 16px;
            color: #4B5563;
            margin-bottom: 30px;
        }

        .button-container {
            text-align: center;
            margin: 40px 0;
        }

        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #FF8C42 0%, #e6793a 100%);
            color: #ffffff;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.4);
        }

        .footer {
            background-color: #F9FAFB;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }

        .footer-text {
            margin: 0;
            font-size: 14px;
            color: #6B7280;
        }

        .company-name {
            color: #FF8C42;
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }

            .header, .content, .footer {
                padding: 30px 20px;
            }

            .title {
                font-size: 20px;
            }

            .action-button {
                padding: 14px 28px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="logo">SOLELEC</h1>
            <p class="subtitle">Solutions Électriques Professionnelles</p>
        </div>

        <div class="content">
            @if(isset($title))
                <h2 class="title">{{ $title }}</h2>
            @endif

            <div class="message">
                {!! $slot !!}
            </div>

            @if(isset($actionUrl) && isset($actionText))
                <div class="button-container">
                    <a href="{{ $actionUrl }}" class="action-button">
                        {{ $actionText }}
                    </a>
                </div>
            @endif
        </div>

        <div class="footer">
            <p class="footer-text">
                Cordialement,<br>
                L'équipe <span class="company-name">SOLELEC</span>
            </p>
        </div>
    </div>
</body>
</html>
