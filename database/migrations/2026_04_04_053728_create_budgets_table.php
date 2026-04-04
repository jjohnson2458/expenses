<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('category_id');
            $table->decimal('amount', 12, 2);
            $table->char('budget_month', 7); // YYYY-MM
            $table->tinyInteger('alert_75_sent')->default(0);
            $table->tinyInteger('alert_90_sent')->default(0);
            $table->tinyInteger('alert_100_sent')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('expense_categories')->onDelete('cascade');
            $table->unique(['user_id', 'category_id', 'budget_month']);
            $table->index('budget_month');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
