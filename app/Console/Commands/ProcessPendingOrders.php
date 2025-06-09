<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:process-pending {--hours=24 : Hours to wait before auto-canceling}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending orders and auto-cancel old ones';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        $hours = $this->option('hours');
        $cutoffDate = Carbon::now()->subHours($hours);
        
        $pendingOrders = Order::where('status_enum', 'NEW')
            ->where('created_at', '<', $cutoffDate)
            ->get();
        
        $canceledCount = 0;
        
        foreach ($pendingOrders as $order) {
            try {
                $orderService->cancelOrder($order, null);
                $canceledCount++;
            } catch (\Exception $e) {
                $this->error("Failed to cancel order {$order->id}: " . $e->getMessage());
            }
        }
        
        $this->info("Auto-canceled {$canceledCount} pending orders.");
        
        return Command::SUCCESS;
    }
}