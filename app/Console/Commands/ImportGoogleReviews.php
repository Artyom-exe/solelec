<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\CustomerReview;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Attributes\AsScheduled;


#[AsScheduled('everyMinute')]
class ImportGoogleReviews extends Command
{
    protected $signature = 'import:google-reviews';
    protected $description = 'Importer les avis Google Places dans la base de données';

    public function handle()
    {
        dd('🚀 La commande a bien été exécutée !');
        $placeId = config('services.google_places.place_id');
        $apiKey = config('services.google_places.key');

        $url = "https://maps.googleapis.com/maps/api/place/details/json?place_id={$placeId}&fields=reviews&language=fr&key={$apiKey}";
        $response = Http::get($url)->json();

        $reviews = $response['result']['reviews'] ?? [];

        foreach ($reviews as $review) {

            if (empty($review['text'])) {
                continue;
            }

            CustomerReview::updateOrCreate(
                [
                    'author' => $review['author_name'],
                    'comment' => $review['text'],
                ],
                [
                    'note' => $review['rating'],
                    'date_publication' => Carbon::createFromTimestamp($review['time']),
                    'source_id' => 'google',
                ]
            );
        }

        $this->info('✅ Avis Google importés avec succès !');
    }
}
