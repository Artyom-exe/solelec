<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réinitialisation de mot de passe - SOLELEC</title>
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
            text-align: center;
        }

        .button-container {
            text-align: center;
            margin: 40px 0;
        }

        .reset-button {
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

        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.4);
        }

        .info-box {
            background: linear-gradient(135deg, #FEF3E2 0%, #FDE68A 100%);
            border-left: 4px solid #FF8C42;
            padding: 20px;
            margin: 30px 0;
            border-radius: 8px;
        }

        .info-text {
            margin: 0;
            font-size: 14px;
            color: #92400E;
        }

        .expiry-text {
            text-align: center;
            font-size: 14px;
            color: #6B7280;
            margin: 20px 0;
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

        .url-text {
            font-size: 12px;
            color: #9CA3AF;
            margin-top: 20px;
            word-break: break-all;
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

            .reset-button {
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
            <h2 class="title">Réinitialisation de votre mot de passe</h2>

            <p class="message">
                Bonjour,<br><br>
                Vous recevez cet email car nous avons reçu une demande de réinitialisation de mot de passe pour votre compte.
            </p>

            <div class="button-container">
                <a href="{{ $actionUrl }}" class="reset-button">
                    Réinitialiser mon mot de passe
                </a>
            </div>

            <p class="expiry-text">
                Ce lien de réinitialisation expirera dans {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.
            </p>

            <div class="info-box">
                <p class="info-text">
                    <strong>Important :</strong> Si vous n'avez pas demandé de réinitialisation de mot de passe, aucune action n'est requise de votre part. Votre compte reste sécurisé.
                </p>
            </div>

            <p class="url-text">
                Si vous rencontrez des difficultés avec le bouton ci-dessus, copiez et collez l'URL suivante dans votre navigateur :<br>
                {{ $actionUrl }}
            </p>
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
