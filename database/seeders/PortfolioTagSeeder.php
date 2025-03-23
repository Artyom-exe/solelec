<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PortfolioTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupération de tous les portfolios et tags
        $portfolios = Portfolio::all();
        $tags = Tag::all();

        // Association des tags aux projets
        $portfolioTags = [
            'Installation de panneaux solaires résidentiels' => ['Panneaux solaires', 'Photovoltaïque', 'Résidentiel', 'Installation'],
            'Mise en conformité électrique d\'une maison ancienne' => ['Mise en conformité', 'Mise aux normes', 'Résidentiel', 'Rénovation'],
            'Électricité en gros œuvre pour extension' => ['Gros œuvre', 'Résidentiel', 'Installation'],
            'Dépannage suite à court-circuit' => ['Dépannage', 'Intervention urgente', 'Résidentiel'],
            'Installation d\'une borne de recharge domestique' => ['Bornes de recharge', 'Véhicules électriques', 'Résidentiel', 'Installation'],
            'Rénovation électrique complète d\'un appartement' => ['Rénovation', 'Mise aux normes', 'Résidentiel'],
        ];

        foreach ($portfolioTags as $portfolioTitle => $tagNames) {
            $portfolio = $portfolios->where('title', $portfolioTitle)->first();

            if ($portfolio) {
                foreach ($tagNames as $tagName) {
                    $tag = $tags->where('name', $tagName)->first();
                    if ($tag) {
                        $portfolio->tags()->attach($tag->id);
                    }
                }
            }
        }
    }
}
