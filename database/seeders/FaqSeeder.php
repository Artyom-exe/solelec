<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FaqSeeder extends Seeder
{
    public function run()
    {
        DB::table('faq')->truncate(); // vide la table

        DB::table('faq')->insert([
            [
                'question' => 'Quels services offrez-vous ?',
                'answer' => "Nous proposons des services d’installation de panneaux solaires, de mise en conformité électrique et de bornes de recharge pour véhicules électriques. Chaque service est conçu pour répondre aux besoins spécifiques de nos clients, qu’ils soient particuliers ou professionnels.",
                'display_order' => 1
            ],
            [
                'question' => 'Comment demander un devis ?',
                'answer' => "Pour demander un devis, cliquez sur le bouton 'Demander un devis' et remplissez le formulaire interactif. Vous pourrez choisir le service souhaité et fournir les détails de votre projet.",
                'display_order' => 2
            ],
            [
                'question' => 'Quels sont vos délais ?',
                'answer' => "Les délais d’intervention dépendent du type de service demandé et de la complexité du projet. Nous nous efforçons de vous fournir une estimation précise lors de votre demande de devis.",
                'display_order' => 3
            ],
            [
                'question' => 'Offrez-vous des garanties ?',
                'answer' => "Oui, nous offrons des garanties sur nos installations pour assurer votre tranquillité d’esprit. Nos services sont conformes aux normes en vigueur, garantissant ainsi la qualité et la fiabilité de nos travaux.",
                'display_order' => 4
            ],
            [
                'question' => 'Comment vous contacter ?',
                'answer' => "Vous pouvez nous contacter via le formulaire sur notre site ou en cliquant sur le bouton 'Contact'. Nous sommes disponibles pour répondre à toutes vos questions.",
                'display_order' => 5
            ],
        ]);
    }
}
