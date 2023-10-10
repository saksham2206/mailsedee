<?php

namespace Acelle\Jobs;

use Acelle\Library\Log as MailLog;
use Acelle\Model\MailList;
use Acelle\Model\Blacklist;

class UpdateMailListJob extends Base
{
    protected $list;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(MailList $list)
    {
        $this->list = $list;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->list->updateCachedInfo();
        // blacklist new emails (if any)
        Blacklist::doBlacklist($this->list->customer);
    }
}
