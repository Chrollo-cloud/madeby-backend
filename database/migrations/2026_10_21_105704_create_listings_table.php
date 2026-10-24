<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('type', ['product', 'commission']);
            $table->string('title');
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('price')->nullable();
            $table->unsignedInteger('price_from')->nullable();
            $table->unsignedInteger('stock')->nullable();
            $table->unsignedInteger('commission_slots')->nullable();
            $table->string('delivery_time')->nullable();
            $table->decimal('rating', 2, 1)->default(0);
            $table->text('description');
            $table->string('image');
            $table->json('tags')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('listings');
    }
};
