<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Federal and state income tax brackets
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('tax_year');
            $table->string('jurisdiction', 10); // 'federal' or state code e.g. 'NY'
            $table->string('filing_status', 30); // single, married_joint, married_separate, head_of_household
            $table->decimal('bracket_floor', 12, 2);
            $table->decimal('bracket_ceiling', 12, 2)->nullable(); // null = no cap
            $table->decimal('rate', 8, 6); // e.g. 0.220000 for 22%
            $table->decimal('base_tax', 12, 2)->default(0); // cumulative tax from lower brackets
            $table->decimal('standard_deduction', 12, 2)->default(0);
            $table->decimal('personal_exemption', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['tax_year', 'jurisdiction', 'filing_status']);
        });

        // State sales tax rates
        Schema::create('state_sales_tax', function (Blueprint $table) {
            $table->id();
            $table->string('state_code', 2);
            $table->string('state_name', 50);
            $table->decimal('base_rate', 6, 4); // e.g. 0.0625 for 6.25%
            $table->decimal('avg_local_rate', 6, 4)->default(0);
            $table->decimal('avg_combined_rate', 6, 4)->default(0);
            $table->date('effective_date');
            $table->timestamps();

            $table->unique('state_code');
        });

        // IRS Schedule C line mapping for expense categories
        Schema::create('schedule_c_mappings', function (Blueprint $table) {
            $table->id();
            $table->integer('category_id');
            $table->foreign('category_id')->references('id')->on('expense_categories')->cascadeOnDelete();
            $table->string('schedule_c_line', 10); // e.g. '17', '18', '24a'
            $table->string('schedule_c_description', 100); // e.g. 'Legal and professional services'
            $table->string('irs_form', 20)->default('Schedule C'); // Schedule C, Schedule SE, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('category_id');
        });

        // User tax profile
        Schema::create('tax_profiles', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('filing_status', 30)->default('single');
            $table->string('state', 2)->nullable();
            $table->string('business_entity', 20)->default('sole_prop'); // sole_prop, llc, s_corp, c_corp
            $table->string('business_name', 255)->nullable();
            $table->string('ein', 20)->nullable(); // Employer Identification Number
            $table->smallInteger('fiscal_year_start')->default(1); // month (1=Jan)
            $table->boolean('track_mileage')->default(false);
            $table->boolean('home_office')->default(false);
            $table->decimal('home_office_sqft', 8, 2)->nullable();
            $table->decimal('home_total_sqft', 8, 2)->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        // Mileage log
        Schema::create('mileage_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->date('trip_date');
            $table->string('start_location', 255);
            $table->string('end_location', 255);
            $table->string('business_purpose', 500);
            $table->decimal('miles', 8, 1);
            $table->decimal('irs_rate', 6, 4)->default(0.7000); // 2025: $0.70/mile
            $table->decimal('deduction_amount', 10, 2)->default(0);
            $table->boolean('round_trip')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'trip_date']);
        });

        // Quarterly estimated tax tracking
        Schema::create('quarterly_estimates', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->smallInteger('tax_year');
            $table->tinyInteger('quarter'); // 1-4
            $table->date('due_date');
            $table->decimal('estimated_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->string('confirmation_number', 50)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tax_year', 'quarter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarterly_estimates');
        Schema::dropIfExists('mileage_logs');
        Schema::dropIfExists('tax_profiles');
        Schema::dropIfExists('schedule_c_mappings');
        Schema::dropIfExists('state_sales_tax');
        Schema::dropIfExists('tax_rates');
    }
};
