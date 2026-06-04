<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Patient Portal + Telemedicine + Package consumption (SRS §5.19, §5.25, §5.26). */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_portal_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('email')->nullable();
            $table->string('phone', 32);
            $table->string('password');
            $table->boolean('mfa_enabled')->default(false);
            $table->string('mfa_secret')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('locale', 8)->default('en');
            $table->rememberToken();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['phone']);
        });

        Schema::create('patient_family_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portal_user_id')->constrained('patient_portal_users')->cascadeOnDelete();
            $table->foreignId('linked_patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('relationship', 32);
            $table->enum('status', ['pending', 'verified', 'rejected', 'revoked'])->default('pending');
            $table->string('verification_token')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->unique(['portal_user_id', 'linked_patient_id']);
        });

        Schema::create('patient_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('category', 64)->nullable(); // doctor, nurse, food, cleanliness, billing
            $table->text('comments')->nullable();
            $table->json('extra')->nullable();
            $table->timestamps();
        });

        Schema::create('telemedicine_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->foreignId('appointment_id')->nullable();
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('doctor_id')->nullable();
            $table->string('meeting_provider', 32)->default('jitsi'); // jitsi, twilio, zoom, internal
            $table->string('meeting_url')->nullable();
            $table->string('meeting_id')->nullable();
            $table->string('access_token')->nullable();
            $table->timestamp('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->enum('status', ['scheduled', 'waiting', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->boolean('is_recorded')->default(false);
            $table->string('recording_path')->nullable();
            $table->boolean('consent_given')->default(false);
            $table->timestamps();
        });

        // Package consumption tracking (SRS §5.19).
        Schema::create('package_enrollments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('package_id'); // matches packages.id (int unsigned)
            $table->foreign('package_id')->references('id')->on('packages')->cascadeOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('encounter_id')->nullable()->constrained('encounters')->nullOnDelete();
            $table->string('enrollment_no', 64)->unique();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('agreed_price', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled', 'expired'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['patient_id', 'status']);
        });

        Schema::create('package_consumption_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_enrollment_id')->constrained('package_enrollments')->cascadeOnDelete();
            $table->foreignId('package_service_id')->nullable()->constrained('package_services')->nullOnDelete();
            $table->foreignId('service_catalog_id')->nullable()->constrained('service_catalogs')->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity_allowed', 12, 4)->default(0);
            $table->decimal('quantity_consumed', 12, 4)->default(0);
            $table->decimal('quantity_extras', 12, 4)->default(0);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();
            $table->index(['package_enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_consumption_entries');
        Schema::dropIfExists('package_enrollments');
        Schema::dropIfExists('telemedicine_sessions');
        Schema::dropIfExists('patient_feedback');
        Schema::dropIfExists('patient_family_links');
        Schema::dropIfExists('patient_portal_users');
    }
};
