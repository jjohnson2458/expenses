<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaxDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedFederalBrackets();
        $this->seedStateSalesTax();
        $this->seedScheduleCMappings();
        $this->seedQuarterlyDueDates();
    }

    private function seedFederalBrackets(): void
    {
        // 2025 Federal Income Tax Brackets
        $year = 2025;
        $brackets = [
            'single' => [
                ['floor' => 0, 'ceiling' => 11925, 'rate' => 0.10, 'base' => 0, 'deduction' => 15000],
                ['floor' => 11925, 'ceiling' => 48475, 'rate' => 0.12, 'base' => 1192.50, 'deduction' => 15000],
                ['floor' => 48475, 'ceiling' => 103350, 'rate' => 0.22, 'base' => 5578.50, 'deduction' => 15000],
                ['floor' => 103350, 'ceiling' => 197300, 'rate' => 0.24, 'base' => 17651.00, 'deduction' => 15000],
                ['floor' => 197300, 'ceiling' => 250525, 'rate' => 0.32, 'base' => 40199.00, 'deduction' => 15000],
                ['floor' => 250525, 'ceiling' => 626350, 'rate' => 0.35, 'base' => 57231.00, 'deduction' => 15000],
                ['floor' => 626350, 'ceiling' => null, 'rate' => 0.37, 'base' => 188769.75, 'deduction' => 15000],
            ],
            'married_joint' => [
                ['floor' => 0, 'ceiling' => 23850, 'rate' => 0.10, 'base' => 0, 'deduction' => 30000],
                ['floor' => 23850, 'ceiling' => 96950, 'rate' => 0.12, 'base' => 2385.00, 'deduction' => 30000],
                ['floor' => 96950, 'ceiling' => 206700, 'rate' => 0.22, 'base' => 11157.00, 'deduction' => 30000],
                ['floor' => 206700, 'ceiling' => 394600, 'rate' => 0.24, 'base' => 35302.00, 'deduction' => 30000],
                ['floor' => 394600, 'ceiling' => 501050, 'rate' => 0.32, 'base' => 80398.00, 'deduction' => 30000],
                ['floor' => 501050, 'ceiling' => 751600, 'rate' => 0.35, 'base' => 114462.00, 'deduction' => 30000],
                ['floor' => 751600, 'ceiling' => null, 'rate' => 0.37, 'base' => 202154.50, 'deduction' => 30000],
            ],
            'head_of_household' => [
                ['floor' => 0, 'ceiling' => 17000, 'rate' => 0.10, 'base' => 0, 'deduction' => 22500],
                ['floor' => 17000, 'ceiling' => 64850, 'rate' => 0.12, 'base' => 1700.00, 'deduction' => 22500],
                ['floor' => 64850, 'ceiling' => 103350, 'rate' => 0.22, 'base' => 7442.00, 'deduction' => 22500],
                ['floor' => 103350, 'ceiling' => 197300, 'rate' => 0.24, 'base' => 16174.00, 'deduction' => 22500],
                ['floor' => 197300, 'ceiling' => 250500, 'rate' => 0.32, 'base' => 38722.00, 'deduction' => 22500],
                ['floor' => 250500, 'ceiling' => 626350, 'rate' => 0.35, 'base' => 55746.00, 'deduction' => 22500],
                ['floor' => 626350, 'ceiling' => null, 'rate' => 0.37, 'base' => 187293.50, 'deduction' => 22500],
            ],
        ];

        foreach ($brackets as $status => $rows) {
            foreach ($rows as $row) {
                DB::table('tax_rates')->insert([
                    'tax_year' => $year,
                    'jurisdiction' => 'federal',
                    'filing_status' => $status,
                    'bracket_floor' => $row['floor'],
                    'bracket_ceiling' => $row['ceiling'],
                    'rate' => $row['rate'],
                    'base_tax' => $row['base'],
                    'standard_deduction' => $row['deduction'],
                    'personal_exemption' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedStateSalesTax(): void
    {
        $states = [
            ['AL', 'Alabama', 0.0400, 0.0524, 0.0924],
            ['AK', 'Alaska', 0.0000, 0.0182, 0.0182],
            ['AZ', 'Arizona', 0.0560, 0.0271, 0.0831],
            ['AR', 'Arkansas', 0.0650, 0.0296, 0.0946],
            ['CA', 'California', 0.0725, 0.0157, 0.0882],
            ['CO', 'Colorado', 0.0290, 0.0489, 0.0779],
            ['CT', 'Connecticut', 0.0635, 0.0000, 0.0635],
            ['DE', 'Delaware', 0.0000, 0.0000, 0.0000],
            ['FL', 'Florida', 0.0600, 0.0105, 0.0705],
            ['GA', 'Georgia', 0.0400, 0.0340, 0.0740],
            ['HI', 'Hawaii', 0.0400, 0.0044, 0.0444],
            ['ID', 'Idaho', 0.0600, 0.0003, 0.0603],
            ['IL', 'Illinois', 0.0625, 0.0282, 0.0907],
            ['IN', 'Indiana', 0.0700, 0.0000, 0.0700],
            ['IA', 'Iowa', 0.0600, 0.0094, 0.0694],
            ['KS', 'Kansas', 0.0650, 0.0232, 0.0882],
            ['KY', 'Kentucky', 0.0600, 0.0000, 0.0600],
            ['LA', 'Louisiana', 0.0445, 0.0556, 0.1001],
            ['ME', 'Maine', 0.0550, 0.0000, 0.0550],
            ['MD', 'Maryland', 0.0600, 0.0000, 0.0600],
            ['MA', 'Massachusetts', 0.0625, 0.0000, 0.0625],
            ['MI', 'Michigan', 0.0600, 0.0000, 0.0600],
            ['MN', 'Minnesota', 0.0688, 0.0059, 0.0747],
            ['MS', 'Mississippi', 0.0700, 0.0007, 0.0707],
            ['MO', 'Missouri', 0.0423, 0.0397, 0.0820],
            ['MT', 'Montana', 0.0000, 0.0000, 0.0000],
            ['NE', 'Nebraska', 0.0550, 0.0194, 0.0744],
            ['NV', 'Nevada', 0.0685, 0.0137, 0.0822],
            ['NH', 'New Hampshire', 0.0000, 0.0000, 0.0000],
            ['NJ', 'New Jersey', 0.0663, 0.0003, 0.0666],
            ['NM', 'New Mexico', 0.0513, 0.0269, 0.0782],
            ['NY', 'New York', 0.0400, 0.0452, 0.0852],
            ['NC', 'North Carolina', 0.0475, 0.0222, 0.0697],
            ['ND', 'North Dakota', 0.0500, 0.0198, 0.0698],
            ['OH', 'Ohio', 0.0575, 0.0176, 0.0751],
            ['OK', 'Oklahoma', 0.0450, 0.0444, 0.0894],
            ['OR', 'Oregon', 0.0000, 0.0000, 0.0000],
            ['PA', 'Pennsylvania', 0.0600, 0.0034, 0.0634],
            ['RI', 'Rhode Island', 0.0700, 0.0000, 0.0700],
            ['SC', 'South Carolina', 0.0600, 0.0175, 0.0775],
            ['SD', 'South Dakota', 0.0420, 0.0191, 0.0611],
            ['TN', 'Tennessee', 0.0700, 0.0255, 0.0955],
            ['TX', 'Texas', 0.0625, 0.0194, 0.0819],
            ['UT', 'Utah', 0.0485, 0.0114, 0.0599],
            ['VT', 'Vermont', 0.0600, 0.0018, 0.0618],
            ['VA', 'Virginia', 0.0430, 0.0035, 0.0465],
            ['WA', 'Washington', 0.0650, 0.0284, 0.0934],
            ['WV', 'West Virginia', 0.0600, 0.0039, 0.0639],
            ['WI', 'Wisconsin', 0.0500, 0.0044, 0.0544],
            ['WY', 'Wyoming', 0.0400, 0.0133, 0.0533],
            ['DC', 'District of Columbia', 0.0600, 0.0000, 0.0600],
        ];

        foreach ($states as $s) {
            DB::table('state_sales_tax')->insert([
                'state_code' => $s[0],
                'state_name' => $s[1],
                'base_rate' => $s[2],
                'avg_local_rate' => $s[3],
                'avg_combined_rate' => $s[4],
                'effective_date' => '2025-01-01',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedScheduleCMappings(): void
    {
        // Map existing categories to IRS Schedule C lines
        $mappings = [
            'Advertising' => ['line' => '8', 'desc' => 'Advertising'],
            'Auto' => ['line' => '9', 'desc' => 'Car and truck expenses'],
            'Vehicle' => ['line' => '9', 'desc' => 'Car and truck expenses'],
            'Travel' => ['line' => '24a', 'desc' => 'Travel'],
            'Meals' => ['line' => '24b', 'desc' => 'Deductible meals'],
            'Insurance' => ['line' => '15', 'desc' => 'Insurance (other than health)'],
            'Legal' => ['line' => '17', 'desc' => 'Legal and professional services'],
            'Office' => ['line' => '18', 'desc' => 'Office expense'],
            'Rent' => ['line' => '20b', 'desc' => 'Rent or lease - Other business property'],
            'Supplies' => ['line' => '22', 'desc' => 'Supplies'],
            'Taxes' => ['line' => '23', 'desc' => 'Taxes and licenses'],
            'Utilities' => ['line' => '25', 'desc' => 'Utilities'],
            'Wages' => ['line' => '26', 'desc' => 'Wages'],
            'Repairs' => ['line' => '21', 'desc' => 'Repairs and maintenance'],
            'Interest' => ['line' => '16b', 'desc' => 'Interest - Other'],
            'Commission' => ['line' => '10', 'desc' => 'Commissions and fees'],
            'Contract' => ['line' => '11', 'desc' => 'Contract labor'],
            'Depreciation' => ['line' => '13', 'desc' => 'Depreciation and section 179'],
            'Employee' => ['line' => '14', 'desc' => 'Employee benefit programs'],
            'Pension' => ['line' => '19', 'desc' => 'Pension and profit-sharing plans'],
        ];

        $categories = DB::table('expense_categories')->get();

        foreach ($categories as $cat) {
            foreach ($mappings as $keyword => $mapping) {
                if (stripos($cat->name, $keyword) !== false) {
                    DB::table('schedule_c_mappings')->updateOrInsert(
                        ['category_id' => $cat->id],
                        [
                            'schedule_c_line' => $mapping['line'],
                            'schedule_c_description' => $mapping['desc'],
                            'irs_form' => 'Schedule C',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                    break;
                }
            }
        }
    }

    private function seedQuarterlyDueDates(): void
    {
        // 2026 estimated tax due dates
        $quarters = [
            ['year' => 2026, 'quarter' => 1, 'due' => '2026-04-15'],
            ['year' => 2026, 'quarter' => 2, 'due' => '2026-06-15'],
            ['year' => 2026, 'quarter' => 3, 'due' => '2026-09-15'],
            ['year' => 2026, 'quarter' => 4, 'due' => '2027-01-15'],
        ];

        // Seed for any existing users
        $users = DB::table('users')->pluck('id');

        foreach ($users as $userId) {
            foreach ($quarters as $q) {
                DB::table('quarterly_estimates')->updateOrInsert(
                    ['user_id' => $userId, 'tax_year' => $q['year'], 'quarter' => $q['quarter']],
                    [
                        'due_date' => $q['due'],
                        'estimated_amount' => 0,
                        'paid_amount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }
}
