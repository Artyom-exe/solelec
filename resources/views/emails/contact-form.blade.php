<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau message de contact - Solelec</title>
    <style>
        /* Styles inspirés de l'esthétique Tailwind mais intégrés directement */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.5;
            color: #1a202c;
            background-color: #f7fafc;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #2D2D2D;
            padding: 24px;
            text-align: center;
        }

        .header-title {
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .content {
            padding: 24px;
        }

        .info-item {
            margin-bottom: 16px;
        }

        .info-label {
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 4px;
        }

        .info-value {
            color: #2d3748;
            margin: 0;
        }

        .message-box {
            margin-top: 8px;
            padding: 16px;
            background-color: #f8fafc;
            border-radius: 6px;
            border-left: 4px solid #FF8C42;
            white-space: pre-line;
        }

        .footer {
            background-color: #f8fafc;
            padding: 16px 24px;
            text-align: center;
            font-size: 14px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }

        .reply-button {
            display: inline-block;
            margin-top: 20px;
            background-color: #FF8C42;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
        }

        .reply-button:hover {
            background-color: #e67e35;
        }

        .company-info {
            margin-top: 8px;
        }
    </style>
</head>

<body>
    <div style="padding: 20px;">
        <div class="container">
            <div class="header">
                <h1 class="header-title">Nouveau message de contact</h1>
            </div>

            <div class="content">
                @php
                    // Défendre contre le cas où une variable fournie serait un objet (ex: $message injecté par le mailer)
                    $safeName = isset($name) && is_string($name) ? $name : '';
                    $safeEmail = isset($email) && is_string($email) ? $email : '';
                    $safeMessage = isset($messageContent) && is_string($messageContent) ? $messageContent : '';
                @endphp
                <div class="info-item">
                    <div class="info-label">Nom</div>
                    <div class="info-value">{{ $safeName }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $safeEmail }}</div>
                </div>

                <div class="info-item">
                    <div class="info-label">Message</div>
                    <div class="message-box">{!! nl2br(e($safeMessage)) !!}</div>
                </div>

                <a href="mailto:{{ $safeEmail }}" class="reply-button">Répondre par email</a>
            </div>

            <div class="footer">
                <p>&copy; {{ date('Y') }} Solelec - Tous droits réservés</p>
                <p class="company-info">Rue de Neufmoustier 4, 1348 Ottignies-Louvain-la-Neuve, Belgium</p>
            </div>
        </div>
    </div>
</body>

</html>
