<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'description',
        'requested_date',
        'end_date',
        'status',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'quotes_services');
    }

    public function interventions()
    {
        return $this->hasMany(Intervention::class, 'devis_id');
    }
}
