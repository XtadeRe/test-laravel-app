<?php

namespace App\Console\Commands;

use App\Models\Stocks;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class getIncomes extends Command
{

    protected $signature = 'get:incomes {dateFrom?} {dateTo?}';

    public function handle()
    {
        $dateFrom = $this->argument('dateFrom') ?? now()->subDays(7)->format('Y-m-d');
        $dateTo = $this->argument('dateTo') ?? now()->subDays(2)->format('Y-m-d');

        $this->info("Идёт загрузка продаж с {$dateFrom} по {$dateTo}");

        $response = Http::get('http://109.73.206.144:6969/api/Incomes', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'page' => 1,
            'key' => env('API_KEY'),
            'limit' => 100,
        ]);

        if (!$response->successful()) {
            $this->info("Произошла ошибка или данных нет");
            return;
        }

        $data = $response->json();

        foreach ($data as $item) {
            Stocks::create([
                'income_id' => $item['income_id'],
                'number' => $item['number'],
                'date' => $item['date'],
                'last_change_date' => $item['last_change_date'],
                'supplier_article' => $item['supplier_article'],
                'tech_size' => $item['tech_size'],
                'barcode' => $item['barcode'],
                'quantity' => $item['quantity'],
                'total_price' => $item['total_price'],
                'date_close' => $item['date_close'],
                'warehouse_name' => $item['warehouse_name'],
                'nm_id' => $item['nm_id'],
                'status' => $item['status'],
            ]);
        }

        $this->info('Данные успешно загружены');
    }
}
