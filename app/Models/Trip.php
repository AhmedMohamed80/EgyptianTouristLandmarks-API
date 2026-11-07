<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'from_location', 'to_location', 
        'trip_date', 'trip_status', 'offers', 'main_image','price'
    ];

    protected $casts = [
        'trip_status' => 'boolean',
        'offers'=>'float',
    ];

    public function getTripStatusAttribute($value)
    {
        return Carbon::parse($this->trip_date)->diffInDays(Carbon::now()) <= 1 ? 1 : 0;
    }

    // العلاقات
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    public function includes()
    {
        return $this->hasMany(TripInclude::class);
    }

    public function getMainImageAttribute($value)
    {
        return $value ? url($value) : null;
    }

    public function excludes()
    {
        return $this->hasMany(Exclude::class);
    }

    public function booking()
    {
        return $this->hasMany(booking::class, 'trip_id');
    }
}
