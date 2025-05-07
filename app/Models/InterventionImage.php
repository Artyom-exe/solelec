<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterventionImage extends Model
{
    use HasFactory;

    protected $table = 'interventions_images';

    protected $fillable = [
        'url_image',
        'intervention_id',
    ];

    public function intervention()
    {
        return $this->belongsTo(Intervention::class);
    }
}
