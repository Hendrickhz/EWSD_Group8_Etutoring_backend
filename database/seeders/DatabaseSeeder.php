<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            MeetingSeeder::class,
            BlogSeeder::class,
            DocumentSeeder::class,
            MessageSeeder::class
        ]);

        User::all()->each(function ($user){
            if($user->last_active_at){
                $oneHourBefore = Carbon::parse($user->last_active_at)->subHour();
                $user->update(['last_login' => $oneHourBefore]);
            }
        });
    }
}
