<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Default SEO meta -->
    <meta name="description" content="Solelec — Électricien professionnel (Wallonie). Installation, maintenance et solutions photovoltaïques pour particuliers et entreprises." />
    <meta name="robots" content="index, follow" />
    <meta property="og:site_name" content="{{ config('app.name', 'Solelec') }}" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="{{ config('app.name', 'Solelec') }}" />
    <meta property="og:description" content="Solelec — Électricien professionnel (Wallonie). Installation, maintenance et solutions photovoltaïques." />
    <meta property="og:url" content="{{ rtrim(config('app.url', env('APP_URL')), '/') }}" />
    <meta property="og:image" content="{{ rtrim(config('app.url', env('APP_URL')), '/') }}/images/social-default.jpg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ config('app.name', 'Solelec') }}" />
    <meta name="twitter:description" content="Installation et maintenance électrique — Solelec" />
    <meta name="twitter:image" content="{{ rtrim(config('app.url', env('APP_URL')), '/') }}/images/social-default.jpg" />

    <!-- JSON-LD Organization -->
    <script type="application/ld+json">
    {
    "@context": "https://schema.org",
    "@type": "Electrician",
    "name": "Solelec",
    "url": "https://solelec.be",
    "logo": "https://solelec.be/images/logo.png",
    "image": "https://solelec.be/images/social-default.jpg",
    "description": "Solelec — Électricien professionnel en Wallonie. Installation, maintenance et dépannage électrique pour particuliers et entreprises.",
    "telephone": "0492 51 09 31",
    "email": "solelec.lmbt@gmail.com",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "Rue de Neufmoustier 4",
        "addressLocality": "Ottignies-Louvain-la-Neuve",
        "postalCode": "1348",
        "addressCountry": "BE"
    },
    "areaServed": {
        "@type": "AdministrativeArea",
        "name": "Wallonie"
    },
    }
</script>


    <!-- Fonts -->
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

    <!-- Google Maps API -->
    <script>
        window.initMap = function() {
            // Cette fonction sera appelée quand Google Maps sera chargé
            // Et elle déclenchera un événement personnalisé
            document.dispatchEvent(new Event('google-maps-loaded'));
        }
    </script>

    <!-- Google Maps API - Correction du paramètre loading -->
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ env('VITE_GOOGLE_MAPS_API_KEY') }}&callback=initMap&loading=async"
        defer></script>

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
