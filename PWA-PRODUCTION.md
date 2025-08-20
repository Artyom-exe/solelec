# Configuration PWA pour la Production - Solelec Admin

## 🌐 Configuration Serveur Web (Apache/Nginx)

### Apache (.htaccess)

Ajoutez ce code dans votre fichier `.htaccess` à la racine :

```apache
# En-têtes PWA
Header always set Service-Worker-Allowed "/"
Header always set X-Frame-Options "SAMEORIGIN"

# Types MIME pour PWA
AddType application/manifest+json .webmanifest
AddType application/manifest+json .json

# Cache pour les assets PWA
<FilesMatch "\.(webmanifest|json)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 week"
</FilesMatch>

<FilesMatch "\.(png|jpg|jpeg|ico)$">
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
</FilesMatch>
```

### Nginx

Ajoutez dans votre configuration Nginx :

```nginx
# En-têtes PWA
add_header Service-Worker-Allowed "/";
add_header X-Frame-Options "SAMEORIGIN";

# Types MIME
location ~ \.(webmanifest|json)$ {
    add_header Content-Type application/manifest+json;
    expires 1w;
}

# Cache des assets
location ~* \.(png|jpg|jpeg|ico)$ {
    expires 1M;
    add_header Cache-Control "public, immutable";
}
```

## ⚙️ Variables d'environnement (.env)

Ajoutez dans votre fichier `.env` de production :

```env
# Configuration PWA
PWA_NAME="Solelec Admin"
PWA_SHORT_NAME="Solelec"
PWA_THEME_COLOR="#FF8C42"
PWA_BACKGROUND_COLOR="#2D2D2D"

# URL de base (OBLIGATOIRE)
APP_URL=https://votre-domaine.com
```

## 🔒 SSL/HTTPS - OBLIGATOIRE

⚠️ **IMPORTANT** : Les PWA nécessitent absolument HTTPS en production.

### Options recommandées :

1. **Let's Encrypt** (gratuit) - Certbot
2. **Cloudflare** (gratuit avec CDN)
3. **OVH SSL** (payant mais simple)

### Commandes Let's Encrypt (Ubuntu/Debian) :

```bash
sudo apt install certbot python3-certbot-apache
sudo certbot --apache -d votre-domaine.com
```

## 📦 Étapes de Déploiement

### 1. Préparer l'application

```bash
# Compiler les assets pour la production
npm run build

# Optimiser le cache Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Vérifier l'installation PWA

1. **Accédez à** : `https://votre-domaine.com/admin`
2. **Ouvrez DevTools** (F12) → Onglet **Application**
3. **Vérifiez** :
    - ✅ Manifest : Pas d'erreurs
    - ✅ Service Worker : Activé
    - ✅ Installabilité : Critères respectés

### 3. Tester l'installation

1. **Dans Chrome** : Cherchez l'icône **+** dans la barre d'adresse
2. **Cliquez sur "Installer Solelec"**
3. **Vérifiez** que l'app s'ouvre comme une application native

## 📱 Fonctionnalités en Production

### ✅ Ce que vos clients pourront faire :

-   **Installer l'application** sur leur ordinateur/téléphone
-   **Accéder rapidement** via une icône sur le bureau
-   **Utiliser l'app même hors ligne** (cache intelligent)
-   **Recevoir des notifications** de mises à jour
-   **Utiliser les raccourcis** (Dashboard, Interventions)

### 🎯 Avantages pour vos clients :

-   **Expérience native** comme une vraie application
-   **Chargement ultra-rapide** après la première visite
-   **Accès offline** aux pages récemment visitées
-   **Aucune installation complexe** requis

## 🔍 Surveillance et Tests

### Test Lighthouse (recommandé)

1. **DevTools** → Onglet **Lighthouse**
2. **Sélectionnez "Progressive Web App"**
3. **Score visé** : > 90/100

### Vérifications manuelles

-   [ ] HTTPS fonctionne correctement
-   [ ] Manifest se charge sans erreur
-   [ ] Service Worker s'active
-   [ ] Installation PWA proposée
-   [ ] App fonctionne hors ligne
-   [ ] Notifications de mise à jour

## 🚨 Résolution de Problèmes

### Problème : "Pas d'icône d'installation"

**Solutions** :

1. Vérifiez que vous êtes en HTTPS
2. Contrôlez le manifest dans DevTools
3. Assurez-vous que les icônes existent

### Problème : "Service Worker ne s'active pas"

**Solutions** :

1. Vérifiez la console pour les erreurs
2. Contrôlez que le fichier `sw.js` est accessible
3. Supprimez le cache du navigateur

### Problème : "App ne fonctionne pas hors ligne"

**Solutions** :

1. Vérifiez que le Service Worker cache les bonnes ressources
2. Contrôlez la version du cache dans `sw.js`
3. Testez en mode réseau désactivé dans DevTools

## 📞 Support

Pour toute question sur la PWA Solelec Admin :

-   Vérifiez les logs de la console navigateur
-   Testez avec l'onglet Application des DevTools
-   Consultez la documentation PWA officielle

**Votre PWA Solelec Admin est maintenant prête pour la production !** 🚀
