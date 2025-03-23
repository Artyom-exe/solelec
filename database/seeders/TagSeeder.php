<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'Panneaux solaires',
            'Photovoltaïque',
            'Mise en conformité',
            'Mise aux normes',
            'Gros œuvre',
            'Dépannage',
            'Intervention urgente',
            'Bornes de recharge',
            'Véhicules électriques',
            'Résidentiel',
            'Commercial',
            'Rénovation',
            'Installation',
            'Basse consommation',
            'Tableau électrique',
            'Éclairage',
        ];

        foreach ($tags as $tagName) {
            Tag::create([
                'name' => $tagName,
                'slug' => Str::slug($tagName),
            ]);
        }
    }
}
