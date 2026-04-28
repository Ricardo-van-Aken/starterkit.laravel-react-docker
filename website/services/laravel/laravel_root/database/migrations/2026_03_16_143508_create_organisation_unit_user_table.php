<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pivot table for organisation units and users
        Schema::create('organisation_unit_user', function (Blueprint $table) {
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['organisation_unit_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_unit_user');
    }
};
