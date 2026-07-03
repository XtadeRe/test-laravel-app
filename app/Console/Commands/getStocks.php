<?php

namespace App\Console\Commands;

use App\Models\Stocks;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class getStocks extends Command
{

    protected $signature = 'get:stocks {dateFrom?} {dateTo?}';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom') ?? now()->format('Y-m-d');
        $dateTo = $this->argument('dateTo') ?? now()->format('Y-m-d');

        $this->info("Идёт загрузка stocks с {$dateFrom} по {$dateTo}");

        $response = Http::get('http://109.73.206.144:6969/api/stocks', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => 1,
            'key' => env('API_KEY'),
            'limit' => 100,
        ]);

        if (!$response->successful()) {
            $this->info("Произошла ошибка");
            return;
        }

        $responseData = $response->json();

        if (!isset($responseData['data']) || empty($responseData['data'])) {
            $this->info("Данные не найдены");
            return;
        }

        $data = $responseData['data'];

        foreach ($data as $item) {
            try {
                Stocks::create([
                    'date' => $item['date'] ?? now()->format('Y-m-d'),
                    'last_change_date' => $item['last_change_date'] ?? now()->format('Y-m-d H:i:s'),
                    'supplier_article' => $item['supplier_article'] ?? '',
                    'tech_size' => $item['tech_size'] ?? '',
                    'barcode' => $item['barcode'] ?? '',
                    'quantity' => $item['quantity'] ?? 0,
                    'warehouse_name' => $item['warehouse_name'] ?? '',
                    'nm_id' => $item['nm_id'] ?? '',
                    'is_supply' => $item['is_supply'] ?? 0,
                    'is_realization' => $item['is_realization'] ?? 0,
                    'quantity_full' => $item['quantity_full'] ?? 0,
                    'in_way_to_client' => $item['in_way_to_client'] ?? 0,
                    'in_way_from_client' => $item['in_way_from_client'] ?? 0,
                    'subject' => $item['subject'] ?? '',
                    'category' => $item['category'] ?? '',
                    'brand' => $item['brand'] ?? '',
                    'sc_code' => $item['sc_code'] ?? 0,
                    'price' => $item['price'] ?? 0,
                    'discount' => $item['discount'] ?? 0,
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка при сохранении остатка: ' . $e->getMessage(), [
                    'item' => $item
                ]);
                continue;
            }
        }

        $this->info('Данные успешно загружены');
    }
}
