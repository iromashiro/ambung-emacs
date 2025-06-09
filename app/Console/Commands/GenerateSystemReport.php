<?php

namespace App\Console\Commands;

use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateSystemReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:generate {--period=month : Reporting period (day, week, month, year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate system sales report and save to storage';

    /**
     * Execute the console command.
     */
    public function handle(ReportService $reportService)
    {
        $period = $this->option('period');
        $salesSummary = $reportService->getPlatformSalesSummary($period);
        $topStores = $reportService->getTopSellingStores(10);
        
        $reportDate = now()->format('Y-m-d');
        $filename = "reports/sales-report-{$reportDate}.csv";
        
        $csvContent = "Sales Report - {$reportDate}\n";
        $csvContent .= "Period: {$period}\n\n";
        $csvContent .= "Total Sales,Rp " . number_format($salesSummary['total_sales'], 0, ',', '.') . "\n";
        $csvContent .= "Order Count,{$salesSummary['order_count']}\n";
        $csvContent .= "Average Order Value,Rp " . number_format($salesSummary['average_order_value'], 0, ',', '.') . "\n\n";
        
        $csvContent .= "Daily Sales\n";
        $csvContent .= "Date,Sales\n";
        foreach ($salesSummary['daily_sales'] as $date => $sales) {
            $csvContent .= "{$date},Rp " . number_format($sales, 0, ',', '.') . "\n";
        }
        
        $csvContent .= "\nTop Stores\n";
        $csvContent .= "Store,Orders,Sales\n";
        foreach ($topStores as $store) {
            $csvContent .= "{$store->name},{$store->order_count},Rp " . number_format($store->total_sales, 0, ',', '.') . "\n";
        }
        
        Storage::put($filename, $csvContent);
        
        $this->info("Report generated and saved to {$filename}");
        
        return Command::SUCCESS;
    }
}