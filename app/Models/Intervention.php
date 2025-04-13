<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Intervention extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'date',
        'notes',
        'clients_id',
        'devis_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'clients_id');
    }

    public function devis()
    {
        return $this->belongsTo(Quote::class, 'devis_id');
    }

    public function images()
    {
        return $this->hasMany(InterventionImage::class);
    }
}
