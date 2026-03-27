<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('users')->count() === 0) {
            DB::table('users')->insert([
                'name' => 'J.J. Johnson',
                'email' => 'email4johnson@gmail.com',
                'password' => Hash::make('24AdaPlace'),
                'role' => 'admin',
                'lang' => 'en',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (DB::table('expense_categories')->count() === 0) {
            $categories = [
                ['name' => 'Advertising', 'name_es' => 'Publicidad', 'color' => '#e74a3b', 'icon' => 'bi-megaphone'],
                ['name' => 'Car & Truck', 'name_es' => 'Auto y Camión', 'color' => '#fd7e14', 'icon' => 'bi-truck'],
                ['name' => 'Commissions & Fees', 'name_es' => 'Comisiones y Tarifas', 'color' => '#f6c23e', 'icon' => 'bi-percent'],
                ['name' => 'Contract Labor', 'name_es' => 'Mano de Obra Contratada', 'color' => '#20c9a6', 'icon' => 'bi-people'],
                ['name' => 'Insurance', 'name_es' => 'Seguro', 'color' => '#36b9cc', 'icon' => 'bi-shield-check'],
                ['name' => 'Interest', 'name_es' => 'Intereses', 'color' => '#4e73df', 'icon' => 'bi-bank'],
                ['name' => 'Legal & Professional', 'name_es' => 'Legal y Profesional', 'color' => '#6f42c1', 'icon' => 'bi-briefcase'],
                ['name' => 'Office Expense', 'name_es' => 'Gastos de Oficina', 'color' => '#858796', 'icon' => 'bi-building'],
                ['name' => 'Rent or Lease', 'name_es' => 'Alquiler o Arrendamiento', 'color' => '#1cc88a', 'icon' => 'bi-house'],
                ['name' => 'Repairs & Maintenance', 'name_es' => 'Reparaciones y Mantenimiento', 'color' => '#e74a3b', 'icon' => 'bi-wrench'],
                ['name' => 'Supplies', 'name_es' => 'Suministros', 'color' => '#fd7e14', 'icon' => 'bi-box'],
                ['name' => 'Taxes & Licenses', 'name_es' => 'Impuestos y Licencias', 'color' => '#f6c23e', 'icon' => 'bi-file-earmark-text'],
                ['name' => 'Travel', 'name_es' => 'Viajes', 'color' => '#36b9cc', 'icon' => 'bi-airplane'],
                ['name' => 'Meals', 'name_es' => 'Comidas', 'color' => '#4e73df', 'icon' => 'bi-cup-hot'],
                ['name' => 'Utilities', 'name_es' => 'Servicios Públicos', 'color' => '#6f42c1', 'icon' => 'bi-lightning'],
                ['name' => 'Other Expenses', 'name_es' => 'Otros Gastos', 'color' => '#858796', 'icon' => 'bi-three-dots'],
            ];

            foreach ($categories as $i => $cat) {
                DB::table('expense_categories')->insert(array_merge($cat, [
                    'sort_order' => $i,
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }
}
