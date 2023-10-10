<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use File;
use Validator;
use ZipArchive;
use KubAT\PhpSimple\HtmlDomParser;
use Acelle\Library\ExtendedSwiftMessage;
use Acelle\Library\Tool;
use Acelle\Library\Rss;
use Acelle\Library\StringHelper;
use Acelle\Library\Log as MailLog;
use Acelle\Model\Setting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Acelle\Jobs\DeliverEmail;
use Acelle\Library\Traits\HasTemplate;

class Email extends Model
{
    use HasTemplate;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'subject', 'from', 'from_name', 'reply_to', 'sign_dkim', 'track_open', 'track_click', 'action_id',
    ];

    // Cached HTML content
    protected $parsedContent = null;

    /**
     * Association with mailList through mail_list_id column.
     */
    public function automation()
    {
        return $this->belongsTo('Acelle\Model\Automation2', 'automation2_id');
    }

    /**
     * Get the customer.
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    /**
     * Association with attachments.
     */
    public function attachments()
    {
        return $this->hasMany('Acelle\Model\Attachment');
    }

    /**
     * Association with email links.
     */
    public function emailLinks()
    {
        return $this->hasMany('Acelle\Model\EmailLink');
    }

    /**
     * Association with open logs.
     */
    public function trackingLogs()
    {
        return $this->hasMany('Acelle\Model\TrackingLog');
    }

    public function deliveryAttempts()
    {
        return $this->hasMany('Acelle\Model\DeliveryAttempt');
    }

    /**
     * Get email's associated tracking domain.
     */
    public function trackingDomain()
    {
        return $this->belongsTo('Acelle\Model\TrackingDomain', 'tracking_domain_id');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating automation.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            $item->uid = $uid;
        });

        static::deleted(function ($item) {
            // Need reviewing...
        });
    }

    /**
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Create automation rules.
     *
     * @return array
     */
    public function rules($request=null)
    {
        $rules = [
            'subject' => 'required',
            'from' => 'required|email',
            'from_name' => 'required',
        ];

        // tracking domain
        if (isset($request) && $request->custom_tracking_domain) {
            $rules['tracking_domain_uid'] = 'required';
        }

        return $rules;
    }

    /**
     * Upload attachment.
     */
    public function uploadAttachment($file)
    {
        $file_name = $file->getClientOriginalName();
        $att = $this->attachments()->make();
        $att->size = $file->getSize();
        $att->name = $file->getClientOriginalName();

        $path = $file->move(
            $this->getAttachmentPath(),
            $att->name
        );
        
        $att->file = $this->getAttachmentPath($att->name);
        $att->save();

        return $att;
    }

    /**
     * Get attachment path.
     */
    public function getAttachmentPath($path = null)
    {
        return $this->customer->getAttachmentsPath($path);
    }

    /**
     * Find and update email links.
     */
    public function updateLinks()
    {

        if (!$this->getTemplateContent()) {
            return false;
        }

        $links = [];

        // find all links from contents
        // Fix: str_get_html returning false
        defined('MAX_FILE_SIZE') || define('MAX_FILE_SIZE', 10000000);
        $document = HtmlDomParser::str_get_html($this->getTemplateContent());
        foreach ($document->find('a') as $element) {
            if (preg_match('/^http/', $element->href) != 0) {
                $links[] = trim($element->href);
            }
        }

        // delete al bold links
        $this->emailLinks()->whereNotIn('link', $links)->delete();

        foreach ($links as $link) {
            $exist = $this->emailLinks()->where('link', '=', $link)->count();

            if (!$exist) {
                $this->emailLinks()->create([
                    'link' => $link,
                ]);
            }
        }
    }

    public function queueDeliverTo($subscriber, $triggerId = null)
    {
        MailLog::info("email data".json_encode($this));
        dispatch(new DeliverEmail(
            $this,
            $subscriber,
            $triggerId,
        ));
    }

    // @note: complicated dependencies
    // It is just fine, we can think Email as an object depends on User/Customer
    public function deliverTo($subscriber, $triggerId = null)
    {
        // @todo: code smell here, violation of Demeter Law
        // @todo: performance
        while ($this->automation->customer->overQuota()) {
            MailLog::warning(sprintf('Email `%s` (%s) to `%s` halted, user exceeds sending limit', $this->subject, $this->uid, $subscriber->email));
            sleep(60);
        }

        $server = $subscriber->mailList->pickSendingServer($this->automation->smtp_server_id);

        MailLog::info('test Data ssss'.json_encode($server));
        MailLog::info("Sending to subscriber `{$subscriber->email}`");
        if($server->type != 'Gmail' || $server->type != 'Microsoft'){
           list($message, $msgId) = $this->prepare($subscriber); 
        }
        
        
        if (config('app.demo') == true) {
            $sent = array(
                'runtime_message_id' => $msgId,
                'status' => SendingServer::DELIVERY_STATUS_SENT
            );
            // additional log
            MailLog::info("[DEMO] Sent to subscriber `{$subscriber->email}`");
        } else {
             if($server->type == 'Gmail'){
                $customHeaders = $this->getCustomHeaders($subscriber, $this);
                $msgId = $customHeaders['X-Acelle-Message-Id'];
                $subject = $this->getSubject($subscriber, $msgId);
                $fromData = array($this->from => $this->from_name);
                $toData= $subscriber;
                $reply_to = $this->reply_to;
                $body = $this->getHtmlContent($subscriber, $msgId, $server);
                $sent = $server->send($msgId,$subject,$fromData,$toData,$reply_to,$body,$server);
             }elseif($server->type == 'Microsoft'){
                $customHeaders = $this->getCustomHeaders($subscriber, $this);
                $msgId = $customHeaders['X-Acelle-Message-Id'];
                $subject = $this->getSubject($subscriber, $msgId);
                $fromData = array($this->from => $this->from_name);
                $toData= $subscriber;
                $reply_to = $this->reply_to;
                $body = $this->getHtmlContent($subscriber, $msgId, $server);
                $sent = $server->send($msgId,$subject,$fromData,$toData,$reply_to,$body,$server);
             }else{
                $sent = $server->send($message,$server);
             }
            MailLog::info('test Data sssssssss'.json_encode($sent));
            //$sent = $server->send($message,$server);
            // additional log
            MailLog::info("Sent to subscriber `{$subscriber->email}`");
        }

        $this->trackMessage($sent, $subscriber, $server, $msgId, $triggerId);
    }

    /**
     * Prepare the email content using Swift Mailer.
     *
     * @input object subscriber
     * @input object sending server
     *
     * @return MIME text message
     */
    public function prepare($subscriber)
    {

        $this->updateLinks();

        // build the message
        $customHeaders = $this->getCustomHeaders($subscriber, $this);
        $msgId = $customHeaders['X-Acelle-Message-Id'];

        $message = new ExtendedSwiftMessage();
        $message->setId($msgId);

        // fixed: HTML type only
        $message->setContentType('text/html; charset=utf-8');

        foreach ($customHeaders as $key => $value) {
            $message->getHeaders()->addTextHeader($key, $value);
        }

        // @TODO for AWS, setting returnPath requires verified domain or email address
        $server = $subscriber->mailList->pickSendingServer();
        if ($server->allowCustomReturnPath()) {
            $returnPath = $server->getVerp($subscriber->email);
            if ($returnPath) {
                $message->setReturnPath($returnPath);
            }
        }
        $message->setSubject($this->getSubject($subscriber, $msgId));
        $message->setFrom(array($this->from => $this->from_name));
        $message->setTo($subscriber->email);
        $message->setReplyTo($this->reply_to);
        $message->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        $message->addPart($this->getHtmlContent($subscriber, $msgId, $server), 'text/html');

        if ($this->sign_dkim) {
            $message = $this->sign($message);
        }

        foreach ($this->attachments as $file) {
            $attachment = \Swift_Attachment::fromPath($file->file);
            $message->attach($attachment);
            // This is used by certain delivery services like ElasticEmail
            $message->extAttachments[] = [ 'path' => $file->file, 'type' => $attachment->getContentType()];
        }
        MailLog::info('Message == '.json_encode($message));
        return array($message, $msgId);
    }

    /**
     * Build Email Custom Headers.
     *
     * @return Hash list of custom headers
     */
    public function getCustomHeaders($subscriber, $server)
    {
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from));

        return array(
            'X-Acelle-email-Id' => $this->uid,
            'X-Acelle-Subscriber-Id' => $subscriber->uid,
            'X-Acelle-Customer-Id' => $this->automation->customer->uid,
            'X-Acelle-Message-Id' => $msgId,
            'X-Acelle-Sending-Server-Id' => $server->uid,
            'List-Unsubscribe' => '<'.$this->generateUnsubscribeUrl($msgId, $subscriber).'>',
            'Precedence' => 'bulk',
        );
    }

    public function generateUnsubscribeUrl($msgId, $subscriber)
    {
        // in case of a fake object, for sending test email
        if (is_a($subscriber, 'stdClass')) {
            $path = route('unsubscribeUrl', [ 'subscriber' => 'unknown', 'message_id' => StringHelper::base64UrlEncode($msgId)], false);

            return $path;
        }

        // OPTION 1: immediately opt out
        $path = $subscriber->generateUnsubscribeUrl($msgId, $absoluteUrl = false);

        // OPTION 2: unsubscribe form, @IMPORTANT: it does not produce tracking log!!
        //$path = route('unsubscribeForm', ['list_uid' => $subscriber->mailList->uid, 'code' => $subscriber->getSecurityToken('unsubscribe'), 'uid' => $subscriber->uid], false);

        return $this->buildPublicUrl($path);
    }

    /**
     * Log delivery message, used for later tracking.
     */
    public function trackMessage($response, $subscriber, $server, $msgId, $triggerId = null)
    {

        // @todo: customerneedcheck
        $params = array_merge(array(
                // 'email_id' => $this->id,
                'message_id' => $msgId,
                'subscriber_id' => $subscriber->id,
                'sending_server_id' => $server->id,
                'customer_id' => $this->automation->customer->id,
                'auto_trigger_id' => $triggerId,
            ), $response);

        if (!isset($params['runtime_message_id'])) {
            $params['runtime_message_id'] = $msgId;
        }
        MailLog::info('CtrateTrack'.json_encode($params));
        // create tracking log for message
        $this->trackingLogs()->create($params);

        // increment customer quota usage
        $this->automation->customer->countUsage();
        $server->countUsage();
    }

    /**
     * Get tagged Subject.
     *
     * @return string
     */
    public function getSubject($subscriber, $msgId)
    {
        return $this->tagMessage($this->subject, $subscriber, $msgId, null);
    }

    public function buildPublicUrl($path)
    {
        return join_url($this->getTrackingHost(), $path);
    }

    public function tagMessage($message, $subscriber, $msgId, $server = null)
    {
        if (!is_null($server) && $server->isElasticEmailServer()) {
            $message = $server->addUnsubscribeUrl($message);
        }

        $tags = array(
            'CAMPAIGN_NAME' => $this->name,
            'CAMPAIGN_UID' => $this->uid,
            'CAMPAIGN_SUBJECT' => $this->subject,
            'CAMPAIGN_FROM_EMAIL' => $this->from,
            'CAMPAIGN_FROM_NAME' => $this->from_name,
            'CAMPAIGN_REPLY_TO' => $this->reply_to,
            'SUBSCRIBER_UID' => $subscriber->uid,
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            // 'CONTACT_NAME' => $subscriber->mailList->contact->company,
            // 'CONTACT_COUNTRY' => $subscriber->mailList->contact->country->name,
            // 'CONTACT_STATE' => $subscriber->mailList->contact->state,
            // 'CONTACT_CITY' => $subscriber->mailList->contact->city,
            // 'CONTACT_ADDRESS_1' => $subscriber->mailList->contact->address_1,
            // 'CONTACT_ADDRESS_2' => $subscriber->mailList->contact->address_2,
            // 'CONTACT_PHONE' => $subscriber->mailList->contact->phone,
            // 'CONTACT_URL' => $subscriber->mailList->contact->url,
            // 'CONTACT_EMAIL' => $subscriber->mailList->contact->email,
            'LIST_NAME' => $subscriber->mailList->name,
            'LIST_SUBJECT' => $subscriber->mailList->default_subject,
            'LIST_FROM_NAME' => $subscriber->mailList->from_name,
            'LIST_FROM_EMAIL' => $subscriber->mailList->from_email,
        );

        # Subscriber specific
        if (!$this->isStdClassSubscriber($subscriber)) {
            $tags['UPDATE_PROFILE_URL'] = $this->generateUpdateProfileUrl($subscriber);
            $tags['UNSUBSCRIBE_URL'] = $this->generateUnsubscribeUrl($msgId, $subscriber);
            $tags['WEB_VIEW_URL'] = $this->generateWebViewerUrl($msgId);

            # Subscriber custom fields
            foreach ($subscriber->mailList->fields as $field) {
                $tags['SUBSCRIBER_'.$field->tag] = $subscriber->getValueByField($field);
                $tags[$field->tag] = $subscriber->getValueByField($field);
            }

            // Special / shortcut fields
            $tags['NAME'] = $subscriber->getFullName();
            $tags['FULL_NAME'] = $subscriber->getFullName();
        } else {
            $tags['SUBSCRIBER_EMAIL'] = $subscriber->email;
        }

        // Actually transform the message
        foreach ($tags as $tag => $value) {
            $message = str_replace('{'.$tag.'}', $value, $message);
        }

        return $message;
    }

    /**
     * Check if the given variable is a subscriber object (for actually sending a email)
     * Or a stdClass subscriber (for sending test email).
     *
     * @param object $object
     */
    public function isStdClassSubscriber($object)
    {
        return get_class($object) == 'stdClass';
    }

    public function generateUpdateProfileUrl($subscriber)
    {
        $path = route('updateProfileUrl', ['list_uid' => $subscriber->mailList->uid, 'uid' => $subscriber->uid, 'code' => $subscriber->getSecurityToken('update-profile')], false);

        return $this->buildPublicUrl($path);
    }

    public function generateWebViewerUrl($msgId)
    {
        $path = route('webViewerUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        return $this->buildPublicUrl($path);
    }

    /**
     * Build Email HTML content.
     *
     * @return string
     */
    public function getHtmlContent($subscriber = null, $msgId = null, $server = null)
    {
        
        // @note: IMPORTANT: the order must be as follows
        // * addTrackingURL
        // * appendOpenTrackingUrl
        // * tagMessage
        //dd($this->parsedContent);
        if (is_null($this->parsedContent)) {
            // STEP 01. Get RAW content
            $body = $this->getTemplateContent();
            //dd($body);
            // STEP 02. Append footer
            //if ($this->footerEnabled()) {
                
            //}
            
            // STEP 03. Parse RSS
            // if (Setting::isYes('rss.enabled')) {
            //     $body = Rss::parse($body);
            // }

            // STEP 04. Replace Bare linefeed
            // Replace bare line feed char which is not accepted by Outlook, Yahoo, AOL...
            $body = StringHelper::replaceBareLineFeed($body);
            //dd($body);
            // "Cache" it, do not repeat this task for every subscriber in the loop
            $this->parsedContent = $body;
        } else {
            // Retrieve content from cache
            $body = $this->parsedContent;
        }

        // STEP 04.1. Check woocommerce items
        if ($this->automation->getTrigger()->getOption('type') == 'woo-abandoned-cart') {
            $body = $this->wooTransform($body);
        }

        // STEP 05. Transform URLs
        $body = $this->template->getContentWithTransformedAssetsUrls($this->getTrackingHost());

        if (is_null($msgId)) {
            $msgId = 'SAMPLE'; // for preview mode
        }

        // STEP 06. Add Click Tracking
        //
        // @note: addTrackingUrl() must go before appendOpenTrackingUrl()
        // Enable click tracking
        if ($this->track_click) {
            $body = $this->addTrackingUrl($body, $msgId);
        }

        // STEP 07. Add Open Tracking
        if ($this->track_open) {
            $body = $this->appendOpenTrackingUrl($body, $msgId);
        }

        // STEP 08. Transform Tags
        if (!is_null($subscriber)) {
            // Transform tags
            $body = $this->tagMessage($body, $subscriber, $msgId, $server);
        }

        // STEP 09. Make CSS inline
        //
        // Transform CSS/HTML content to inline CSS
        // Be carefule, it will make
        //       <a href="{BUNSUBSCRIBE_URL}"
        // become
        //       <a href="%7BUNSUBSCRIBE_URL%7D"
        $body = $this->appendFooter($body, $this->getHtmlFooter());
        $body = $this->inlineHtml($body);

        return $body;
    }

    public function wooTransform($body)
    {
        if ($this->automation->getTrigger()->getOption('source_uid')) {
            $client = new \GuzzleHttp\Client();

            // find all links from contents
            $document = HtmlDomParser::str_get_html($body);

            // Woo Items List
            foreach ($document->find('[builder-element=ProductListElement]') as $element) {
                $max = $element->getAttribute('data-max-items');
                $display = $element->getAttribute('data-display');
                $sort = $element->getAttribute('data-sort-by');

                $request = request();
                $request->merge(['per_page' => $max]);
                $request->merge(['sort_by' => $sort]);

                $items = Product::search($request)->paginate($request->per_page)
                    ->map(function ($product, $key) {
                        return [
                            'id' => $product->uid,
                            'name' => $product->title,
                            'price' => $product->price,
                            'image' => action('ProductController@image', $product->uid),
                            'description' => substr(strip_tags($product->description), 0, 100),
                            'link' => action('ProductController@index'),
                        ];
                    })->toArray();
                $itemsHtml = [];
                foreach ($items as $item) {
                    // $element->find('.woo-items')[0]->innertext = 'dddddd';
                    $itemsHtml[] = '
                        <div class="woo-col-item mb-4 mt-4 col-md-' . (12/$display) . '">
                            <div class="">
                                <div class="img-col mb-3">
                                    <div class="d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <a style="width:100%" href="'.$item["link"].'" class="mr-4"><img width="100%" src="'.($item["image"] ? $item["image"] : url('images/cart_item.svg')).'" style="max-height:200px;max-width:100%;" /></a>
                                    </div>
                                </div>
                                <div class="">
                                    <p class="font-weight-normal product-name mb-1">
                                        <a style="color: #333;" href="'.$item["link"].'" class="mr-4">'.$item["name"].'</a>
                                    </p>
                                    <p class=" product-description">'.$item["description"].'</p>
                                    <p><strong>'.$item["price"].'</strong></p>
                                    <a href="'.$item["link"].'" style="background-color: #9b5c8f;
        border-color: #9b5c8f;" class="btn btn-primary text-white">
                                        ' . trans('messages.automation.view_more') . '
                                    </a>
                                </div>
                            </div>
                        </div>
                    ';
                }

                $element->find('.products')[0]->innertext = implode('', $itemsHtml);
            }

            // Woo Single Item
            foreach ($document->find('[builder-element=ProductElement]') as $element) {
                $productId = $element->getAttribute('product-id');

                if ($productId) {
                    $product = Product::findByUid($productId);

                    $item = [
                        'id' => $product->uid,
                        'name' => $product->title,
                        'price' => $product->price,
                        'image' => action('ProductController@image', $product->uid),
                        'description' => substr(strip_tags($product->description), 0, 100),
                        'link' => action('ProductController@index'),
                    ];
                    // $element->find('.product-name', 0)->innertext = $item["name"];
                    // $element->find('.product-description', 0)->innertext = $item["description"];
                    // $element->find('.product-link', 0)->href = $item["link"];
                    // $element->find('.product-price', 0)->innertext = $item["price"];
                    $element->find('.product-link img', 0)->src = $item["image"];
                    $html = $element->innertext;
                    $html = str_replace('*|PRODUCT_NAME|*', $item["name"], $html);
                    $html = str_replace('*|PRODUCT_DESCRIPTION|*', $item["description"], $html);
                    $html = str_replace('*|PRODUCT_URL|*', $item["link"], $html);
                    $html = str_replace('*|PRODUCT_PRICE|*', $item["price"], $html);
                    // $html = str_replace('*|PRODUCT_QUANTITY|*', $item["quantity"], $html);
                    $element->innertext = $html;
                }
            }

            $body = $document;
        }

        return $body;
    }

    /**
     * Replace link in text by click tracking url.
     *
     * @return text
     * @note addTrackingUrl() must go before appendOpenTrackingUrl()
     */
    public function addTrackingUrl($email_html_content, $msgId)
    {
        if (preg_match_all('/<a[^>]*href=["\'](?<url>http[^"\']*)["\']/i', $email_html_content, $matches)) {
            foreach ($matches[0] as $key => $href) {
                $url = $matches['url'][$key];

                $newUrl = route('clickTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId), 'url' => StringHelper::base64UrlEncode($url)], false);
                $newUrl = $this->buildTrackingUrl($newUrl);
                $newHref = str_replace($url, $newUrl, $href);

                // if the link contains UNSUBSCRIBE URL tag
                if (strpos($href, '{UNSUBSCRIBE_URL}') !== false) {
                    // just do nothing
                } elseif (preg_match('/{[A-Z0-9_]+}/', $href)) {
                    // just skip if the url contains a tag. For example: {UPDATE_PROFILE_URL}
                    // @todo: do we track these clicks?
                } else {
                    $email_html_content = str_replace($href, $newHref, $email_html_content);
                }
            }
        }

        return $email_html_content;
    }

    /**
     * Append Open Tracking URL
     * Append open-tracking URL to every email message.
     */
    public function appendOpenTrackingUrl($body, $msgId)
    {
        $path = route('openTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);
        $url = $this->buildTrackingUrl($path);

        return $this->appendToBody($body, '<img alt="" src="'.$url.'" width="0" height="0" alt="" style="visibility:hidden" />');
    }

    public function buildTrackingUrl($path)
    {
        $host = $this->getTrackingHost();

        return join_url($host, $path);
    }

    public function getTrackingHost()
    {
        if ($this->trackingDomain()->exists()) {
            return $this->trackingDomain->getUrl();
        } else {
            return config('app.url');
        }
    }

    /**
     * Check if email footer enabled.
     *
     * @return string
     */
    public function footerEnabled()
    {
        return ($this->automation->customer->getCurrentSubscription()->plan->getOption('email_footer_enabled') == 'yes') ? true : false;
    }

    /**
     * Get HTML footer.
     *
     * @return string
     */
    public function getHtmlFooter()
    {
        

        return $this->customer->footer_text;
    }

    /**
     * Append footer.
     *
     * @return string.
     */
    public function appendFooter($body, $footer)
    {
        return $this->appendToBody($body, $footer);
    }

    /**
     * Convert html to inline.
     *
     * @todo not very OOP here, consider moving this to a Helper instead
     */
    public function inlineHtml($html)
    {
        return $html;
    }

    /**
     * Find sending domain from email.
     *
     * @return mixed
     */
    public function findSendingDomain($email)
    {
        $domainName = substr(strrchr($email, '@'), 1);

        if ($domainName == false) {
            return;
        }

        $domain = $this->customer->activeDkimSendingDomains()->where('name', $domainName)->first();
        if (is_null($domain)) {
            $domain = SendingDomain::getAllAdminActive()->where('name', $domainName)->first();
        }

        return $domain;
    }

    /**
     * Sign the message with DKIM.
     *
     * @return mixed
     */
    public function sign($message)
    {
        $sendingDomain = $this->findSendingDomain($this->from_email);

        if (empty($sendingDomain)) {
            return $message;
        }

        $privateKey = $sendingDomain->dkim_private;
        $domainName = $sendingDomain->name;
        $selector = $sendingDomain->getDkimSelectorParts()[0];
        $signer = new \Swift_Signers_DKIMSigner($privateKey, $domainName, $selector);
        $signer->ignoreHeader('Return-Path');
        $message->attachSigner($signer);

        return $message;
    }

    public function isOpened($subscriber)
    {
        return $this->trackingLogs()->where('subscriber_id', $subscriber->id)
                            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')->exists();
    }

    public function isClicked($subscriber)
    {
        return $this->trackingLogs()->where('subscriber_id', $subscriber->id)
                            ->join('click_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')->exists();
    }

    /**
     * Fill email's fields from request.
     */
    public function fillAttributes($params)
    {
        $this->fill($params);

        // Tacking domain
        if (isset($params['custom_tracking_domain']) && $params['custom_tracking_domain'] && isset($params['tracking_domain_uid'])) {
            $tracking_domain = \Acelle\Model\TrackingDomain::findByUid($params['tracking_domain_uid']);
            if (is_object($tracking_domain)) {
                $this->tracking_domain_id = $tracking_domain->id;
            } else {
                $this->tracking_domain_id = null;
            }
        } else {
            $this->tracking_domain_id = null;
        }
    }

    public function appendToBody($html, $content)
    {
        $regexp = '/<\/body\s*>/i';
        if (preg_match($regexp, $html) == true) {
            //dd(preg_replace($regexp, $content.'</body>', $html));
            $newHtml = preg_replace($regexp, $content.'</body>', $html);
        } else {
           //dd($html.$content,'else');
            $newHtml =  $html.$content;
        }
        $regex = '../storage';
        //if(preg_match($regex,$newHtml)){
        //dd(url('storage'));
        
            return str_replace($regex,url('storage'),$newHtml);
    }

    public function isSetup()
    {
        return $this->subject && $this->reply_to && $this->from && $this->template;
    }

    public function deleteAndCleanup()
    {
        if ($this->template) {
            $this->template->deleteAndCleanup();
        }

        $this->delete();
    }
}
