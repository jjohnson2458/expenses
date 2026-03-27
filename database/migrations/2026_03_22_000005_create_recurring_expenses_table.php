<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('recurring_expenses')) {
            return;
        }

        Schema::create('recurring_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->enum('type', ['debit', 'credit'])->default('debit');
            $table->string('description', 500);
            $table->decimal('amount', 12, 2);
            $table->string('vendor', 255)->nullable();
            $table->integer('day_of_month')->default(1);
            $table->boolean('is_active')->default(true);
            $table->date('last_processed')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_expenses');
    }
};
