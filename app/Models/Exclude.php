<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exclude extends Model
{
    use HasFactory;

    protected $fillable = ['trip_id', 'point'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
