#!/bin/bash

# Script pour générer les icônes PWA à partir d'une image source
# Nécessite ImageMagick (sudo apt-get install imagemagick ou brew install imagemagick)

# Image source (remplacez par votre logo)
SOURCE_IMAGE="logo-source.png"  # Placez votre logo 1024x1024 ici
OUTPUT_DIR="c:/laragon/www/solelec/public/images/icons"

# Créer le répertoire s'il n'existe pas
mkdir -p "$OUTPUT_DIR"

# Tailles d'icônes requises
sizes=(16 32 57 60 70 72 76 96 114 120 128 144 150 152 180 192 310 384 512)

echo "Génération des icônes PWA..."

for size in "${sizes[@]}"; do
    echo "Génération de l'icône ${size}x${size}..."
    convert "$SOURCE_IMAGE" -resize "${size}x${size}" "$OUTPUT_DIR/icon-${size}x${size}.png"
done

# Icônes spéciales
echo "Génération de l'icône Apple Touch..."
convert "$SOURCE_IMAGE" -resize "180x180" "$OUTPUT_DIR/apple-touch-icon.png"

echo "Génération de l'icône Microsoft wide..."
convert "$SOURCE_IMAGE" -resize "310x150" "$OUTPUT_DIR/icon-310x150.png"

echo "Génération terminée !"
echo "Placez vos propres icônes dans le dossier $OUTPUT_DIR"
echo "Tailles requises : 16x16, 32x32, 72x72, 96x96, 128x128, 144x144, 152x152, 192x192, 384x384, 512x512"
