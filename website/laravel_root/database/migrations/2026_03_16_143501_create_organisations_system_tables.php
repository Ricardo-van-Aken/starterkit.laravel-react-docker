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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->timestamps();
        });

        // Pivot table for tenants and users
        Schema::create('tenant_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id']);
        });

        Schema::create('organisation_units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('organisation_units')->nullOnDelete();
            $table->string('name');
            $table->string('type')->nullable(); // department, team, etc
            $table->timestamps();
        });

        // Pivot table for organisation units and users
        Schema::create('organisation_unit_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['organisation_unit_id', 'user_id']);
        });

        // Closure table for organisation units (for efficient querying of hierarchical data)
        Schema::create('organisation_unit_closure', function (Blueprint $table) {
            $table->foreignId('ancestor_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->foreignId('descendant_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->unsignedInteger('depth');
            $table->primary(['ancestor_id', 'descendant_id']);
            $table->index(['descendant_id', 'ancestor_id']);
        });

        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organisation_unit_user');
        Schema::dropIfExists('tenant_user');
        Schema::dropIfExists('resource_shares');
        Schema::dropIfExists('resources');
        Schema::dropIfExists('organisation_unit_closure');
        Schema::dropIfExists('organisation_units');
        Schema::dropIfExists('tenants');
    }
};
