<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $t) {
            if (! Schema::hasColumn('patients', 'portal_password')) {
                $t->string('portal_password', 255)->nullable()->after('email');
            }
            if (! Schema::hasColumn('patients', 'portal_last_login_at')) {
                $t->timestamp('portal_last_login_at')->nullable()->after('portal_password');
            }
            if (! Schema::hasColumn('patients', 'remember_token')) {
                $t->rememberToken()->after('portal_last_login_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $t) {
            foreach (['portal_password', 'portal_last_login_at', 'remember_token'] as $c) {
                if (Schema::hasColumn('patients', $c)) {
                    $t->dropColumn($c);
                }
            }
        });
    }
};
