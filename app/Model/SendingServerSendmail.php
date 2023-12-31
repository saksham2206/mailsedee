<?php

/**
 * SendingServerSendmail class.
 *
 * Abstract class for Sendmail sending server
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Acelle\Library\Log as MailLog;
use Acelle\Library\StringHelper;
class SendingServerSendmail extends SendingServer
{
    protected $table = 'sending_servers';

    /**
     * Send the provided message.
     *
     * @return bool
     *
     * @param message
     */
    public function send($message, $params = array())
    {
        try {
            $transport = new \Swift_SendmailTransport($this->sendmail_path.' -bs');

            // Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);

            // Actually send
            $sent = $mailer->send($message);

            if ($sent) {
                MailLog::info('Sent! -  SENDMAIL'.$sent);

                return array(
                    'status' => self::DELIVERY_STATUS_SENT,
                );
            } else {
                MailLog::warning('Sending failed');
                
            //start bounce email record by himanshu
            $bounceLog = new BounceLog();
            // $bounceLog->runtime_message_id = StringHelper::cleanupMessageId($e->getMessage());
            // For Mailgun, runtime_message_id EQUIV. message_id
            $bounceLog->message_id = $bounceLog->runtime_message_id;
            $bounceLog->bounce_type = BounceLog::HARD;
            // $bounceLog->raw = $inputJSON;
            $bounceLog->save();
            MailLog::info('Bounce recorded for message '.$bounceLog->runtime_message_id);
            //end bounce email record by himanshu

                return array(
                    'status' => self::DELIVERY_STATUS_FAILED,
                    'error' => 'Unknown SMTP error',
                );
            }
        } catch (\Exception $e) {
            MailLog::warning('Sending failed');
            MailLog::warning($e->getMessage());
            
            //start bounce email record by himanshu
            $bounceLog = new BounceLog();
            // $bounceLog->runtime_message_id = StringHelper::cleanupMessageId($e->getMessage());
            // For Mailgun, runtime_message_id EQUIV. message_id
            $bounceLog->message_id = $bounceLog->runtime_message_id;
            $bounceLog->bounce_type = BounceLog::HARD;
            // $bounceLog->raw = $inputJSON;
            $bounceLog->save();
            MailLog::info('Bounce recorded for message '.$bounceLog->runtime_message_id);
            //end bounce email record by himanshu

            return array(
                'status' => self::DELIVERY_STATUS_FAILED,
                'error' => $e->getMessage(),
            );
        }
    }

    /**
     * Check the sending server settings, make sure it does work.
     *
     * @return bool
     */
    public function test()
    {
        if (!file_exists($this->sendmail_path)) {
            throw new \Exception("File {$this->sendmail_path} does not exists");
        }

        if (!is_executable($this->sendmail_path)) {
            throw new \Exception("File {$this->sendmail_path} is not executable");
        }

        return true;
    }

    public function allowVerifyingOwnEmailsRemotely()
    {
        return false;
    }

    public function allowVerifyingOwnDomainsRemotely()
    {
        return false;
    }

    public function syncIdentities()
    {
        // just do nothing
    }

    public static function instantiateFromSettings($settings = [])
    {
        $properties = [ 'sendmail_path', 'from_name', 'from_address' ];
        $required = ['sendmail_path', 'from_address'];

        $server = new self();

        // Validate
        foreach ($properties as $property) {
            if ((!array_key_exists($property, $settings) || empty($settings[$property])) && in_array($property, $required)) {
                throw new \Exception("Cannot instantiate Sendmail mailer, '{$property}' property is missing");
            }

            $server->{$property} = $settings[$property];
        }

        return $server;
    }

    public function setupBeforeSend($fromEmailAddress)
    {
        //
    }
}
