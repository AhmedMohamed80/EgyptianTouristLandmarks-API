<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Landmark extends Model
{
    use HasFactory;

    protected $table = 'landmarks';
    protected $primaryKey = 'id'; 
    public $timestamps = true;

    protected $fillable = [
        'Item_ID',
        'name',
        'description',
        'city',
        'location',
        'image_path',
        'type',
        'open_time',
        'close_time',
        'egyptian_adult_price',
        'egyptian_kid_price',
        'egyptian_student_price',
        'foreigner_adult_price',
        'foreigner_kid_price',
        'foreigner_student_price',
    ];

    protected $casts = [
        'open_time' => 'datetime:H:i:s',
        'close_time' => 'datetime:H:i:s',
        'type' => 'string',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class, 'Item_ID', 'Item_ID');
    }

    public function reviews()
    {
        return $this->hasManyThrough(
            Review::class,
            Item::class,  
            'Item_ID',    
            'Item_ID',   
            'Item_ID',   
            'Item_ID'   
        );
    }
    
}
