# Configuration PWA pour Solelec Admin

## Installation de l'application

Votre interface d'administration Solelec est maintenant une PWA (Progressive Web App) installable !

### Sur mobile (Android/iOS) :

1. **Android Chrome :**

    - Ouvrez `/admin` dans Chrome
    - Appuyez sur le menu (3 points) ⋮
    - Sélectionnez "Ajouter à l'écran d'accueil" ou "Installer l'app"
    - Confirmez l'installation

2. **iPhone Safari :**
    - Ouvrez `/admin` dans Safari
    - Appuyez sur le bouton de partage 📤
    - Faites défiler et sélectionnez "Sur l'écran d'accueil"
    - Nommez l'app "Solelec Admin" et confirmez

### Sur ordinateur :

1. **Chrome/Edge :**

    - Ouvrez `/admin` dans votre navigateur
    - Cliquez sur l'icône d'installation dans la barre d'adresse (⊕)
    - Ou menu ⋮ > "Installer Solelec Admin"

2. **Firefox :**
    - Ouvrez `/admin` dans Firefox
    - Menu ☰ > "Installer cette application"

## Fonctionnalités PWA activées :

✅ **Installation native** - L'app apparaît comme une vraie application
✅ **Mode hors ligne** - Fonctionne sans connexion internet (cache intelligent)
✅ **Notifications** - Alertes de mise à jour disponibles
✅ **Raccourcis** - Accès direct aux sections (Interventions, Devis, Clients)
✅ **Thème personnalisé** - Interface adaptée avec les couleurs Solelec
✅ **Responsive** - Optimisé mobile et desktop

## Configuration des icônes :

Pour personnaliser les icônes, placez vos images dans `/public/images/icons/` :

-   `icon-16x16.png` à `icon-512x512.png` (différentes tailles)
-   `apple-touch-icon.png` (180x180) pour iOS
-   Format PNG recommandé
-   Fond transparent ou couleur #2D2D2D

## Utilisation du script de génération d'icônes :

```bash
# 1. Placez votre logo 1024x1024 dans le dossier racine
# 2. Renommez-le "logo-source.png"
# 3. Exécutez le script (nécessite ImageMagick)
chmod +x generate-icons.sh
./generate-icons.sh
```

## URLs importantes :

-   **Application principale :** `/admin`
-   **Manifest :** `/manifest.json`
-   **Service Worker :** `/sw.js`
-   **Raccourcis directs :**
    -   Interventions : `/admin/interventions`
    -   Devis : `/admin/devis`
    -   Clients : `/admin/clients`

## Avantages pour vos clients :

1. **Accès rapide** - Une seule icône sur l'écran d'accueil
2. **Expérience native** - Se comporte comme une vraie app
3. **Performance** - Chargement plus rapide grâce au cache
4. **Hors ligne** - Consultation possible sans internet
5. **Sécurité** - Toujours la dernière version avec mises à jour automatiques

## Notes techniques :

-   Le cache se met à jour automatiquement
-   Les données sensibles ne sont jamais stockées hors ligne
-   Compatible avec tous les navigateurs modernes
-   Respecte les standards web PWA de Google/Apple/Microsoft
