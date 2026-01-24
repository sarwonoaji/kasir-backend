<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        Shift::create([
            'name' => 'Pagi',
            'start_time' => '07:00',
            'end_time' => '15:00',
        ]);

        Shift::create([
            'name' => 'Siang',
            'start_time' => '15:00',
            'end_time' => '23:00',
        ]);

        Shift::create([
            'name' => 'Malam',
            'start_time' => '23:00',
            'end_time' => '07:00',
        ]);
    }
}
