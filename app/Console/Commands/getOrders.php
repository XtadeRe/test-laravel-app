<?php

namespace App\Console\Commands;

use App\Models\Incomes;
use App\Models\Orders;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class getOrders extends Command
{

    protected $signature = 'get:orders {dateFrom?} {dateTo?}';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom') ?? now()->subDays(7)->format('Y-m-d');
        $dateTo = $this->argument('dateTo') ?? now()->format('Y-m-d');

        $this->info("Идёт загрузка orders с {$dateFrom} по {$dateTo}");

        $response = Http::get('http://109.73.206.144:6969/api/orders', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => 1,
            'key' => env('API_KEY'),
            'limit' => 100,
        ]);

        if (!$response->successful()) {
            $this->error("Произошла ошибка");
            return;
        }

        $data = $response->json();

        if (!isset($data['data']) || empty($data['data'])) {
            $this->info("Нет данных для загрузки. Либо укажите больший диапазон get:orders 2026-xx-xx 2026-xx-xx");
            return;
        }

        foreach ($data['data'] as $item) {
            try {
                Orders::create([
                    'g_number' => $item['g_number'] ?? '',
                    'date' => $item['date'] ?? now()->format('Y-m-d'),
                    'last_change_date' => $item['last_change_date'] ?? now()->format('Y-m-d H:i:s'),
                    'supplier_article' => $item['supplier_article'] ?? '',
                    'tech_size' => $item['tech_size'] ?? '',
                    'barcode' => $item['barcode'] ?? '',
                    'total_price' => $item['total_price'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'warehouse_name' => $item['warehouse_name'] ?? '',
                    'oblast' => $item['oblast'] ?? '',
                    'income_id' => $item['income_id'] ?? '',
                    'odid' => $item['odid'] ?? '',
                    'nm_id' => $item['nm_id'] ?? '',
                    'subject' => $item['subject'] ?? '',
                    'category' => $item['category'] ?? '',
                    'brand' => $item['brand'] ?? '',
                    'is_cancel' => (bool)($item['is_cancel'] ?? false),
                    'cancel_dt' => $item['cancel_dt'] ?? null,
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка при сохранении заказа: ' . $e->getMessage(), [
                    'item' => $item
                ]);
                continue;
            }
        }

        $this->info('Данные orders успешно загружены');
    }
}
