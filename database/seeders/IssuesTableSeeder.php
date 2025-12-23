<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class IssuesTableSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $issues = [
            ['title' => 'Engine overheating'],
            ['title' => 'Oil leakage'],
            ['title' => 'Brake pad wear'],
            ['title' => 'Battery failure'],
            ['title' => 'Tire puncture'],
            ['title' => 'Wheel alignment issue'],
            ['title' => 'Suspension problem'],
            ['title' => 'Transmission issue'],
            ['title' => 'Air filter replacement'],
            ['title' => 'Coolant leakage'],
            ['title' => 'Spark plug failure'],
            ['title' => 'Clutch wear'],
            ['title' => 'Steering vibration'],
            ['title' => 'Exhaust system issue'],
            ['title' => 'AC not cooling properly'],
        ];

        foreach ($issues as $issue) {
            DB::table('issues')->insert([
                'title'      => $issue['title'],
                'is_active'  => true,
                'created_by' => 1, // system admin / default user
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
