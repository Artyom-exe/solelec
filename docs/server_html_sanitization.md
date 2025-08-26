# Assainissement du HTML côté serveur (recommandations)

Ce document explique comment protéger le projet contre les contenus HTML malveillants générés par un éditeur riche (TipTap) et propose deux approches :

1. Utiliser DOMPurify côté Node/JS lors du rendu (optionnel)
2. Utiliser HTMLPurifier côté PHP pour assainir avant stockage ou avant affichage

## Pourquoi ?

Les éditeurs riches peuvent insérer du HTML. Même si le client tente de nettoyer le contenu, il ne faut jamais faire confiance au côté client. Il faut donc assainir côté serveur.

## Option recommandée (PHP) — HTMLPurifier

Installation via Composer :

```bash
composer require ezyang/htmlpurifier
```

Utilisation exemple dans un contrôleur ou FormRequest :

```php
use HTMLPurifier_Config;
use HTMLPurifier;

$config = HTMLPurifier_Config::createDefault();
$config->set('HTML.SafeIframe', true);
$config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www.youtube.com/embed/|player.vimeo.com/video/)%');

$purifier = new HTMLPurifier($config);
$cleanHtml = $purifier->purify($dirtyHtml);
```

Exemple dans un contrôleur :

```php
public function store(QuoteStoreRequest $request)
{
    $data = $request->validated();

    // Assainir la description riche
    if (!empty($data['description'])) {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        $data['description'] = $purifier->purify($data['description']);
    }

    // Enregistrer la quote
    Quote::create($data);
}
```

## Option alternative (pré-rendu côté JS avec DOMPurify)

Si tu préfères assainir avant envoi (UX), intègre DOMPurify côté client :

```bash
npm install dompurify
```

```js
import DOMPurify from "dompurify";

const clean = DOMPurify.sanitize(dirtyHtml);
```

Important : cela n’exempte pas l’assainissement côté serveur.

## Recommandations supplémentaires

-   Logger les contenus bloqués pour audit.
-   Fournir une whitelist d’éléments autorisés si nécessaire (ex: <p>, <ul>, <li>, <strong>, <em>, <a>).
-   Éviter d’autoriser des iframes externes sauf si besoin.
-   Ajouter des tests unitaires pour vérifier que les contenus malicieux sont neutralisés.

---

Ce fichier peut être référencé dans ton rapport et intégré dans la section sécurité / traitement des contenus riches.
