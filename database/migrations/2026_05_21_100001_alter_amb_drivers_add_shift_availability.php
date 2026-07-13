<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('amb_drivers', function (Blueprint $t) {
            $t->enum('shift', ['MORNING', 'EVENING', 'NIGHT'])->nullable()->after('license_expiry');
            $t->enum('availability', ['AVAILABLE', 'ASSIGNED', 'OFF_DUTY'])->default('AVAILABLE')->after('shift');
        });
    }

    public function down(): void
    {
        Schema::table('amb_drivers', function (Blueprint $t) {
            $t->dropColumn(['shift', 'availability']);
        });
    }
};
