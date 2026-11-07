<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id(); // المفتاح الأساسي Story_ID
            $table->text('description');
            $table->date('date');
            $table->string('image_path', 255);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // الربط بالجدول الأساسي
            $table->timestamps();
        });
    }    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }

};
