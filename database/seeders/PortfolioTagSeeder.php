<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PortfolioTagSeeder extends Seeder
{
    public function run(): void
    {
        $portfolioTags = [
            'Travaux électriques en hauteur sur maison en bois' => [
                'Résidentiel',
                'Travaux en hauteur',
                'Installation extérieure',
                'Sécurité chantier',
            ],
            'Installation électrique extérieure sur façade en briques' => [
                'Résidentiel',
                'Installation extérieure',
                'Éclairage',
                'Façade',
            ],
            'Montage et câblage d’un tableau électrique complet' => [
                'Résidentiel',
                'Tableau électrique',
                'Mise aux normes',
                'Distribution électrique',
            ],
            'Installation électrique industrielle en local technique' => [
                'Industriel',
                'Local technique',
                'Câblage',
                'Conduits rigides',
            ],
            'Installation d’un luminaire mural LED design' => [
                'Résidentiel',
                'Éclairage intérieur',
                'LED',
                'Décoratif',
            ],
            'Installation de prises électriques industrielles en parking' => [
                'Industriel',
                'Parking',
                'Prises',
                'Installation apparente',
            ],
            'Test de mise à la terre d’une installation électrique' => [
                'Mise à la terre',
                'Mesure',
                'Sécurité',
                'Contrôle',
            ],
            'Installation de panneaux photovoltaïques en toiture inclinée' => [
                'Panneaux solaires',
                'Photovoltaïque',
                'Résidentiel',
                'Énergie renouvelable',
            ],
            'Installation d’éclairage LED et prises électriques' => [
                'Éclairage LED',
                'Installation électrique',
                'Cuisine',
                'Résidentiel',
            ],
        ];

        foreach ($portfolioTags as $portfolioTitle => $tagNames) {

            $portfolio = Portfolio::where('title', $portfolioTitle)->first();

            if (!$portfolio) {
                continue;
            }

            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(['name' => $tagName]);

                // évite les doublons dans la table pivot
                $portfolio->tags()->syncWithoutDetaching([$tag->id]);
            }
        }
    }
}
