<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue - Vos identifiants de connexion</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .credentials {
            background-color: #fff;
            padding: 15px;
            border: 1px solid #007bff;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Bienvenue chez Banque API</h1>
        <p>Votre compte a été créé avec succès</p>
    </div>

    <div class="content">
        <p>Bonjour <strong>{{ $client->titulaire }}</strong>,</p>

        <p>Votre compte bancaire a été créé avec succès. Voici vos identifiants de connexion :</p>

        <div class="credentials">
            <h3>Vos identifiants :</h3>
            <p><strong>Email :</strong> {{ $client->email }}</p>
            <p><strong>Mot de passe :</strong> {{ $password }}</p>
        </div>

        <div class="warning">
            <strong> Important :</strong>
            <ul>
                <li>Ce mot de passe est temporaire et doit être changé lors de votre première connexion.</li>
                <li>Conservez ces informations en lieu sûr.</li>
                <li>Ne partagez jamais vos identifiants avec qui que ce soit.</li>
            </ul>
        </div>

        <p>Vous pouvez maintenant vous connecter à votre espace client et commencer à utiliser vos services bancaires.</p>

        <p>Cordialement,<br>
        L'équipe Banque API</p>
    </div>

    <div class="footer">
        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>&copy; 2025 Banque API - Tous droits réservés</p>
    </div>
</body>
</html>