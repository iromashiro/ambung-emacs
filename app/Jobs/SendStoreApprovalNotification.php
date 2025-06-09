<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendStoreApprovalNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The users to notify.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $users;

    /**
     * The notification to send.
     *
     * @var \Illuminate\Notifications\Notification
     */
    protected $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $users, Notification $notification)
    {
        $this->users = $users;
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->users as $user) {
            $user->notify($this->notification);
        }
    }
}