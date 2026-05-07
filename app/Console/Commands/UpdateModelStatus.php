<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\InventoryModel\Material\ModelStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UpdateModelStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:check-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update model project status based on lifecycle dates (Project -> Regular -> Expired)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $this->info("Running model status check for $today...");
        
        // 1. Project -> Regular
        $toRegular = ModelStatus::where('project_status', 'Project')
            ->whereNotNull('regular_start_date')
            ->whereDate('regular_start_date', '<=', $today)
            ->get();

        $countToRegular = 0;
        foreach ($toRegular as $status) {
            $status->project_status = 'Regular';
            $status->save();
            $countToRegular++;
        }

        if ($countToRegular > 0) {
            $this->info("Updated $countToRegular model(s) to Regular.");
            Log::info("Scheduler [model:check-status]: Updated $countToRegular model(s) from Project to Regular.");
        }

        $this->info("Model status check completed.");
    }
}
