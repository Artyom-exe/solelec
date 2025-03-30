<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerReview extends Model
{
    protected $fillable = [
        'source_id',
        'author',
        'comment',
        'note',
        'date_publication'
    ];
}
