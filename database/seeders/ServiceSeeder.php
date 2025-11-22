<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
            $services = [
        [
            'title' => 'Panneaux photovoltaïques',
            'icon' => 'assets/icons/services/solar-panel-solid.svg',
            'short_description' => 'Installation de panneaux solaires pour une énergie renouvelable.',
            'description' => 'Nous vous accompagnons dans l’installation et la maintenance de vos panneaux photovoltaïques pour optimiser votre consommation d’énergie verte.',
            'image' => 'images/services/install-solare-panel.webp',
        ],
        [
            'title' => 'Bornes de recharge pour véhicule',
            'icon' => 'assets/icons/services/charging-station-solid.svg',
            'short_description' => 'Installation de bornes de recharge pour voitures électriques.',
            'description' => 'Nous proposons des solutions adaptées pour la recharge de véhicules électriques, en résidentiel ou en entreprise.',
            'image' => 'images/services/charging-station.jpg',
        ],
        [
            'title' => 'Mise en conformité électrique',
            'icon' => 'assets/icons/services/bolt-solid.svg',
            'short_description' => 'Mise en conformité des installations électriques.',
            'description' => 'Nous assurons l’audit et la mise en conformité des installations électriques selon les normes en vigueur pour garantir votre sécurité et votre conformité réglementaire.',
            'image' => 'images/services/mise-conformite.webp',
        ],
        [
            'title' => 'Gros œuvre électrique',
            'icon' => 'assets/icons/services/screwdriver-wrench-solid.svg',
            'short_description' => 'Installation des infrastructures électriques de base.',
            'description' => 'Nous réalisons le câblage et la mise en place des infrastructures électriques dès la phase de construction ou de rénovation pour assurer une distribution optimale de l’électricité.',
            'image' => 'images/services/electrical-construction-site.webp',
        ],
        [
            'title' => 'Autres services électriques',
            'icon' => 'assets/icons/services/plus-solid-full.svg',
            'short_description' => 'Interventions diverses selon vos besoins.',
            'description' => 'Nous intervenons pour tous types d’installations, dépannages ou améliorations électriques. Contactez-nous pour un devis personnalisé adapté à votre situation.',
            'image' => 'images/services/autres.webp',
        ],
    ];


        foreach ($services as $service) {
            Service::updateOrCreate(
                ['title' => $service['title']],
                $service
            );
        }
    }
}
