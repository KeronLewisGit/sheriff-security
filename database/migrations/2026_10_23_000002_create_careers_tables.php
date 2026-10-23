<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('department', 40)->default('Operations');
            $table->string('location')->default('Georgetown');
            $table->string('employment_type', 20)->default('full_time');
            $table->string('status', 10)->default('draft')->index();
            $table->unsignedSmallInteger('openings')->default(1);
            $table->text('summary');
            $table->text('duties')->nullable();
            $table->text('requirements')->nullable();
            $table->date('closes_on')->nullable();
            $table->timestamps();
        });

        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('job_posting_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 12)->default('new')->index();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('phone', 30);
            $table->string('phone_digits', 20)->nullable()->index();
            $table->string('email')->nullable();
            $table->string('region', 40);
            $table->string('experience', 12);
            $table->boolean('armed_licence')->default(false);
            $table->boolean('offshore_ready')->default(false);
            $table->text('message')->nullable();
            $table->text('staff_notes')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        // Police clearance, ID, certificates and CVs. Files live on the private disk.
        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('path');
            $table->string('original_name');
            $table->unsignedInteger('size');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('job_postings');
    }
};
