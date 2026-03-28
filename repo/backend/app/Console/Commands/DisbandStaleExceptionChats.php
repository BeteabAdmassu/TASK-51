<?php

namespace App\Console\Commands;

use App\Models\RideOrder;
use App\Services\GroupChatLifecycleService;
use Illuminate\Console\Command;

class DisbandStaleExceptionChats extends Command
{
    protected $signature = 'ride:disband-stale-exception-chats';

    protected $description = 'Disband chats for rides stuck in exception status over 30 minutes';

    public function __construct(private readonly GroupChatLifecycleService $chatLifecycleService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $rides = RideOrder::query()
            ->where('status', 'exception')
            ->where('updated_at', '<', now()->subMinutes(30))
            ->get();

        foreach ($rides as $ride) {
            $this->chatLifecycleService->disband($ride, 'exception');
        }

        return self::SUCCESS;
    }
}
