<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id('photo_id');
            $table->unsignedBigInteger('Item_ID'); // يجب أن يكون نفس نوع id في items
            $table->foreign('Item_ID')->references('Item_ID')->on('items')->onDelete('cascade');
            $table->string('image_path', 255);
            $table->timestamps();
        });
    }
    public function down()
    {
        Schema::dropIfExists('photos');
    }
};
