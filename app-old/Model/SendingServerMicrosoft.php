<?php

/**
 * SendingServerGmail class.
 *
 * Abstract class for standard SMTP sending server
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

use Acelle\Library\Log as MailLog;
use Dacastro4\LaravelGmail\Services\Message\Mail;
use Hybridauth\Hybridauth;
//use Dacastro4\LaravelGmail\Services\Message\Mail;

class SendingServerMicrosoft extends SendingServer
{
    protected $table = 'sending_servers';

    /**
     * Send the provided message.
     *
     * @return bool
     *
     * @param message
     */
    public function send($msgId,$subject,$fromData,$toData,$reply_to,$body, $params)
    {
        //MailLog::info('subscriber '.json_encode($toData));

        try {
            $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
              'clientId'                => config('azure.appId'),
              'clientSecret'            => config('azure.appSecret'),
              'redirectUri'             => config('azure.redirectUri'),
              'urlAuthorize'            => config('azure.authority').config('azure.authorizeEndpoint'),
              'urlAccessToken'          => config('azure.authority').config('azure.tokenEndpoint'),
              'urlResourceOwnerDetails' => '',
              'scopes'                  => config('azure.scopes')
            ]);
            $newToken = $oauthClient->getAccessToken('refresh_token', [
                'refresh_token' => $params->api_secret_key
              ]);

            $to = $toData->email;
            $data = [
                "message"  => [
                    "subject" => $subject,
                    "body" => [
                        "contentType" => 'Html',
                        "content" => $body,
                    ],
                    "toRecipients" => [[
                        "emailAddress" => [
                            "address" => $to,

                        ],
                    ],]
                ],
            ];
            //var_dump();
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://graph.microsoft.com/v1.0/me/sendMail',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS =>json_encode($data,true),
              CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$newToken->getToken(),
                'Content-Type: application/json'
              ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            echo $response;
            MailLog::info('Sent!s'.$response);
            //foreach ($toData as $key => $value) {
                // $to = $toData->email; 
                // $mail = new Mail;
                // $mail->using( $params->token_mail );

                // $mail->to( $to, $name = null );
                // $mail->from( $params->default_from_email, $name = null );
                // $mail->subject( $subject );
                // $mail->message( $body );
                // $sent = $mail->send();

                // if ($sent) {
                //     MailLog::info('Sentss!');
                //     return array(
                //         'status' => self::DELIVERY_STATUS_SENT,
                //     );  
                    
                // }
                

            //}
            
            // $transport = new \Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl');
            //     $transport->setAuthMode('XOAUTH2');
            //         $transport->setUsername($params->default_from_email);
            //         $transport ->setPassword($params->aws_access_key_id);
            // in case of: stream_socket_enable_crypto(): SSL operation failed with code 1. OpenSSL Error messages: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed
            //$transport->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false)));

            // setup bounce handler: specify the Return-Path
            // if ($this->bounceHandler) {
            //     $message->setReturnPath($this->bounceHandler->username);
            // }

            //MailLog::info('SentMail1!'.$params->default_from_email);
                // Create the Mailer using your created Transport
                //$mailer = new \Swift_Mailer($transport);
                //MailLog::info('SentMail!');

            /*// Create the Mailer using your created Transport
            $mailer = new \Swift_Mailer($transport);*/

            // Actually send
            /*$sent = $mailer->send($message);

            if ($sent) {
                MailLog::info('Sent!');

                return array(
                    'status' => self::DELIVERY_STATUS_SENT,
                );
            } else {
                MailLog::info('testing new');
                $config = [
                    'callback' => HttpClient\Util::getCurrentUrl(),

                    'providers' => [                        
                        'Google' => [
                            'enabled' => true,
                            'keys' => ['id' => '256665035570-kmba9hu43667ms8h1j6c09qber124gj1.apps.googleusercontent.com', 'secret' => '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'],
                        ]
                    ],
                ];
                $hybridauth = new Hybridauth($config);
                $response = $hybridauth->refreshAccessToken([
                    "grant_type" => "refresh_token",
                    "refresh_token" => $params->aws_secret_access_key,
                    "client_id" => env('GOOGLE_CLIENT_ID','256665035570-kmba9hu43667ms8h1j6c09qber124gj1.apps.googleusercontent.com'),
                    "client_secret" => env('GOOGLE_CLIENT_SECRET','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'),
                ]);
                 
                $data = json_decode($response);
                $transport = new \Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl');
                $transport->setAuthMode('XOAUTH2');
                    $transport->setUsername($response->email);
                    $transport ->setPassword($response->access_token);
                MailLog::warning('Sending again');
                $mailer = new \Swift_Mailer($transport);
                $sent = $mailer->send($message);
                if($sent){
                    MailLog::info('Sent!s');

                    return array(
                        'status' => self::DELIVERY_STATUS_SENT,
                    );  
                }else{
                    MailLog::warning('Sending Failed');
                    return array(
                        'status' => self::DELIVERY_STATUS_FAILED,
                        'error' => 'Unknown SMTP error',
                    );
                }
                
            }*/
        } catch (\Exception $e) {

            // MailLog::info('testing new');
            //     $config = [
                    

            //         'providers' => [                        
            //             'Google' => [
            //                 'enabled' => true,
            //                 'keys' => ['id' => '256665035570-kmba9hu43667ms8h1j6c09qber124gj1.apps.googleusercontent.com', 'secret' => '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'],
            //             ]
            //         ],
            //     ];
            //     $hybridauth = new Hybridauth($config);
            //     $response = $hybridauth->refreshAccessToken([
            //         "grant_type" => "refresh_token",
            //         "refresh_token" => $params->aws_secret_access_key,
            //         "client_id" => env('GOOGLE_CLIENT_ID','256665035570-kmba9hu43667ms8h1j6c09qber124gj1.apps.googleusercontent.com'),
            //         "client_secret" => env('GOOGLE_CLIENT_SECRET','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'),
            //     ]);
                 
            //     $data = json_decode($response);
            //     $transport = new \Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl');
            //     $transport->setAuthMode('XOAUTH2');
            //         $transport->setUsername($response->email);
            //         $transport ->setPassword($response->access_token);
            //     MailLog::warning('Sending again');
            //     $mailer = new \Swift_Mailer($transport);
            //     $sent = $mailer->send($message);
            //     if($sent){
            //         MailLog::info('Sent!s');

            //         return array(
            //             'status' => self::DELIVERY_STATUS_SENT,
            //         );  
            //     }else{
            //         
            //     }

            MailLog::warning('Sending failed');
                    MailLog::warning($e->getMessage());

                    return array(
                        'status' => self::DELIVERY_STATUS_FAILED,
                        'error' => $e->getMessage(),
                    );
            
        }

        // // try {
        //     // $transport = new \Swift_SmtpTransport($this->host, (int) $this->smtp_port, $this->smtp_protocol);
        //     // $transport->setUsername($this->smtp_username);
        //     // $transport->setPassword($this->smtp_password);
        //     // // in case of: stream_socket_enable_crypto(): SSL operation failed with code 1. OpenSSL Error messages: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed
        //     // $transport->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false)));

        //     // setup bounce handler: specify the Return-Path
        //     // if ($this->bounceHandler) {
        //     //     $message->setReturnPath($this->bounceHandler->username);
        //     // }

        //     // Create the Mailer using your created Transport
        //     // $mailer = new \Swift_Mailer($transport);
        //     try {

        //         $transport = new \Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl');
        //         $transport->setAuthMode('XOAUTH2');
        //             $transport->setUsername($params->default_from_email);
        //             $transport ->setPassword($params->access_token);
        //         MailLog::info('SentMail1!'.$params->default_from_email);
        //         // Create the Mailer using your created Transport
        //         $mailer = new \Swift_Mailer($transport);
        //         MailLog::info('SentMail!');
        //         // Create a message
        //         //$body = 'Hello, <p>Email sent through <span style="color:red;">Swift Mailer</span>.</p>';
         
        //         // $message = (new Swift_Message('Email Through Swift Mailer'))
        //         //     ->setFrom(['FROM_EMAIL' => 'FROM_NAME'])
        //         //     ->setTo([$email])
        //         //     ->setBody($body)
        //         //     ->setContentType('text/html');
         
        //         // Send the message
        //         //$sent = false;
        //         $sent = $mailer->send($message);

        //         if ($sent) {
        //             MailLog::info('Sent!');

        //             return array(
        //                 'status' => self::DELIVERY_STATUS_SENT,
        //             );
        //         } else {
        //             MailLog::info('Not Sent!');
        //         }
         
        //         //echo 'Email has been sent.';
        //     } catch (Exception $e) {
        //         MailLog::info('Sending failed');
        //             //MailLog::warning($e->getMessage());

        //             return array(
        //                 'status' => self::DELIVERY_STATUS_FAILED,
        //                 'error' => $e->getMessage(),
        //             );
        //     }
        //     // MailLog::info('last send '.json_encode($params));
        //     //     $mail = new Mail;
        //     //     $token = $params['token'];
        //     //     $mail->using( $token );

        //     //     $mail->from( $params['default_from_email'], $name = $params['name'] );



        //     // // Actually send
        //     // $sent = $mailer->send($message);

        //     // if ($sent) {
        //     //     MailLog::info('Sent!');

        //     //     return array(
        //     //         'status' => self::DELIVERY_STATUS_SENT,
        //     //     );
        //     // } else {
        //     //     MailLog::warning('Sending failed');
        //     //     MailLog::warning($e->getMessage());

        //     //     return array(
        //     //         'status' => self::DELIVERY_STATUS_FAILED,
        //     //         'error' => $e->getMessage(),
        //     //     );
        //     // }
        // //}
    }

    /**
     * Check the sending server settings, make sure it does work.
     *
     * @return bool
     */
    public function test()
    {
        $transport = new \Swift_SmtpTransport($this->host, (int) $this->smtp_port, $this->smtp_protocol);
        $transport->setUsername($this->smtp_username);
        $transport->setPassword($this->smtp_password);

        // in case of: stream_socket_enable_crypto(): SSL operation failed with code 1. OpenSSL Error messages: error:14090086:SSL routines:SSL3_GET_SERVER_CERTIFICATE:certificate verify failed
        $transport->setStreamOptions(array('ssl' => array('allow_self_signed' => true, 'verify_peer' => false, 'verify_peer_name' => false)));

        // Create the Mailer using your created Transport
        $mailer = new \Swift_Mailer($transport);
        $mailer->getTransport()->start();

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
        $properties = [ 'host', 'smtp_port',  'smtp_protocol', 'smtp_username', 'smtp_password', 'from_name', 'from_address' ];
        $required = ['host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_address'];

        $server = new self();

        // validate
        foreach ($properties as $property) {
            if ((!array_key_exists($property, $settings) || empty($settings[$property])) && in_array($property, $required)) {
                throw new \Exception("Cannot instantiate SMTP mailer, '{$property}' property is missing");
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