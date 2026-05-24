<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $orderCounts = [
            'john@example.com' => 5,
            'jane@example.com' => 3,
            'alice@example.com' => 0,
            'admin@example.com' => 1,
        ];

        foreach ($orderCounts as $email => $count) {
            $user = User::where('email', $email)->first();

            if (! $user) {
                continue;
            }

            for ($i = 0; $i < $count; $i++) {
                Order::create(['user_id' => $user->id]);
            }
        }
    }
}
