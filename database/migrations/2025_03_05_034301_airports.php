<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('Item_ID'); // يجب أن يكون نفس نوع id في items
            $table->foreign('Item_ID')->references('Item_ID')->on('items')->onDelete('cascade');
            $table->string('website_link', 255)->nullable();
            $table->text('additional_info')->nullable();
        
        });
    }

    public function down()
    {
        Schema::dropIfExists('airports');
    }
};

