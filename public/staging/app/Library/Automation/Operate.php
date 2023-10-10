<?php

namespace Acelle\Library\Automation;

class Operate extends Action
{
    /*****

        Operate action may result in the following cases:
          + Done OK
          + Exception (any type of exception...)
        In case of Exception, it is better to stop the whole automation process and write error log to the automation
        so that the responsible person can check it

        Then, "last_executed" is used as a flag indicating that the process is done
        Execution always returns TRUE

    ****/
    protected function doExecute()
    {
        if (config('app.demo') == 'true') {
            return true;
        }

        // DO HERE
        $this->logger->info(sprintf('Perform an action...'));

        sleep(1);
        return true;
    }

    // Overwrite
    public function getActionDescription()
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();

        return sprintf('Perform an operation');
    }

    public function getProgressDescription()
    {
        $subscriber = $this->autoTrigger->subscriber;

        if (is_null($this->getLastExecuted())) {
            return sprintf('* Update contact "%s"', $subscriber->email);
        }

        return sprintf('Updated contact "%s"', $subscriber->email);
    }
}
