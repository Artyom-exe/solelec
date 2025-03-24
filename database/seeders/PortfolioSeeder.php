<?php

namespace Database\Seeders;

use App\Models\Portfolio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $portfolios = [
            [
                'title' => 'Installation de panneaux solaires résidentiels',
                'description' => 'Installation d\'un système photovoltaïque de 3kW sur le toit d\'une maison individuelle. Raccordement au réseau et mise en place du système de monitoring de production.',
                'image' => 'images/portfolio/installation-panneaux-solaire.jpg',
            ],
            [
                'title' => 'Mise en conformité électrique d\'une maison ancienne',
                'description' => 'Rénovation du tableau électrique et remise aux normes complète d\'une habitation datant des années 70. Remplacement des câbles et installation de protections adaptées.',
                'image' => 'images/portfolio/mise-conformite-maison.jpg',
            ],
            [
                'title' => 'Électricité en gros œuvre pour extension',
                'description' => 'Réalisation de l\'installation électrique d\'une extension de maison de 40m². Pose des gaines, tirage des câbles et raccordement au tableau existant.',
                'image' => 'images/portfolio/extension-maison.jpg',
            ],
            [
                'title' => 'Dépannage suite à court-circuit',
                'description' => 'Intervention rapide chez un particulier suite à une panne générale. Diagnostic, isolation du circuit défectueux et réparation dans la journée.',
                'image' => 'images/portfolio/court-circuit.jpg',
            ],
            [
                'title' => 'Installation d\'une borne de recharge domestique',
                'description' => 'Pose et raccordement d\'une wallbox 11kW pour la recharge d\'un véhicule électrique. Configuration de la puissance et test de fonctionnement.',
                'image' => 'images/portfolio/borne-recharge.jpg',
            ],
            [
                'title' => 'Rénovation électrique complète d\'un appartement',
                'description' => 'Refonte totale de l\'installation électrique d\'un appartement de 85m². Remplacement des circuits, mise aux normes et installation de prises et points lumineux supplémentaires.',
                'image' => 'images/portfolio/renovation-appartement.jpg',
            ],
            [
                'title' => 'Installation de chauffage électrique',
                'description' => 'Mise en place d\'un système de chauffage électrique
                par le sol dans une salle de bain. Programmation et réglage de la température pour un confort optimal.',
                'image' => 'images/portfolio/radiateur.jpg',
            ],


        ];

        foreach ($portfolios as $portfolio) {
            Portfolio::create($portfolio);
        }
    }
}
