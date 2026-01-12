<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'iso_code',
        'phone_code',
        'id_format',
        'phone_format',
        'phone_min_length',
        'phone_max_length',
        'id_min_length',
        'id_max_length',
    ];
}
