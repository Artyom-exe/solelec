Add-Type -AssemblyName System.Drawing

$sourcePath = "c:\laragon\www\solelec\public\images\icons\icon-512x512.png"
$targetPath = "c:\laragon\www\solelec\public\images\icons\icon-192x192.png"

# Charger l'image source
$img = [System.Drawing.Image]::FromFile($sourcePath)

# Créer une nouvelle image redimensionnée
$newImg = New-Object System.Drawing.Bitmap(192, 192)
$graphics = [System.Drawing.Graphics]::FromImage($newImg)
$graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
$graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality

# Dessiner l'image redimensionnée
$graphics.DrawImage($img, 0, 0, 192, 192)

# Sauvegarder
$newImg.Save($targetPath, [System.Drawing.Imaging.ImageFormat]::Png)

# Nettoyer les ressources
$graphics.Dispose()
$newImg.Dispose()
$img.Dispose()

Write-Host "Icône 192x192 créée avec succès !"
