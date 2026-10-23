<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('status', 16)->default('new')->index();
            $table->string('company');
            $table->string('contact_name', 120);
            $table->string('job_title', 120)->nullable();
            $table->string('email');
            $table->string('phone', 30);
            $table->string('industry', 20);
            $table->string('site_type', 20);
            $table->string('site_location');
            $table->unsignedSmallInteger('posts');
            $table->string('coverage', 12);
            $table->string('armed', 10);
            $table->date('start_date')->nullable();
            $table->string('contract_term', 20)->nullable();
            $table->text('details')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('staff_notes')->nullable();
            $table->decimal('value_estimate', 12, 2)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('proposal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_request_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_documents');
        Schema::dropIfExists('proposal_requests');
    }
};
