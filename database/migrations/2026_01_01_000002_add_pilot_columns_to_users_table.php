<?php

use Domain\User\Enums\UserStatus;
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
        Schema::table('users', function (Blueprint $table) {
            $table->string('vanity_id')->nullable()->unique()->after('id');
            $table->string('avatar')->nullable()->after('email');
            $table->string('country', 2)->nullable()->after('avatar');
            $table->string('timezone')->nullable()->after('country');
            $table->tinyInteger('status')->default(UserStatus::Pending->value)->after('timezone');
            $table->timestamp('last_login_at')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['vanity_id']);
            $table->dropColumn([
                'vanity_id',
                'avatar',
                'country',
                'timezone',
                'status',
                'last_login_at',
            ]);
        });
    }
};
