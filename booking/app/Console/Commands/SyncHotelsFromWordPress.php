<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Hotel;

class SyncHotelsFromWordPress extends Command
{
    protected $signature = 'sync:hotels';
    protected $description = 'Đồng bộ dữ liệu hotel từ WordPress';

    public function handle()
    {
        $token = config('services.wordpress.token'); // load từ .env

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get(config('services.wordpress.api_url') . '/wp-json/custom-api/v1/hotels');

        if ($response->failed()) {
            $this->error('Không lấy được dữ liệu từ WordPress');
            return Command::FAILURE;
        }

        $hotels = $response->json();

        foreach ($hotels as $hotel) {
            Hotel::updateOrCreate(
                ['wordpress_id' => $hotel['id']],
                [
                    'name' => $hotel['name'],
                    'address' => $hotel['address'],
                    'phone' => $hotel['phone'],
                    'email' => $hotel['email'],
                ]
            );
        }

        $this->info('Đã đồng bộ ' . count($hotels) . ' khách sạn');
        return Command::SUCCESS;
    }
}
