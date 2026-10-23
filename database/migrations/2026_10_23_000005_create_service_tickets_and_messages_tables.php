<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Alarm service requests and fault reports from Sheriff Alarms customers.
        Schema::create('service_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->string('status', 12)->default('received')->index();
            $table->string('issue', 20);
            $table->boolean('urgent')->default(false);
            $table->string('name', 120);
            $table->string('email');
            $table->string('phone', 30);
            $table->string('account_number', 40)->nullable();
            $table->string('site_address');
            $table->text('description');
            $table->string('preferred_time', 20)->nullable();
            $table->date('visit_on')->nullable();
            // Shown to the customer on the status lookup; staff notes are not.
            $table->string('public_note')->nullable();
            $table->text('staff_notes')->nullable();
            $table->timestamp('status_changed_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->string('company')->nullable();
            $table->string('topic', 20);
            $table->text('body');
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
        Schema::dropIfExists('service_tickets');
    }
};
