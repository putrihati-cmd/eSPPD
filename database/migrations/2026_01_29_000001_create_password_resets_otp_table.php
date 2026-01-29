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
        // Create OTP password resets table
        Schema::create('password_resets_otp', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 18)->index();
            $table->string('token', 64);
            $table->string('otp', 64); // Hashed OTP
            $table->string('channel', 20); // email/whatsapp
            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);
            $table->timestamps();
        });

        // Add missing columns to users table if not exists
        if (!Schema::hasColumn('users', 'is_password_reset')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_password_reset')->default(false)->after('password');
            });
        }

        if (!Schema::hasColumn('users', 'last_login')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_login')->nullable()->after('is_password_reset');
            });
        }

        if (!Schema::hasColumn('users', 'phone_wa')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('phone_wa', 20)->nullable()->after('email');
            });
        }

        if (!Schema::hasColumn('users', 'last_password_reset')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('last_password_reset')->nullable()->after('last_login');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_resets_otp');
        
        // Remove added columns from users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_password_reset')) {
                $table->dropColumn('is_password_reset');
            }
            if (Schema::hasColumn('users', 'last_login')) {
                $table->dropColumn('last_login');
            }
            if (Schema::hasColumn('users', 'phone_wa')) {
                $table->dropColumn('phone_wa');
            }
            if (Schema::hasColumn('users', 'last_password_reset')) {
                $table->dropColumn('last_password_reset');
            }
        });
    }
};
