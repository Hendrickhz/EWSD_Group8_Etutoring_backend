<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;

class DocumentSeeder extends Seeder
{

    public function run(): void
    {
        Document::factory()->count(50)->create();
    }
}