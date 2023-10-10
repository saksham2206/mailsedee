<?php

namespace Acelle\Jobs;

class DeliverEmail extends Base
{
    protected $email;
    protected $subscriber;
    protected $triggerId; // one email may be delivered to a given subscriber more than once (weekly recurring, birthday... for example)

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($email, $subscriber, $triggerId)
    {
        $this->email = $email;
        $this->subscriber = $subscriber;
        $this->triggerId = $triggerId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->email->deliverTo($this->subscriber, $this->triggerId);
    }
}
