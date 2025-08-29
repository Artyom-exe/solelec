<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mentions légales — Solelec</title>

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

    <!-- Charger le CSS principal via Vite -->
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-white text-gray-900">
    <main class="max-w-5xl mx-auto py-16 px-5 font-inter">
        <!-- Header sombre similaire au site -->
        <header class="bg-[#2D2D2D] rounded-lg p-8 mb-8 flex items-center gap-6">
            <!-- Logo inline -->
            <div class="flex items-center">
                <span class="font-inter font-extrabold text-xl text-white leading-7">S<span class="text-[#FF8C42]">O</span>LELEC</span>
            </div>
            <div>
                <h1 class="text-white text-2xl md:text-3xl font-poppins font-semibold">Mentions légales</h1>
                <p class="text-sm text-gray-400 mt-1">Dernière mise à jour : 29/08/2025</p>
            </div>
        </header>

        <section class="bg-white shadow-lg rounded-lg p-8 prose prose-lg max-w-none text-gray-800">
            <h2 class="text-[#FF8C42]">Éditeur du site</h2>
            <p>
                Solelec<br>
                Rue de Neufmoustier 4<br>
                1348 Ottignies-Louvain-la-Neuve, Belgique<br>
                Email : <a href="mailto:solelec.lmbt@gmail.com" class="underline text-[#FF8C42]">solelec.lmbt@gmail.com</a><br>
                Téléphone : 0492 51 09 31<br>
                Numéro d’entreprise / TVA : BE 0795.509.569
            </p>

            <h2 class="text-[#FF8C42]">Responsable de la publication</h2>
            <p>Monsieur Nicolas Lambot, en qualité de gérant de Solelec.</p>

            <h2 class="text-[#FF8C42]">Hébergement</h2>
            <p>
                Le site est hébergé par :<br>
                o2switch<br>
                222–224 Boulevard Gustave Flaubert<br>
                63000 Clermont-Ferrand, France<br>
                Téléphone : 04 44 44 60 40<br>
                Site web : <a href="https://www.o2switch.fr" class="underline text-[#FF8C42]">https://www.o2switch.fr</a>
            </p>

            <h2 class="text-[#FF8C42]">Propriété intellectuelle</h2>
            <p>
                Le contenu du site (textes, images, logos, éléments graphiques) est la propriété exclusive de Solelec, sauf mention contraire.
                Toute reproduction, représentation, modification ou utilisation non autorisée est strictement interdite.
            </p>

            <h2 class="text-[#FF8C42]">Responsabilité</h2>
            <p>
                Solelec met tout en œuvre pour assurer l’exactitude et la mise à jour des informations diffusées sur le site. Toutefois, l’entreprise ne peut être tenue responsable des erreurs ou omissions, ni des conséquences liées à l’utilisation de ces informations.
            </p>

            <h2 class="text-[#FF8C42]">Données personnelles</h2>
            <p>
                Pour les informations concernant la collecte et le traitement des données personnelles, veuillez consulter notre
                <a href="/privacy" class="underline text-[#FF8C42]">Politique de confidentialité</a>.
            </p>

            <div class="mt-6">
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
