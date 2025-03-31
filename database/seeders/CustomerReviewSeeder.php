<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerReview;
use Carbon\Carbon;

class CustomerReviewSeeder extends Seeder
{
    public function run(): void
    {
        CustomerReview::updateOrCreate([
            'author' => 'iKenzo24',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-03-31'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Eoline Savary',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'baptiste meaux',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'alexandre mouton',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Thomas Lambot',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'laurence jacquemin',
            'comment' => "Super travail ! Qualitatif, sympathique, ponctuel ! Je recommande",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-11-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Mallaury Devillers',
            'comment' => "Beau travail ! Merci",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-12-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Boomgaert Jonathan',
            'comment' => "Travail impeccable dans les temps! Le tout à prix imbattable, je recommande!",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-03-31'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Damien Drosson',
            'comment' => "J'ai fait appel pour régler quelques problèmes sur mon installation électrique extérieure. [...] Merci Solelec et bonne chance pour le lancement de votre entreprise !",
        ], [
            'note' => 4,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Bennafla Aïda',
            'comment' => "Travail impeccable et de bon conseil (j'ai eu affaire à Nicolas pour l’éclairage de ma cuisine)",
        ], [
            'note' => 4,
            'date_publication' => Carbon::parse('2024-03-31'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Savinen Gosselain',
            'comment' => "Très content du service et un personnel sympathique et travailleur! Je recommande :)",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'catherine de vogelaere',
            'comment' => "Du beau et bon travail ! Je vous recommande SOLELEC !",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-03-31'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Shannon Crape',
            'comment' => "Très bonne expérience, je recommande cette entreprise à mes proches !",
        ], [
            'note' => 4,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Nicolas Wyns',
            'comment' => "Je recommande, Nicolas est très pro et connaît bien son métier !",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-03-31'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Yeganeh Salehi',
            'comment' => "Service parfait. Je recommande :-)",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Xavier Simons',
            'comment' => "Fiabilité et professionnalisme caractérise parfaitement Solelec !",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Robin Goudelouf',
            'comment' => "Électricien compétent que je recommande. Efficace et ponctuel.",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Gabriel Kanyanzira',
            'comment' => "Service de qualité",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2023-04-01'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Clara Nuten',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2025-01-30'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'kevin Kevin',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-03-31'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'simon gosselain',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-03-31'),
            'source_id' => 'google',
        ]);

        CustomerReview::updateOrCreate([
            'author' => 'Kevin Bosman',
            'comment' => "",
        ], [
            'note' => 5,
            'date_publication' => Carbon::parse('2024-03-31'),
            'source_id' => 'google',
        ]);
    }
}
