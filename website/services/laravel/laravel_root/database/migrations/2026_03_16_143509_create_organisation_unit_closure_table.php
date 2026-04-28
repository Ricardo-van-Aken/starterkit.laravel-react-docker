<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Closure table for organisation units (for efficient querying of hierarchical data)
        Schema::create('organisation_unit_closure', function (Blueprint $table) {
            $table->foreignId('ancestor_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->foreignId('descendant_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->unsignedInteger('depth');
            $table->primary(['ancestor_id', 'descendant_id']);
            $table->index(['descendant_id', 'ancestor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisation_unit_closure');
    }
};
