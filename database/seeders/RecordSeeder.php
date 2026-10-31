<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        DB::connection()->disableQueryLog();
        $this->command->info("Cleaning existing rows for a fresh 1 Crore target...");
        DB::table('records')->truncate();

        $totalRecords = 10000000;
        $chunkSize = 25000;
        $data = [];

        $streetNumbers = ['10', '25', '142', 'Block A', 'Suite 4', 'Plot 99', 'Building 12'];
        $streets = ['London Road', 'Baker Street', 'MG Road', 'Sunset Blvd', 'Victoria Street', 'High Street'];
        $cities = ['Manchester', 'London', 'Delhi', 'New York', 'Sydney', 'Mumbai', 'Birmingham'];
        $countries = ['UK', 'India', 'USA', 'Australia', 'Canada'];

        $cNum = count($streetNumbers) - 1;
        $cStr = count($streets) - 1;
        $cCit = count($cities) - 1;
        $cCou = count($countries) - 1;

        $this->command->info("Seeding exact 10,000,000 records smoothly...");
        $bar = $this->command->getOutput()->createProgressBar($totalRecords / $chunkSize);
        $bar->start();
        DB::beginTransaction();

        for ($i = 1; $i <= $totalRecords; $i++) {
            $randomContent = $streetNumbers[mt_rand(0, $cNum)] . ' ' .
                $streets[mt_rand(0, $cStr)] . ', ' .
                $cities[mt_rand(0, $cCit)] . ' ' .
                $countries[mt_rand(0, $cCou)];
            $data[] = [
                'content' => $randomContent
            ];

            if ($i % $chunkSize === 0) {
                DB::table('records')->insert($data);
                $data = [];
                $bar->advance();
            }
        }

        if (!empty($data)) {
            DB::table('records')->insert($data);
        }

        DB::commit();
        $bar->finish();
        $this->command->info("\n Successfully seeded exactly 10,000,000 professional rows!");
    }
}
