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
        Schema::create('tenant_invitations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('status')->default(\App\Enums\TenantInvitationStatus::Pending->value);
            $table->string('accept_token', 64)->nullable()->unique();
            $table->string('decline_token', 64)->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            // Action makes sure only one pending invite exists per email per tenant at a time.
            $table->index(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_invitations');
    }
};
