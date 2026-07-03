<?php

namespace App\Console\Commands;

use App\Models\Incomes;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class getIncomes extends Command
{

    protected $signature = 'get:incomes {dateFrom?} {dateTo?}';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom') ?? now()->subDays(7)->format('Y-m-d');
        $dateTo = $this->argument('dateTo') ?? now()->subDays(2)->format('Y-m-d');

        $this->info("Идёт загрузка incomes с {$dateFrom} по {$dateTo}");

        $response = Http::get('http://109.73.206.144:6969/api/incomes', [
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
            $this->info("Нет данных для загрузки. Введите команду в таком формате желательно за прошлый год get:incomes 2025-xx-xx 2025-xx-xx");
            return;
        }

        foreach ($data['data'] as $item) {
            try {
                Incomes::create([
                    'income_id' => $item['income_id'] ?? '',
                    'number' => $item['number'] ?? '',
                    'date' => $item['date'] ?? null,
                    'last_change_date' => $item['last_change_date'] ?? null,
                    'supplier_article' => $item['supplier_article'] ?? '',
                    'tech_size' => $item['tech_size'] ?? '',
                    'barcode' => $item['barcode'] ?? '',
                    'quantity' => $item['quantity'] ?? 0,
                    'total_price' => $item['total_price'] ?? 0,
                    'date_close' => $item['date_close'] ?? null,
                    'warehouse_name' => $item['warehouse_name'] ?? '',
                    'nm_id' => $item['nm_id'] ?? '',
                ]);
            } catch (\Exception $e) {
                Log::error('Ошибка при сохранении дохода: ' . $e->getMessage(), [
                    'item' => $item
                ]);
                continue;
            }
        }

        $this->info('Данные успешно загружены');
    }
}
