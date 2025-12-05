<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         // Équipe #1 - Montréal
        $team1 = Team::create([
            'name'  => 'Équipe Montréal'
        ]);

        $team1->brokers()->createMany([
            [
                'name'  => 'Julie Tremblay',
                'email' => 'julie.tremblay@example.com'
            ],
            [
                'name'  => 'Marc Gagnon',
                'email' => 'marc.gagnon@example.com'
            ],
        ]);

        // Èquipe #2 - Québec
        $team2 = Team::create([
            'name'  => 'Équipe Québec'
        ]);

        $team2->brokers()->createMany([
            [
                'name'  => 'Sophie Lavoie',
                'email' => 'sophie.lavoie@example.com'
            ],
        ]);
    }
}
