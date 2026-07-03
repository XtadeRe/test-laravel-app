<?php

namespace App\Console\Commands;

use App\Models\Sales;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class getSales extends Command
{

    protected $signature = 'get:sales {dateFrom?} {dateTo?}';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom') ?? now()->subDays(1)->format('Y-m-d');
        $dateTo = $this->argument('dateTo') ?? now()->format('Y-m-d');

        $this->info("Идёт загрузка sales с {$dateFrom} по {$dateTo}");

        $response = Http::get('http://109.73.206.144:6969/api/sales', [
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
                Sales::create([
                    'g_number' => $item['g_number'] ?? '',
                    'date' => $item['date'] ?? now()->format('Y-m-d'),
                    'last_change_date' => $item['last_change_date'] ?? now()->format('Y-m-d H:i:s'),
                    'supplier_article' => $item['supplier_article'] ?? '',
                    'tech_size' => $item['tech_size'] ?? '',
                    'barcode' => $item['barcode'] ?? '',
                    'total_price' => $item['total_price'] ?? 0,
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'is_supply' => $item['is_supply'] ?? false,
                    'is_realization' => $item['is_realization'] ?? false,
                    'promo_code_discount' => $item['promo_code_discount'] ?? 0,
                    'warehouse_name' => $item['warehouse_name'] ?? '',
                    'country_name' => $item['country_name'] ?? '',
                    'oblast_okrug_name' => $item['oblast_okrug_name'] ?? '',
                    'region_name' => $item['region_name'] ?? '',
                    'income_id' => $item['income_id'] ?? '',
                    'sale_id' => $item['sale_id'] ?? '',
                    'odid' => $item['odid'] ?? null,
                    'spp' => $item['spp'] ?? '',
                    'for_pay' => $item['for_pay'] ?? 0,
                    'finished_price' => $item['finished_price'] ?? 0,
                    'price_with_disc' => $item['price_with_disc'] ?? 0,
                    'nm_id' => $item['nm_id'] ?? '',
                    'subject' => $item['subject'] ?? '',
                    'category' => $item['category'] ?? '',
                    'brand' => $item['brand'] ?? '',
                    'is_storno' => $item['is_storno'] ?? false,
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка при сохранении продажи: ' . $e->getMessage(), [
                    'item' => $item
                ]);
                continue;
            }
        }

        $this->info('Данные успешно загружены');
    }
}
