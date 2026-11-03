<?php
namespace Database\Seeders; use Illuminate\Database\Seeder;
// Only reference/default data. NO fake user history, NO seeded users with personal records.
// User settings & prayer settings are auto-created on user registration via User::booted().
class DatabaseSeeder extends Seeder { public function run(): void { // Intentionally empty: defaults live in migrations & model boot logic.
 } }
