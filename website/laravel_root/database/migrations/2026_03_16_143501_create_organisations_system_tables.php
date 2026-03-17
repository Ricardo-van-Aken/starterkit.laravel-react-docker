<?php

use App\Enums\OrganisationUnitType;
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
            $table->text('description')->nullable();
            $table->enum('type', ['branch', 'department', 'team', 'other']);
            $table->timestamps();
        });

        Schema::create('branch_units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('department_units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('team_units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('other_units', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
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

        Schema::create('iot_device_examples', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->string('type');
            $table->string('device_id')->unique();
            $table->string('device_secret');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('customer_examples', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('organisation_unit_id')->constrained('organisation_units')->cascadeOnDelete();
            $table->string('type')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_units');
        Schema::dropIfExists('department_units');
        Schema::dropIfExists('branch_units');
        Schema::dropIfExists('organisation_unit_user');
        Schema::dropIfExists('tenant_user');
        Schema::dropIfExists('resource_shares');
        Schema::dropIfExists('iot_device_examples');
        Schema::dropIfExists('customer_examples');
        Schema::dropIfExists('organisation_unit_closure');
        Schema::dropIfExists('organisation_units');
        Schema::dropIfExists('tenants');
    }
};
