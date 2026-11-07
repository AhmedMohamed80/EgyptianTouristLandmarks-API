<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;
    protected $table = 'photos';
    protected $primaryKey = 'Item_ID'; 
    public $incrementing = false;
    protected $fillable = ['Item_ID', 'image_path','trip_id'];
    public $timestamps = false;
    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'Item_ID');
    }
}
