<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class facilities extends Model
{
    use HasFactory;

    protected $fillable = ['hotel_id', 'facility_name'];

    public function Hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
