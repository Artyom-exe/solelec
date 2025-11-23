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
            'Résidentiel',
            'Travaux en hauteur',
            'Installation extérieure',
            'Sécurité chantier',
            'Éclairage',
            'Façade',
            'Tableau électrique',
            'Mise aux normes',
            'Distribution électrique',
            'Industriel',
            'Local technique',
            'Câblage',
            'Conduits rigides',
            'Éclairage intérieur',
            'LED',
            'Décoratif',
            'Parking',
            'Prises',
            'Installation apparente',
            'Mise à la terre',
            'Mesure',
            'Sécurité',
            'Contrôle',
            'Panneaux solaires',
            'Photovoltaïque',
            'Énergie renouvelable',
        ];

        foreach ($tags as $name) {
            Tag::updateOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            );
        }

    }
}
