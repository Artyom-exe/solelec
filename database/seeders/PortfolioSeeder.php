<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('portfolio_tag')->truncate();

        Portfolio::query()->delete();

            $portfolios = [
        [
            'title' => 'Travaux électriques en hauteur sur maison en bois',
            'description' => 'Intervention en hauteur pour la réalisation d’une installation électrique extérieure sur une maison en bois. Mise en sécurité du chantier, accès via engin de levage, pose des câbles et fixation des équipements sur la façade.',
            'image' => 'images/portfolio/travaux-electricite-exterieure-maison-bois.webp',
        ],
        [
            'title' => 'Installation électrique extérieure sur façade en briques',
            'description' => 'Pose de conduits électriques et d’un luminaire étanche sur une façade en briques. Passage des câbles en apparent, fixation sécurisée et protection contre les intempéries.',
            'image' => 'images/portfolio/installation-electrique-facade-brique.webp',
        ],
        [
            'title' => 'Montage et câblage d’un tableau électrique complet',
            'description' => 'Assemblage et câblage d’un tableau électrique avec protections différentielles, disjoncteurs et borniers. Organisation soignée des circuits et conformité aux normes en vigueur.',
            'image' => 'images/portfolio/tableau-electrique-cable.webp',
        ],
        [
            'title' => 'Installation électrique industrielle en local technique',
            'description' => 'Installation de conduits rigides, armoires électriques et systèmes de sécurité dans un local technique. Passage des câbles, raccordements et mise aux normes de l’infrastructure.',
            'image' => 'images/portfolio/installation-electrique-industrielle.webp',
        ],
        [
            'title' => 'Installation d’un luminaire mural LED design',
            'description' => 'Pose et raccordement d’un luminaire mural LED avec éclairage haut/bas. Intégration discrète dans la pièce pour un éclairage moderne et chaleureux.',
            'image' => 'images/portfolio/installation-luminaire-mural.webp',
        ],
        [
            'title' => 'Installation de prises électriques industrielles en parking',
            'description' => 'Pose de conduits et de prises électriques en apparent dans un parking souterrain. Installation robuste et sécurisée pour alimenter des équipements techniques.',
            'image' => 'images/portfolio/installation-prise-parking-industriel.webp',
        ],
        [
            'title' => 'Test de mise à la terre d’une installation électrique',
            'description' => 'Mesure de la résistance de terre avec un appareil professionnel afin de vérifier la qualité de la prise de terre et garantir la sécurité de l’installation.',
            'image' => 'images/portfolio/test-mise-a-la-terre.webp',
        ],
        [
            'title' => 'Installation de panneaux photovoltaïques en toiture inclinée',
            'description' => 'Pose de panneaux solaires sur une toiture inclinée avec fixation sécurisée, orientation optimisée et raccordement électrique complet au système de production d’énergie.',
            'image' => 'images/portfolio/installation-panneaux-photovoltaiques-toiture.webp',
        ],
    ];

        foreach ($portfolios as $data) {
            Portfolio::updateOrCreate(
                ['title' => $data['title']],
                $data
            );
        }
    }
}
