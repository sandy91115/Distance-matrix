<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_settings', function (Blueprint $table) {
             $table->id();
             $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->boolean('is_encrypted')->default(false);
            $table->timestamps();
        });
        
         Schema::create('route_cache', function (Blueprint $table) {
            $table->id();
            $table->string('origin_hash');
            $table->string('destination_hash');
            $table->json('route_data');
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['origin_hash', 'destination_hash']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_settings');
         Schema::dropIfExists('route_cache');
    }
};
