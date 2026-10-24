<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The documents a procurement team can ask for (ISO certificates, insurance, HSE policy).
        Schema::create('compliance_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category', 20);
            $table->string('description')->nullable();
            $table->string('path')->nullable();
            $table->string('original_name')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->date('issued_on')->nullable();
            $table->date('expires_on')->nullable();
            $table->boolean('is_listed')->default(true);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('vendor_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('status', 10)->default('pending')->index();
            $table->string('company');
            $table->string('contact_name', 120);
            $table->string('job_title', 120)->nullable();
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('purpose', 20);
            $table->string('tender_reference', 120)->nullable();
            $table->text('message')->nullable();
            // Random token in the signed link, so a link can be revoked without touching the signature key.
            $table->string('access_token', 64)->nullable()->unique();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->string('decision_note')->nullable();
            $table->timestamps();
        });

        // Explicit key names: MySQL caps identifiers at 64 characters and the generated ones are longer.
        Schema::create('compliance_document_vendor_request', function (Blueprint $table) {
            $table->foreignId('vendor_request_id')->constrained(indexName: 'cdvr_vendor_request_id_foreign')->cascadeOnDelete();
            $table->foreignId('compliance_document_id')->constrained(indexName: 'cdvr_compliance_document_id_foreign')->cascadeOnDelete();
            $table->primary(['vendor_request_id', 'compliance_document_id']);
        });

        Schema::create('document_downloads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('compliance_document_id')->nullable()->constrained()->nullOnDelete();
            $table->string('document_title');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_downloads');
        Schema::dropIfExists('compliance_document_vendor_request');
        Schema::dropIfExists('vendor_requests');
        Schema::dropIfExists('compliance_documents');
    }
};
