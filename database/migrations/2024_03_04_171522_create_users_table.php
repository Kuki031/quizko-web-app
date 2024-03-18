<?php

use App\Models\Role;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 30)->unique()->nullable(false);
            $table->string('email')->unique()->nullable(false);
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_email_verified')->default(false);
            $table->boolean('is_account_active')->default(true);
            $table->string('password')->nullable(false);
            $table->string('api_token', 80)->unique()->nullable()->default(null);
            $table->boolean('is_in_team')->nullable()->default(false);
            $table->unsignedBigInteger('role_id')->nullable(false)->default(3);
            $table->string('confirm_email_token')->nullable();
            $table->string('forgot_password_token')->nullable();
            $table->timestamp('date_of_reg')->useCurrent();
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade')->onUpdate('cascade');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
