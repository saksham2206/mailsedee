<?php

namespace Acelle\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Acelle\Model\Subscriber;

class SendMessage implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscriber;
    protected $server;
    protected $campaign;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($campaign, Subscriber $subscriber, $server)
    {
        $this->campaign = $campaign;
        $this->subscriber = $subscriber;
        $this->server = $server;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }

        // Check customer quota
        while ($this->campaign->customer->overQuota()) {
            // throw new \Exception('Customer has reached sending limit');
            sleep(60);
        }

        // Check sending server quota
        while ($this->server->overQuota()) {
            sleep(60);
        }

        $logger = $this->campaign->logger();

        // Prepare the email message to send
        list($message, $msgId) = $this->campaign->prepareEmail($this->subscriber, $this->server);

        // Actually send!
        $logger->info(sprintf('Sending to %s [Server "%s"]', $this->subscriber->email, $this->server->name));
        $sent = $this->server->send($message);

        // Log
        $this->campaign->trackMessage($sent, $this->subscriber, $this->server, $msgId);
        $logger->info(sprintf('Sent to %s [Server "%s"]', $this->subscriber->email, $this->server->name));
    }
}
