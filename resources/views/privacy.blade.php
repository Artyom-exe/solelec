<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Politique de confidentialité — Solelec</title>

    <!-- Fonts (comme dans app.blade.php) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">

    <!-- Charger l'entrée Vite (inclut le CSS via import dans app.js) -->
    @vite('resources/js/app.js')
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    <main class="max-w-5xl mx-auto py-16 px-5 font-inter">
        <!-- Header sombre similaire au site -->
        <header class="bg-[#2D2D2D] rounded-lg p-8 mb-8 flex items-center gap-6">
            <!-- Logo inline (reprend le markup de resources/js/Components/logo.vue) -->
            <div class="flex items-center">
                <span class="font-inter font-extrabold text-xl text-white leading-7">S<span class="text-[#FF8C42]">O</span>LELEC</span>
            </div>
            <div>
                <h1 class="text-white text-2xl md:text-3xl font-poppins font-semibold">Politique de confidentialité</h1>
                <p class="text-sm text-gray-400 mt-1">Dernière mise à jour : 01/01/2025</p>
            </div>
        </header>

        <!-- Contenu principal dans une carte blanche -->
        <section class="bg-white shadow-lg rounded-lg p-8 prose prose-lg max-w-none text-gray-800">
            <h2 class="text-[#FF8C42]">Responsable du traitement</h2>
            <p>
                Solelec — Rue de Neufmoustier 4, 1348 Ottignies-Louvain-la-Neuve, Belgique<br>
                Email : <a href="mailto:solelec.lmbt@gmail.com" class="underline text-[#FF8C42]">solelec.lmbt@gmail.com</a>
            </p>

            <h2 class="text-[#FF8C42]">Catégories de données collectées</h2>
            <p>
                Nous collectons uniquement les données nécessaires pour répondre à vos demandes : nom, adresse e-mail, message et, le cas échéant, les éléments optionnels fournis volontairement via nos formulaires.
            </p>

            <h2 class="text-[#FF8C42]">Finalités et bases juridiques</h2>
            <ul>
                <li>Répondre aux demandes de contact et devis — base : exécution d’une demande / consentement.</li>
                <li>Amélioration du site et sécurité — base : intérêt légitime.</li>
            </ul>

            <h2 class="text-[#FF8C42]">Durée de conservation</h2>
            <p>
                Les messages de contact sont conservés le temps nécessaire à leur traitement et au suivi (par défaut 2 ans), sauf obligation légale contraire.
            </p>

            <h2 class="text-[#FF8C42]">Partage des données</h2>
            <p>
                Nous ne vendons pas vos données. Elles peuvent être partagées uniquement avec nos prestataires techniques (hébergeur, messagerie), soumis à des obligations strictes de confidentialité.
            </p>

            <h2 class="text-[#FF8C42]">Vos droits</h2>
            <p>
                Vous pouvez exercer vos droits d’accès, de rectification, de suppression, de limitation et de portabilité en nous contactant à l’adresse indiquée ci-dessus.
            </p>

            <h2 class="text-[#FF8C42]">Cookies</h2>
            <p>
                Le site utilise uniquement des cookies techniques indispensables à son bon fonctionnement. Aucun cookie publicitaire ou de suivi n’est utilisé.
            </p>

            <h2 class="text-[#FF8C42]">Hébergement</h2>
            <p>
                Le site est hébergé par : o2switch, 222-224 Boulevard Gustave Flaubert, 63000 Clermont-Ferrand, France.
            </p>

            <h2 class="text-[#FF8C42]">Contact</h2>
            <p>
                Pour toute question liée à la confidentialité de vos données : <a href="mailto:solelec.lmbt@gmail.com" class="underline text-[#FF8C42]">solelec.lmbt@gmail.com</a>
            </p>

            <div class="mt-6">
                <!-- Bouton stylé similaire à PrimaryButton.vue -->
                <a href="/" class="inline-flex justify-center items-center px-4 py-2 border border-[#FF8C42] rounded-md font-medium md:text-base text-sm font-inter bg-[#FF8C42] text-white transition-all duration-300 ease-in-out hover:bg-transparent hover:text-[#FF8C42] focus:outline-none focus:ring-2 focus:ring-[#FF8C42] focus:ring-opacity-50">
                    Retour à l'accueil
                </a>
            </div>
        </section>

        <footer class="mt-12 text-sm text-gray-500 text-center">
            <p>© 2025 Solelec. Tous droits réservés.</p>
        </footer>
    </main>
</body>
</html>
