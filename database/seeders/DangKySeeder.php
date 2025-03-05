<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DangKySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $startUserId = 7;
        $endUserId = 316;
        $days = 15;
        $dateStart = now()->subDays($days);

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $dateStart->copy()->addDays($i)->format('Y-m-d');
            $data = [];

            for ($userId = $startUserId; $userId <= $endUserId; $userId++) {
                $data[] = [
                    'user_id' => $userId,
                    'ip_user' => $this->generateRandomIp(),
                    'oto_muc_3' => rand(10, 500),
                    'xe_may_muc_3' => rand(10, 500),
                    'oto_muc_4' => rand(10, 500),
                    'xe_may_muc_4' => rand(10, 500),
                    'xe_may_dien_muc_3' => rand(10, 500),
                    'xe_may_dien_muc_4' => rand(10, 500),
                    'created_at' => $currentDate . ' ' . now()->format('H:i:s'),
                    'updated_at' => $currentDate . ' ' . now()->format('H:i:s'),
                ];
            }

            DB::table('dang_kies')->insert($data);
        }
    }

    private function generateRandomIp()
    {
        return implode('.', [rand(1, 255), rand(0, 255), rand(0, 255), rand(1, 255)]);
    }
}
