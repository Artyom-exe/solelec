<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            // Général
            'Résidentiel',
            'Industriel',

            // Types de travaux
            'Installation électrique',
            'Mise aux normes',
            'Rénovation',
            'Dépannage',

            // Spécifiques techniques
            'Travaux en hauteur',
            'Installation extérieure',
            'Installation apparente',
            'Câblage',
            'Conduits rigides',
            'Distribution électrique',
            'Tableau électrique',
            'Mise à la terre',
            'Mesure',
            'Contrôle',
            'Sécurité',
            'Sécurité chantier',

            // Éclairage
            'Éclairage',
            'Éclairage intérieur',
            'Éclairage LED',
            'LED',
            'Décoratif',

            // Lieux / usages
            'Façade',
            'Parking',
            'Cuisine',
            'Local technique',

            // Énergies
            'Panneaux solaires',
            'Photovoltaïque',
            'Énergie renouvelable',

            // Équipements
            'Prises',
        ];

        foreach ($tags as $name) {
            Tag::updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }

    }
}
