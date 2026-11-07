<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class facilities_airports extends Model
{
    use HasFactory;

    protected $fillable = ['airport_id', 'facility_name'];

    public function Airport()
    {
        return $this->belongsTo(Airport::class);
    }  
}
