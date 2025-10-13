<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\DemoPropertySeeder;

class SeedDemoProperties extends Command
{
    // No Kernel registration needed in Laravel 11/12
    protected $signature = 'rent:seed-demo {count=20}';
    protected $description = 'Create demo properties with random amenities and internet images';

    public function handle()
    {
        $count = (int) $this->argument('count');
        $this->info("Seeding {$count} demo properties with images...");
        (new DemoPropertySeeder)->run($count);
        $this->info('Done!');
        return self::SUCCESS;
    }
}
