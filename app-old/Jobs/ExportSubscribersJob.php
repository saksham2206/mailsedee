<?php

namespace Acelle\Jobs;

use Acelle\Library\Traits\Trackable;
use Exception;

class ExportSubscribersJob extends Base
{
    use Trackable;

    protected $mailList;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mailList)
    {
        $this->mailList = $mailList;

        // Set the initial value for progress check
        $this->afterDispatched(function ($thisJob, $monitor) {
            $monitor->setJsonData([
                'percentage' => 0,
                'total' => 0,
                'processed' => 0,
                'failed' => 0,
                'message' => 'Export is being queued for processing...',
                'filepath' => $this->mailList->getExportFilePath(),
            ]);
        });
    }
    
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->mailList->export(function ($processed, $total, $failed, $message) {
            $percentage = ($total && $processed) ? (int)($processed*100/$total) : 0;

            $this->monitor->updateJsonData([
                'percentage' => $percentage,
                'total' => $total,
                'processed' => $processed,
                'failed' => $failed,
                'message' => $message,
            ]);
        });
    }
}
