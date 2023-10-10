<?php

/**
 * Campaign class.
 *
 * Model class for campaigns related functionalities.
 * This is the center of the application
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
use DB;
use Acelle\Model\SendingServer;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Illuminate\Validation\ValidationException;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Carbon\Carbon;
use League\Csv\Writer;
use Acelle\Library\ExtendedSwiftMessage;
use Acelle\Library\StringHelper;
use Acelle\Library\Tool;
use Acelle\Library\Rss;
use Acelle\Model\Setting;
use Validator;
use File;
use ZipArchive;
use KubAT\PhpSimple\HtmlDomParser;
use Exception;
use Acelle\Library\Traits\HasTemplate;
use Acelle\Jobs\LoadCampaign;
use Acelle\Jobs\ScheduleCampaign;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Acelle\Library\RouletteWheel;
use Acelle\Library\Traits\TrackJobs;
use Throwable;

class Campaign extends Model
{
    use HasTemplate, TrackJobs;

    protected $logger;

    // Campaign status
    const STATUS_NEW = 'new';
    const STATUS_QUEUING = 'queuing'; // equiv. to 'queue'
    const STATUS_QUEUED = 'queued'; // equiv. to 'queue'
    const STATUS_SENDING = 'sending';
    const STATUS_ERROR = 'error';
    const STATUS_DONE = 'done';
    const STATUS_PAUSED = 'paused';

    // Campaign types
    const TYPE_REGULAR = 'regular';
    const TYPE_PLAIN_TEXT = 'plain-text';

    // 4 types of delivery status for a given contact
    const DELIVERY_STATUS_FAILED = 'failed';
    const DELIVERY_STATUS_SENT = 'sent';
    const DELIVERY_STATUS_NEW = 'new';
    const DELIVERY_STATUS_SKIPPED = 'skipped';
    const DELIVERY_STATUS_BOUNCED = 'bounced';
    const DELIVERY_STATUS_FEEDBACK = 'feedback';

    // Content processing handler
    // Chain-of-Responsibilties pattern
    const HANDLE_PARSE_RSS = 'parse-rss';
    const HANDLE_TRACK_OPEN_CLICK = 'track-open-click';
    const HANDLE_TRANSFORM_TAG = 'transform-tag';
    const HANDLE_INLINE_CSS = 'inline-css';
    const HANDLE_TRANSFORM_URL = 'transform-url'; // make assets publicly available

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'run_at'];

    // parse content
    protected $parsedContent = null;

    /**
     * Get campaign's default mail list.
     */
    public function defaultMailList()
    {
        return $this->belongsTo('Acelle\Model\MailList', 'default_mail_list_id');
    }

    /**
     * Get campaign's associated mail list.
     */
    public function mailLists()
    {
        return $this->belongsToMany('Acelle\Model\MailList', 'campaigns_lists_segments');
    }

    /**
     * Campaign has many campaign links.
     */
    public function campaignLinks()
    {
        return $this->hasMany('Acelle\Model\CampaignLink');
    }

    /**
     * Get campaign's associated tracking domain.
     */
    public function trackingDomain()
    {
        return $this->belongsTo('Acelle\Model\TrackingDomain', 'tracking_domain_id');
    }

    /**
     * Get campaign validation rules.
     */
    public function rules($request=null)
    {
        $rules = array(
            'name' => 'required',
            'subject' => 'required',
            'from_email' => 'required|email',
            'from_name' => 'required',
            'reply_to' => 'required|email',
        );

        if ($this->use_default_sending_server_from_email) {
            $rules['from_email'] = 'nullable|email';
        } else {
            $rules['from_email'] = 'required|email';
        }

        // tracking domain
        if (isset($request) && $request->custom_tracking_domain) {
            $rules['tracking_domain_uid'] = 'required';
        }

        return $rules;
    }

    /**
     * Get the customer.
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    /**
     * Get campaign tracking logs.
     *
     * @return mixed
     */
    public function trackingLogs()
    {
        return $this->hasMany('Acelle\Model\TrackingLog');
    }

    /**
     * Get campaign bounce logs.
     *
     * @return mixed
     */
    public function bounceLogs()
    {
        return BounceLog::select('bounce_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'bounce_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign open logs.
     *
     * @return mixed
     */
    public function openLogs()
    {
        return OpenLog::select('open_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign click logs.
     *
     * @return mixed
     */
    public function clickLogs()
    {
        return ClickLog::select('click_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign feedback loop logs.
     *
     * @return mixed
     */
    public function feedbackLogs()
    {
        return FeedbackLog::select('feedback_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'feedback_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign unsubscribe logs.
     *
     * @return mixed
     */
    public function unsubscribeLogs()
    {
        return UnsubscribeLog::select('unsubscribe_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'unsubscribe_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);
    }

    /**
     * Get campaign list segment.
     *
     * @return mixed
     */
    public function listsSegments()
    {
        return $this->hasMany('Acelle\Model\CampaignsListsSegment');
    }

    /**
     * Get campaign lists segments.
     *
     * @return mixed
     */
    public function getListsSegments()
    {
        $lists_segments = $this->listsSegments;

        if ($lists_segments->isEmpty()) {
            $lists_segment = new CampaignsListsSegment();
            $lists_segment->campaign_id = $this->id;
            $lists_segment->is_default = true;

            $lists_segments->push($lists_segment);
        }

        return $lists_segments;
    }

    /**
     * Get campaign lists segments group by list.
     *
     * @return mixed
     */
    public function getListsSegmentsGroups()
    {
        $lists_segments = $this->getListsSegments();
        $groups = [];

        foreach ($lists_segments as $lists_segment) {
            if (!isset($groups[$lists_segment->mail_list_id])) {
                $groups[$lists_segment->mail_list_id] = [];
                $groups[$lists_segment->mail_list_id]['list'] = $lists_segment->mailList;
                if ($this->default_mail_list_id == $lists_segment->mail_list_id) {
                    $groups[$lists_segment->mail_list_id]['is_default'] = true;
                } else {
                    $groups[$lists_segment->mail_list_id]['is_default'] = false;
                }
                $groups[$lists_segment->mail_list_id]['segment_uids'] = [];
            }
            if (is_object($lists_segment->segment) && !in_array($lists_segment->segment->uid, $groups[$lists_segment->mail_list_id]['segment_uids'])) {
                $groups[$lists_segment->mail_list_id]['segment_uids'][] = $lists_segment->segment->uid;
            }
        }

        return $groups;
    }

    /**
     * Prepare the email content using Swift Mailer.
     *
     * @input object subscriber
     * @input object sending server
     *
     * @return MIME text message
     */
    public function prepareEmail($subscriber, $server = null)
    {
        $this->updateLinks();

        // build the message
        $customHeaders = $this->getCustomHeaders($subscriber, $this);
        $msgId = $customHeaders['X-Acelle-Message-Id'];

        $message = new ExtendedSwiftMessage();
        $message->setId($msgId);

        if ($this->type == self::TYPE_REGULAR) {
            $message->setContentType('text/html; charset=utf-8');
        } else {
            $message->setContentType('text/plain; charset=utf-8');
        }

        foreach ($customHeaders as $key => $value) {
            $message->getHeaders()->addTextHeader($key, $value);
        }

        // @TODO for AWS, setting returnPath requires verified domain or email address
        if (!is_null($server) && $server->allowCustomReturnPath()) {
            $returnPath = $server->getVerp($subscriber->email);
            if ($returnPath) {
                $message->setReturnPath($returnPath);
            }
        }
        
        $message->setSubject($this->getSubject($subscriber, $msgId));

        if ($this->useSendingServerFromEmailAddress()) {
            $message->setFrom($server->default_from_email);
        } else {
            $message->setFrom(array($this->from_email => $this->from_name));
        }
        
        $message->setTo($subscriber->email);

        if (!empty(Setting::get('campaign.bcc'))) {
            $addresses = array_filter(preg_split('/\s*,\s*/', Setting::get('campaign.bcc')));
            $message->setBcc($addresses);
        }

        if (!empty(Setting::get('campaign.cc'))) {
            $addresses = array_filter(preg_split('/\s*,\s*/', Setting::get('campaign.cc')));
            $message->setCc($addresses);
        }

        $message->setReplyTo($this->reply_to);
        $message->setEncoder(new \Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        $message->addPart($this->getPlainContent($subscriber, $msgId, $server), 'text/plain');
        if ($this->type == self::TYPE_REGULAR) {
            $message->addPart($this->getHtmlContent($subscriber, $msgId, $server), 'text/html');
        }

        if ($this->sign_dkim) {
            $message = $this->sign($message);
        }

        //@todo attach function used for any attachment of Campaign
        $path_campaign = $this->getAttachmentPath();
        if (is_dir($path_campaign)) {
            $files = File::allFiles($path_campaign);
            foreach ($files as $file) {
                $attachment = \Swift_Attachment::fromPath((string) $file);
                $message->attach($attachment);
                // This is used by certain delivery services like ElasticEmail
                $message->extAttachments[] = [ 'path' => (string) $file, 'type' => $attachment->getContentType()];
            }
        }

        return array($message, $msgId);
    }

    /**
     * Check if the campaign setting is "use sending server's FROM email address".
     *
     * @return mixed
     */
    private function useSendingServerFromEmailAddress()
    {
        return $this->use_default_sending_server_from_email == true;
    }

    /**
     * Reset max_execution_time so that command can run for a long time without being terminated.
     *
     * @return mixed
     */
    public static function resetMaxExecutionTime()
    {
        try {
            set_time_limit(0);
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '-1');
        } catch (\Exception $e) {
            MailLog::warning('Cannot reset max_execution_time: '.$e->getMessage());
        }
    }

    /**
     * Mark the campaign as 'done' or 'sent'.
     */
    public function setDone()
    {
        $this->status = self::STATUS_DONE;
        $this->last_error = null;
        $this->save();
    }

    /**
     * Mark the campaign as 'sending'.
     */
    public function setSending()
    {
        $this->status = self::STATUS_SENDING;
        $this->running_pid = getmypid();
        $this->delivery_at = Carbon::now();
        $this->save();
    }

    /**
     * Check if the campaign is in the "SENDING" status;.
     */
    public function isSending()
    {
        return $this->status == self::STATUS_SENDING;
    }

    /**
     * Check if the campaign is in the "DONE" status;.
     */
    public function isDone()
    {
        return $this->status == self::STATUS_DONE;
    }

    /**
     * Check if the campaign is ready to start.
     */
    public function isQueued()
    {
        return $this->status == self::STATUS_QUEUED;
    }

    /**
     * Mark the campaign as 'ready' (which is equiv. to 'queued').
     */
    public function setQueued()
    {
        $this->status = self::STATUS_QUEUED;
        $this->save();
        return $this;
    }

    /**
     * Mark the campaign as 'ready' (which is equiv. to 'queued').
     */
    public function setQueuing()
    {
        $this->status = self::STATUS_QUEUING;
        $this->save();
        return $this;
    }

    /**
     * Mark the campaign as 'done' or 'sent'.
     */
    public function setError($error = null)
    {
        $this->status = self::STATUS_ERROR;
        $this->last_error = $error;
        $this->save();
        return $this;
    }

    /**
     * Log delivery message, used for later tracking.
     */
    public function trackMessage($response, $subscriber, $server, $msgId)
    {
        // @todo: customerneedcheck
        $params = array_merge(array(
                'campaign_id' => $this->id,
                'message_id' => $msgId,
                'subscriber_id' => $subscriber->id,
                'sending_server_id' => $server->id,
                'customer_id' => $this->customer->id,
            ), $response);

        if (!isset($params['runtime_message_id'])) {
            $params['runtime_message_id'] = $msgId;
        }

        // create tracking log for message
        TrackingLog::create($params);

        // increment customer quota usage
        $this->customer->countUsage();
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
     * Append Open Tracking URL
     * Append open-tracking URL to every email message.
     */
    public function appendOpenTrackingUrl($body, $msgId)
    {
        $path = route('openTrackingUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);
        $url = $this->buildTrackingUrl($path);

        return $this->appendToBody($body, '<img alt="" src="'.$url.'" width="0" height="0" alt="" style="visibility:hidden" />');
    }

    /**
     * Build Email Custom Headers.
     *
     * @return Hash list of custom headers
     */
    public function getCustomHeaders($subscriber, $server)
    {
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from_email));

        return array(
            'X-Acelle-Campaign-Id' => $this->uid,
            'X-Acelle-Subscriber-Id' => $subscriber->uid,
            'X-Acelle-Customer-Id' => $this->customer->uid,
            'X-Acelle-Message-Id' => $msgId,
            'X-Acelle-Sending-Server-Id' => $server->uid,
            'List-Unsubscribe' => '<'.$this->generateUnsubscribeUrl($msgId, $subscriber).'>',
            'Precedence' => 'bulk',
        );
    }

    /**
     * Build Email HTML content.
     *
     * @return string
     */
    public function getHtmlContent($subscriber = null, $msgId = null, $server = null, $handlers = [])
    {
        if (is_null($this->parsedContent)) {
            // STEP 01. Get RAW content
            $body = $this->getTemplateContent();

            // STEP 02. Append footer
            if ($this->footerEnabled()) {
                $body = $this->appendFooter($body, $this->getHtmlFooter());
            }
            
            // STEP 03. Parse RSS
            if (Setting::isYes('rss.enabled')) {
                $body = Rss::parse($body);
            }

            // STEP 04. Replace Bare linefeed
            // Replace bare line feed char which is not accepted by Outlook, Yahoo, AOL...
            $body = StringHelper::replaceBareLineFeed($body);

            // "Cache" it, do not repeat this task for every subscriber in the loop
            $this->parsedContent = $body;
        } else {
            // Retrieve content from cache
            $body = $this->parsedContent;
        }

        // STEP 04.1. Check woocommerce items
        $body = $this->wooTransform($body);

        // STEP 05. Transform URLs
        $body = $this->template->getContentWithTransformedAssetsUrls($this->getTrackingHost());

        // STEP 05.1. Remove title tag from html
        $body = strip_tags_only($body, 'title');

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
        $body = $this->inlineHtml($body);


        return $body;
    }

    /**
     * Check if email footer enabled.
     *
     * @return string
     */
    public function footerEnabled()
    {
        return ($this->customer->getCurrentSubscription()->plan->getOption('email_footer_enabled') == 'yes') ? true : false;
    }

    /**
     * Get HTML footer.
     *
     * @return string
     */
    public function getHtmlFooter()
    {
        return $this->customer->getCurrentSubscription()->plan->getOption('html_footer');
    }

    /**
     * Get PLAIN TEXT footer.
     *
     * @return string
     */
    public function getPlainTextFooter()
    {
        return $this->customer->getCurrentSubscription()->plan->getOption('plain_text_footer');
    }

    /**
     * Build Email HTML content.
     *
     * @return string
     */
    public function getPlainContent($subscriber, $msgId, $server = null)
    {
        $plain = $this->tagMessage($this->plain, $subscriber, $msgId, $server);

        // Append footer
        if ($this->footerEnabled()) {
            $plain = $this->appendFooter($plain, $this->getPlainTextFooter());
        }

        // Replace bare line feed char which is not accepted by Outlook, Yahoo, AOL...
        $plain = StringHelper::replaceBareLineFeed($plain);

        return $plain;
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subject', 'from_name', 'from_email',
        'reply_to', 'track_open',
        'track_click', 'sign_dkim', 'track_fbl',
        'html', 'plain', 'template_source',
        'tracking_domain_id', 'use_default_sending_server_from_email',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'mail_list_uid' => 'required',
    );

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

    /**
     * Get all items.
     *
     * @return collect
     */
    public static function getAll()
    {
        return self::select('campaigns.*');
    }

    /**
     * Get select options.
     *
     * @return array
     */
    public static function getSelectOptions($customer = null, $status = null)
    {
        $query = self::getAll();
        if (is_object($customer)) {
            $query = $query->where('customer_id', '=', $customer->id);
        }
        if (isset($status)) {
            $query = $query->where('status', '=', $status);
        }
        $options = $query->orderBy('created_at', 'DESC')->get()->map(function ($item) {
            return ['value' => $item->uid, 'text' => $item->name];
        });

        return $options;
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            $uid = uniqid();
            $item->uid = $uid;
        });
    }

    /**
     * Get current links of campaign.
     */
    public function getLinks()
    {
        return $this->campaignLinks()->get();
    }

    /**
     * Get urls from campaign html.
     */
    public function getUrls()
    {
        // Find all links in campaign content
        preg_match_all('/<a[^>]*href=["\'](?<url>http[^"\']*)["\']/i', $this->getTemplateContent(), $matches);
        $hrefs = array_unique($matches['url']);

        $urls = [];
        foreach ($hrefs as $href) {
            if (preg_match('/^http/i', $href) && strpos($href, '{UNSUBSCRIBE_URL}') === false) {
                $urls[] = strtolower(trim($href));
            }
        }

        return $urls;
    }

    /**
     * Update campaign links.
     */
    public function updateLinks()
    {
        $this->campaignLinks()->delete();

        foreach ($this->getUrls() as $url) {
            // Campaign link
            if ($this->campaignLinks()->where('url', '=', $url)->count() == 0) {
                $cl = new CampaignLink();
                $cl->campaign_id = $this->id;
                $cl->url = $url;
                $cl->save();
            }
        }
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
     * CHeck UNSUBSCRIBE_URL.
     *
     * @return object
     */
    public function unsubscribe_url_valid()
    {
        if ($this->type != 'plain-text' &&
           $this->customer->getOption('unsubscribe_url_required') == 'yes' &&
            strpos($this->getTemplateContent(), '{UNSUBSCRIBE_URL}') == false
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Get max step.
     *
     * @return object
     */
    public function step()
    {
        $step = 0;

        // Step 1
        if (is_object($this->defaultMailList)) {
            $step = 1;
        } else {
            return $step;
        }

        // Step 2
        if (!empty($this->name) && !empty($this->subject) && !empty($this->from_name)
                && !empty($this->from_email) && !empty($this->reply_to)) {
            $step = 2;
        } else {
            return $step;
        }

        // Step 3
        if (($this->template || $this->type == 'plain-text') && !empty($this->plain)) {
            $step = 3;
        } else {
            return $step;
        }

        // Step 4
        if (isset($this->run_at) && $this->run_at != '0000-00-00 00:00:00') {
            $step = 4;
        } else {
            return $step;
        }

        // Step 5
        // @todo: consider removing this check!
        if (is_object($this->subscribers([], ['email_verifications'])->limit(1)->first())) {
            $step = 5;
        } else {
            return $step;
        }

        return $step;
    }

    /**
     * Filter items.
     *
     * @return collect
     */
    public static function filter($request)
    {
        $customer = $request->user()->customer;
        $query = self::where('customer_id', '=', $customer->id);

        // Get campaign from ... (all|normal|automated)
        if ($request->source == 'template') {
            $query = $query->where('html', '!=', null);
        }

        // Keyword
        if (!empty(trim($request->keyword))) {
            $query = $query->where('name', 'like', '%'.$request->keyword.'%');
        }

        // Status
        if (!empty(trim($request->statuses))) {
            $query = $query->whereIn('status', explode(',', $request->statuses));
        }

        return $query;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function search($request)
    {
        $query = self::filter($request);

        $query = $query->orderBy($request->sort_order, $request->sort_direction);

        return $query;
    }

    /**
     * Create customer action log.
     *
     * @param string   $cat
     * @param Customer $customer
     * @param array    $add_datas
     */
    public function log($name, $customer, $add_datas = [])
    {
        $data = [
                'id' => $this->id,
                'name' => $this->name,
        ];

        if (is_object($this->defaultMailList)) {
            $data['list_id'] = $this->default_mail_list_id;
            $data['list_name'] = $this->defaultMailList->name;
        }

        if (is_object($this->segment)) {
            $data['segment_id'] = $this->segment_id;
            $data['segment_name'] = $this->segment->name;
        }

        $data = array_merge($data, $add_datas);

        \Acelle\Model\Log::create([
                                'customer_id' => $customer->id,
                                'type' => 'campaign',
                                'name' => $name,
                                'data' => json_encode($data),
                            ]);
    }

    /**
     * Count delivery processed.
     *
     * @return number
     */
    public function trackingCount()
    {
        return $this->trackingLogs()->count();
    }

    /**
     * Count delivery processed.
     *
     * @return number
     */
    public function deliveredCount()
    {
        // including bounced, feedbcak...
        return $this->trackingLogs()->where('status', '!=', TrackingLog::STATUS_FAILED)->count();
    }

    /**
     * Count failed processed.
     *
     * @return number
     */
    public function failedCount()
    {
        return $this->trackingLogs()->where('status', '=', TrackingLog::STATUS_FAILED)->count();
    }

    /**
     * Count failed processed.
     *
     * @return number
     */
    public function notDeliveredCount()
    {
        $subscribersCountUniq = $this->subscribers([], ['email_verifications'])->count();
        return $subscribersCountUniq - $this->deliveredCount();
    }

    /**
     * Count delivery success rate.
     *
     * @return number
     */
    public function deliveredRate($cache = false)
    {
        $total = $this->subscribersCount($cache);

        if ($total == 0) {
            return 0;
        }

        return $this->deliveredCount() / $total;
    }

    /**
     * Count delivery success rate.
     *
     * @return number
     */
    public function failedRate($cache = false)
    {
        $total = $this->subscribersCount($cache);

        if ($total == 0) {
            return 0;
        }

        return $this->failedCount() / $total;
    }

    /**
     * Count delivery success rate.
     *
     * @return number
     */
    public function notDeliveredRate($cache = false)
    {
        $total = $this->subscribersCount($cache);

        if ($total == 0) {
            return 0;
        }

        return $this->notDeliveredCount() / $total;
    }

    /**
     * Count click.
     *
     * @return number
     */
    public function clickCount($start = null, $end = null)
    {
        $query = $this->clickLogs();

        if (isset($start)) {
            $query = $query->where('click_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('click_logs.created_at', '<=', $end);
        }

        return $query->count();
    }

    /**
     * Url count.
     *
     * @return number
     */
    public function urlCount()
    {
        return $this->campaignLinks()->count();
    }

    /**
     * Click rate.
     *
     * @return number
     */
    public function clickedLinkCount()
    {
        return $this->clickLogs()->distinct('url')->count('url');
    }

    /**
     * Click rate.
     *
     * @return number
     */
    public function clickRate()
    {
        $url_count = $this->urlCount();

        if ($url_count == 0) {
            return 0;
        }

        return round(($this->clickedLinkCount() / $url_count), 2);
    }

    /**
     * Count unique clicked opened emails.
     *
     * @return number
     */
    public function clickedEmailsCount()
    {
        $query = $this->clickLogs();

        return $query->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Click a link rate.
     *
     * @return number
     */
    public function clickALinkRate()
    {
        $open_count = $this->openCount();

        if ($open_count == 0) {
            return 0;
        }

        return $this->clickCount() / $open_count;
    }

    /**
     * Clicked emails count.
     *
     * @return number
     */
    public function clickedEmailsRate()
    {
        $open_count = $this->openUniqCount();

        if ($open_count == 0) {
            return 0;
        }

        return $this->clickedEmailsCount() / $open_count;
    }

    /**
     * Count click.
     *
     * @return number
     */
    public function clickPerUniqOpen()
    {
        $open_count = $this->openCount();

        if ($open_count == 0) {
            return 0;
        }

        return $this->clickCount() / $open_count;
    }

    /**
     * Count abuse feedback.
     *
     * @return number
     */
    public function abuseFeedbackCount()
    {
        return $this->feedbackLogs()->where('feedback_type', '=', 'abuse')->count();
    }

    /**
     * Count open.
     *
     * @return number
     */
    public function openCount()
    {
        return $this->openLogs()->count();
    }

    /**
     * Not open count.
     *
     * @return number
     */
    public function notOpenCount($cache = false)
    {
        return $this->subscribersCount($cache) - $this->openUniqCount();
    }

    /**
     * Count unique open.
     *
     * @return number
     */
    public function openUniqCount($start = null, $end = null)
    {
        $query = $this->openLogs();
        if (isset($start)) {
            $query = $query->where('open_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('open_logs.created_at', '<=', $end);
        }

        return $query->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Open rate.
     *
     * @return number
     */
    public function openRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->openCount() / $delivered_count;
    }

    /**
     * Not open rate.
     *
     * @return number
     */
    public function notOpenRate($cache = false)
    {
        $total = $this->subscribersCount($cache);

        if ($total == 0) {
            return 0;
        }

        return $this->notOpenCount($cache) / $total;
    }

    /**
     * Count unique open rate.
     *
     * @return number
     */
    public function openUniqRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->openUniqCount() / $delivered_count;
    }

    /**
     * Count bounce back.
     *
     * @return number
     */
    public function feedbackCount()
    {
        return $this->feedbackLogs()->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count feedback rate.
     *
     * @return number
     */
    public function feedbackRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->feedbackCount() / $delivered_count;
    }

    /**
     * Count bounce back.
     *
     * @return number
     */
    public function bounceCount()
    {
        return $this->bounceLogs()->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count bounce rate.
     *
     * @return number
     */
    public function bounceRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->bounceCount() / $delivered_count;
    }

    /**
     * Count hard bounce.
     *
     * @return number
     */
    public function hardBounceCount()
    {
        return $this->campaign_bounce_logs()->where('bounce_type', '=', 'hard')->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count hard bounce rate.
     *
     * @return number
     */
    public function hardBounceRate()
    {
        $delivered_processed_count = $this->deliveryProcessedCount();

        if ($delivered_processed_count == 0) {
            return 0;
        }

        return $this->hardBounceCount() / $delivered_processed_count;
    }

    /**
     * Count soft bounce.
     *
     * @return number
     */
    public function softBounceCount()
    {
        return $this->campaign_bounce_logs()->where('bounce_type', '=', 'soft')->distinct('subscriber_id')->count('subscriber_id');
    }

    /**
     * Count soft bounce rate.
     *
     * @return number
     */
    public function softBounceRate()
    {
        $tracking_count = $this->trackingCount();

        if ($tracking_count == 0) {
            return 0;
        }

        return $this->softBounceCount() / $tracking_count;
    }

    /**
     * Count unsubscibe.
     *
     * @return number
     */
    public function unsubscribeCount()
    {
        return $this->unsubscribeLogs()->distinct('unsubscribe_logs.subscriber_id')->count();
    }

    /**
     * Count unsubscibe rate.
     *
     * @return number
     */
    public function unsubscribeRate()
    {
        $delivered_count = $this->deliveredCount();

        if ($delivered_count == 0) {
            return 0;
        }

        return $this->unsubscribeCount() / $delivered_count;
    }

    /**
     * Get last click.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastClick()
    {
        return $this->clickLogs()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get last open.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastOpen()
    {
        return $this->openLogs()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Get last open list.
     *
     * @param number $number
     *
     * @return collect
     */
    public function lastOpens($number)
    {
        return $this->openLogs()->orderBy('created_at', 'desc')->limit($number);
    }

    /**
     * Get last opened time.
     *
     * @return datetime
     */
    public function getLastOpen()
    {
        $last = $this->campaign_track_opens()->orderBy('created_at', 'desc')->first();

        return is_object($last) ? $last->created_at : null;
    }

    public static function topOpens($number = 5, $customer = null)
    {
        $records = self::select('campaigns.name', 'campaigns.id', 'campaigns.uid')
            ->addSelect(DB::raw('count(*) as aggregate'))
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaigns.id')
            ->join('open_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id');

        if (isset($customer)) {
            $records = $records->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('campaigns.name', 'campaigns.id', 'campaigns.uid')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    public static function topClicks($number = 5, $customer = null)
    {
        $records = self::select('campaigns.name', 'campaigns.id', 'campaigns.uid')
            ->addSelect(DB::raw('count(*) as aggregate'))
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaigns.id')
            ->join('click_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id');

        if (isset($customer)) {
            $records = $records->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('campaigns.name', 'campaigns.id', 'campaigns.uid')
                    ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    public static function topLinks($number = 5, $customer = null)
    {
        $records = CampaignLink::select('campaign_links.url')
            ->addSelect(DB::raw('count(*) as aggregate'))
            ->join('tracking_logs', 'tracking_logs.campaign_id', '=', 'campaign_links.campaign_id')
            ->join('click_logs', function ($join) {
                $join->on('click_logs.message_id', '=', 'tracking_logs.message_id')
                ->on('click_logs.url', '=', 'campaign_links.url');
            });

        if (isset($customer)) {
            $records = $records->join('campaigns', 'campaign_links.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('campaign_links.url')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public function getTopLinks($number = 5)
    {
        $records = $this->clickLogs()
            ->select('click_logs.url')
            ->addSelect(DB::raw('count(*) as aggregate'))
            ->groupBy('click_logs.url');

        return $records->take($number);
    }

    /**
     * Campaign top 5 clicks.
     *
     * @return datetime
     */
    public function getTopOpenSubscribers($number = 5)
    {
        $query = $this->openLogs()->select('subscribers.email', 'subscribers.id')
                        ->addSelect(DB::raw('count(*) as count'))
                        ->join('subscribers', 'subscribers.id', '=', 'tracking_logs.subscriber_id')
                        ->groupBy('subscribers.email', 'subscribers.id')
                        ->orderBy('count', 'desc');

        return $query->take($number);
    }

    /**
     * Campaign top 5 open location.
     *
     * @return datetime
     */
    public function topLocations($number = 5, $customer = null)
    {
        $records = IpLocation::select('ip_locations.ip_address', 'ip_locations.country_code', 'ip_locations.country_name')
            ->addSelect(DB::raw('count(*) as aggregate'))
            ->join('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($customer)) {
            $records = $records->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('ip_locations.ip_address', 'ip_locations.country_code', 'ip_locations.country_name')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 open countries.
     *
     * @return datetime
     */
    public function topCountries($number = 5, $customer = null)
    {
        $records = IpLocation::select('ip_locations.country_name', 'ip_locations.country_code', 'ip_locations.ip_address')
            ->addSelect(DB::raw('count(*) as aggregate'))
            ->join('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($customer)) {
            $records = $records
                ->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records
            ->groupBy('ip_locations.country_name', 'ip_locations.country_code', 'ip_locations.ip_address')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    /**
     * Campaign top 5 click countries.
     *
     * @return datetime
     */
    public function topClickCountries($number = 5, $customer = null)
    {
        $records = IpLocation::select('ip_locations.country_name', 'ip_locations.country_code', 'ip_locations.ip_address')
            ->addSelect(DB::raw('count(*) as aggregate'))
            ->join('click_logs', 'click_logs.ip_address', '=', 'ip_locations.ip_address')
            ->join('tracking_logs', 'click_logs.message_id', '=', 'tracking_logs.message_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        if (isset($customer)) {
            $records = $records->join('campaigns', 'tracking_logs.campaign_id', '=', 'campaigns.id')
                ->where('campaigns.customer_id', '=', $customer->id);
        }

        $records = $records->groupBy('ip_locations.country_name', 'ip_locations.country_code', 'ip_locations.ip_address')
            ->orderBy('aggregate', 'desc');

        return $records->take($number);
    }

    public function generateUnsubscribeUrl($msgId, $subscriber)
    {
        // in case of a fake object, for sending test email
        if (is_a($subscriber, 'stdClass')) {
            // @TODO workaround here
            $path = route('unsubscribeUrl', [ 'subscriber' => 'unknown', 'message_id' => StringHelper::base64UrlEncode($msgId)], false);

            return $path;
        }

        // OPTION 1: immediately opt out
        $path = $subscriber->generateUnsubscribeUrl($msgId, $absoluteUrl = false);

        // OPTION 2: unsubscribe form, @IMPORTANT: it does not produce tracking log!!
        //$path = route('unsubscribeForm', ['list_uid' => $subscriber->mailList->uid, 'code' => $subscriber->getSecurityToken('unsubscribe'), 'uid' => $subscriber->uid], false);

        return $this->buildPublicUrl($path);
    }

    public function generateWebViewerUrl($msgId)
    {
        $path = route('webViewerUrl', ['message_id' => StringHelper::base64UrlEncode($msgId)], false);

        return $this->buildPublicUrl($path);
    }

    public function generateUpdateProfileUrl($subscriber)
    {
        $path = route('updateProfileUrl', ['list_uid' => $this->defaultMailList->uid, 'uid' => $subscriber->uid, 'code' => $subscriber->getSecurityToken('update-profile')], false);

        return $this->buildPublicUrl($path);
    }

    public function buildTrackingUrl($path)
    {
        $host = $this->getTrackingHost();

        return join_url($host, $path);
    }

    public function buildPublicUrl($path)
    {
        return join_url($this->getTrackingHost(), $path);
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
     * Campaign locations.
     *
     * @return datetime
     */
    public function locations()
    {
        $records = IpLocation::select('ip_locations.*', 'open_logs.created_at as open_at', 'subscribers.email as email')
            ->leftJoin('open_logs', 'open_logs.ip_address', '=', 'ip_locations.ip_address')
            ->leftJoin('tracking_logs', 'open_logs.message_id', '=', 'tracking_logs.message_id')
            ->leftJoin('subscribers', 'subscribers.id', '=', 'tracking_logs.subscriber_id')
            ->where('tracking_logs.campaign_id', '=', $this->id);

        return $records;
    }

    /**
     * Replace link in text by click tracking url.
     *
     * @return textaddTrackingUrl
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
     * Type of campaigns.
     *
     * @return object
     */
    public static function types()
    {
        return [
            'regular' => [
                'icon' => 'icon-magazine',
            ],
            'plain-text' => [
                'icon' => 'icon-file-text2',
            ],
        ];
    }

    /**
     * Copy new campaign.
     */
    public function copy($name)
    {
        $copy = new self();

        // Foreign key
        $copy->customer_id = $this->customer_id;
        $copy->default_mail_list_id = $this->default_mail_list_id;
        $copy->tracking_domain_id = $this->tracking_domain_id;

        // Overwrite attributes
        $copy->name = $name;
        $copy->status = self::STATUS_NEW;

        // Overwrit date/time
        $now = Carbon::now();
        $copy->created_at = $now;
        $copy->updated_at = $now;

        // Other attributes to clone
        $attributes = [
            'type',
            'subject',
            'preheader',
            'from_email',
            'from_name',
            'reply_to',
            'sign_dkim',
            'track_open',
            'track_click',
            'use_default_sending_server_from_email',
        ];
        
        foreach ($attributes as $attribute) {
            $copy[$attribute] = $this[$attribute];
        }
        
        // Save to DB
        $copy->save();

        // Lists segments
        foreach ($this->listsSegments as $listSegment) {
            $newListSegment = $copy->listsSegments()->make();

            $newListSegment->mail_list_id = $listSegment->mail_list_id;
            $newListSegment->segment_id = $listSegment->segment_id;
            $newListSegment->created_at = $now;
            $newListSegment->updated_at = $now;

            $newListSegment->save();
        }

        // copy template
        if (!is_null($this->template)) {
            $copy->setTemplate($this->template);
        }

        // refresh to update cache (otherwise, list-segment information will not be available yet)
        $copy->refresh();
        $copy->updateCache();

        return $copy;
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
     * Send a test email for testing campaign.
     */
    public function sendTestEmail($email)
    {

        try {
            //MailLog::info('Sending test email for campaign `'.$this->name.'`');
            MailLog::info('Sending test email to `'.$email.'`');

            // @todo: only send a test message when campaign sufficient information is available

            // build a temporary subscriber oject used to pass through the sending methods
            $subscriber = $this->createStdClassSubscriber(['email' => $email]);

            // Pick up an available sending server
            // Throw exception in case no server available
            $server = $this->pickSendingServer();

            // build the message from campaign information
            list($message, $msgId) = $this->prepareEmail($subscriber, $server);
            //print_r($message);
            //die;
            // actually send
            // @todo consider using queue here
            $result = $server->send($message);

            // examine the result from sending server
            if (array_has($result, 'error')) {
                throw new \Exception($result['error']);
            }

            return [
                'status' => 'success',
                'message' => trans('messages.campaign.test_sent'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get the delay time before sending.
     */
    public function getDelayInSeconds()
    {
        $now = Carbon::now();

        if ($now->gte($this->run_at)) {
            return 0;
        } else {
            return $this->run_at->diffInSeconds($now);
        }
    }

    /**
     * Re-send the campaign for sending.
     */
    public function resend($filter = 'not_receive') // not_receive | not_open | not_click
    {
        // clean up failed log so that they will be included in resend
        switch ($filter) {
            case 'not_receive':
                $this->cleanupFailedLog();
                break;
            case 'not_open':
                $this->cleanupFailedLog();
                $this->cleanupNotOpenLog();
                break;
            case 'not_click':
                $this->cleanupFailedLog();
                $this->cleanupNotClickLog();
                break;
            default:
                throw new \Exception("Unknown campaign RESEND type: ".$filter);
                break;
        }

        // and queue again
        $this->schedule();
    }

    /**
     * Re-send the campaign for sending.
     */
    public function cleanupFailedLog()
    {
        // clean up failed log so that they will be included in resend
        $recipients = $this->trackingLogs()->where('status', SendingServer::DELIVERY_STATUS_FAILED);
        MailLog::warning('Resend to those who failed to deliver: '.$recipients->count());
        $recipients->delete();
    }

    public function cleanupNotOpenLog()
    {
        // clean up failed log so that they will be included in resend
        $recipients = $this->trackingLogs()
                         ->leftJoin('open_logs', 'tracking_logs.message_id', 'open_logs.message_id')
                         ->whereNull('open_logs.id');
        MailLog::warning('Resend to those who did not open: '.$recipients->count());
        $recipients->delete();
    }

    public function cleanupNotClickLog()
    {
        // clean up failed log so that they will be included in resend
        $recipients = $this->trackingLogs()
                         ->leftJoin('click_logs', 'tracking_logs.message_id', 'click_logs.message_id')
                         ->whereNull('click_logs.id');
        MailLog::warning('Resend to those who did not click: '.$recipients->count());
        $recipients->delete();
    }

    /**
     * Create a stdClass subscriber (for sending a campaign test email)
     * The campaign sending functions take a subscriber object as input
     * However, a test email address is not yet a subscriber object, so we have to build a fake stdClass object
     * which can be used as a real subscriber.
     *
     * @param array $subscriber
     */
    public function createStdClassSubscriber($subscriber)
    {
        // default attributes that are required
        $jsonObj = [
            'uid' => uniqid(),
        ];

        // append the customer specified attributes and build a stdClass object
        $stdObj = json_decode(json_encode(array_merge($jsonObj, $subscriber)));

        return $stdObj;
    }

    /**
     * Check if the given variable is a subscriber object (for actually sending a campaign)
     * Or a stdClass subscriber (for sending test email).
     *
     * @param object $object
     */
    public function isStdClassSubscriber($object)
    {
        return get_class($object) == 'stdClass';
    }

    /**
     * Get information from mail list.
     *
     * @param void
     */
    public function getInfoFromMailList($list)
    {
        $this->from_name = !empty($this->from_name) ? $this->from_name : $list->from_name;
        $this->from_email = !empty($this->from_email) ? $this->from_email : $list->from_email;
        $this->subject = !empty($this->subject) ? $this->subject : $list->default_subject;
    }

    /**
     * Get type select options.
     *
     * @return array
     */
    public static function getTypeSelectOptions()
    {
        return [
            ['text' => trans('messages.'.self::TYPE_REGULAR), 'value' => self::TYPE_REGULAR],
            ['text' => trans('messages.'.self::TYPE_PLAIN_TEXT), 'value' => self::TYPE_PLAIN_TEXT],
        ];
    }

    /**
     * The validation rules for automation trigger.
     *
     * @var array
     */
    public function recipientsRules($params = [])
    {
        $rules = [
            'lists_segments' => 'required',
        ];

        if (isset($params['lists_segments'])) {
            foreach ($params['lists_segments'] as $key => $param) {
                $rules['lists_segments.'.$key.'.mail_list_uid'] = 'required';
            }
        }

        return $rules;
    }

    /**
     * Fill recipients by params.
     *
     * @var void
     */
    public function fillRecipients($params = [])
    {
        if (isset($params['lists_segments'])) {
            foreach ($params['lists_segments'] as $key => $param) {
                $mail_list = null;

                if (!empty($param['mail_list_uid'])) {
                    $mail_list = MailList::findByUid($param['mail_list_uid']);

                    // default mail list id
                    if (isset($param['is_default']) && $param['is_default'] == 'true') {
                        $this->default_mail_list_id = $mail_list->id;
                    }
                }

                if (!empty($param['segment_uids'])) {
                    foreach ($param['segment_uids'] as $segment_uid) {
                        $segment = Segment::findByUid($segment_uid);

                        $lists_segment = new CampaignsListsSegment();
                        $lists_segment->campaign_id = $this->id;
                        if (is_object($mail_list)) {
                            $lists_segment->mail_list_id = $mail_list->id;
                        }
                        $lists_segment->segment_id = $segment->id;
                        $this->listsSegments->push($lists_segment);
                    }
                } else {
                    $lists_segment = new CampaignsListsSegment();
                    $lists_segment->campaign_id = $this->id;
                    if (is_object($mail_list)) {
                        $lists_segment->mail_list_id = $mail_list->id;
                    }
                    $this->listsSegments->push($lists_segment);
                }
            }
        }
    }

    /**
     * Save Recipients.
     *
     * @var void
     */
    public function saveRecipients($params = [])
    {
        // Empty current data
        $this->listsSegments = collect([]);
        // Fill params
        $this->fillRecipients($params);

        $lists_segments_groups = $this->getListsSegmentsGroups();

        $data = [];
        foreach ($lists_segments_groups as $lists_segments_group) {
            if (!empty($lists_segments_group['segment_uids'])) {
                foreach ($lists_segments_group['segment_uids'] as $segment_uid) {
                    $segment = Segment::findByUid($segment_uid);
                    $data[] = [
                        'campaign_id' => $this->id,
                        'mail_list_id' => $lists_segments_group['list']->id,
                        'segment_id' => $segment->id,
                    ];
                }
            } else {
                $data[] = [
                    'campaign_id' => $this->id,
                    'mail_list_id' => $lists_segments_group['list']->id,
                    'segment_id' => null,
                ];
            }
        }

        // Empty old data
        $this->listsSegments()->delete();

        // Insert Data
        CampaignsListsSegment::insert($data);

        // Save campaign with default list id
        $campaign = Campaign::find($this->id);
        $campaign->default_mail_list_id = $this->default_mail_list_id;
        $campaign->save();
    }

    /**
     * Display Recipients.
     *
     * @var array
     */
    public function displayRecipients()
    {
        if (!is_object($this->defaultMailList)) {
            return '';
        }

        $lines = [];
        foreach ($this->getListsSegmentsGroups() as $lists_segments_group) {
            if (is_object($lists_segments_group['list'])) {
                $list_name = $lists_segments_group['list']->name;

                $segment_names = [];
                if (!empty($lists_segments_group['segment_uids'])) {
                    foreach ($lists_segments_group['segment_uids'] as $segment_uid) {
                        $segment = Segment::findByUid($segment_uid);
                        $segment_names[] = $segment->name;
                    }
                }

                if (empty($segment_names)) {
                    $lines[] = $list_name;
                } else {
                    $lines[] = implode(': ', [$list_name, implode(', ', $segment_names)]);
                }
            }
        }

        return implode(' | ', $lines);
    }

    /**
     * Pause campaign.
     *
     * @return bool
     */
    public function pause()
    {
        $this->cancelAndDeleteJobs();
        $this->setPaused();
    }

    private function setPaused()
    {
        // set campaign status
        $this->status = self::STATUS_PAUSED;
        $this->save();
        return $this;
    }

    /**
     * Check if campaign is paused.
     *
     * @return bool
     */
    public function isPaused()
    {
        return $this->status == self::STATUS_PAUSED;
    }

    /**
     * Update Campaign cached data.
     */
    public function updateCache($key = null)
    {
        // cache indexes
        $index = [
            // @note: SubscriberCount must come first as its value shall be used by the others
            'ActiveSubscriberCount' => function (&$campaign) {
                return $campaign->activeSubscribersCount(); // spepcial key that requires true update
            },
            'SubscriberCount' => function (&$campaign) {
                return $campaign->subscribersCount(false); // spepcial key that requires true update
            },
            'DeliveredRate' => function (&$campaign) {
                return $campaign->deliveredRate(true);
            },
            'DeliveredCount' => function (&$campaign) {
                return $campaign->deliveredCount();
            },
            'FailedDeliveredRate' => function (&$campaign) {
                return $campaign->failedRate(true);
            },
            'FailedDeliveredCount' => function (&$campaign) {
                return $campaign->failedCount();
            },
            'NotDeliveredRate' => function (&$campaign) {
                return $campaign->notDeliveredRate(true);
            },
            'NotDeliveredCount' => function (&$campaign) {
                return $campaign->notDeliveredCount();
            },
            'ClickedRate' => function (&$campaign) {
                return $campaign->clickedEmailsRate();
            },
            'UniqOpenRate' => function (&$campaign) {
                return $campaign->openUniqRate();
            },
            'UniqOpenCount' => function (&$campaign) {
                return $campaign->openUniqCount();
            },
            'NotOpenRate' => function (&$campaign) {
                return $campaign->notOpenRate(true);
            },
            'NotOpenCount' => function (&$campaign) {
                return $campaign->notOpenCount(true);
            },
        ];

        // retrieve cached data
        $cache = json_decode($this->cache, true);
        if (is_null($cache)) {
            $cache = [];
        }

        if (is_null($key)) {
            // update all cache
            foreach ($index as $key => $callback) {
                $cache[$key] = $callback($this);
                if ($key == 'SubscriberCount') {
                    // SubscriberCount cache must always be updated as its value will be used for the others
                    $this->cache = json_encode($cache);
                    $this->save();
                }
            }
        } else {
            // @deprecated, requires updating the SubscriberCount cache before updating any other one
            // update specific key
            $callback = $index[$key];
            $cache[$key] = $callback($this);
        }

        // write back to the DB
        $this->cache = json_encode($cache);
        $this->save();
    }

    /**
     * Retrieve Campaign cached data.
     *
     * @return mixed
     */
    public function readCache($key, $default = null)
    {
        $cache = json_decode($this->cache, true);
        if (is_null($cache)) {
            return $default;
        }
        if (array_key_exists($key, $cache)) {
            if (is_null($cache[$key])) {
                return $default;
            } else {
                return $cache[$key];
            }
        } else {
            return $default;
        }
    }

    /**
     * Count subscribers.
     *
     * @return int
     */
    public function subscribersCount($cache = false)
    {
        if ($cache) {
            return $this->readCache('SubscriberCount', 0);
        }
        // email_verifications join is required in case of segment condition (for example: 'deliverable' or 'risky')
        // return distinctCount($this->subscribers([], []), 'subscribers.email');
        // return distinctCount($this->subscribers([], ['email_verifications']), 'subscribers.email');
        return $this->subscribers([], ['email_verifications'])->count();
    }

    /**
     * Count subscribers.
     *
     * @return int
     */
    public function activeSubscribersCount()
    {
        // return distinctCount($this->subscribers([], ['email_verifications'])->where('subscribers.status', Subscriber::STATUS_SUBSCRIBED), 'subscribers.email');
        return $this->subscribers([], ['email_verifications'])->where('subscribers.status', Subscriber::STATUS_SUBSCRIBED)->count();
    }

    /**
     * Count unique open by hour.
     *
     * @return number
     */
    public function openUniqHours($start = null, $end = null)
    {
        $query = $this->openLogs()->select('open_logs.created_at');
        if (isset($start)) {
            $query = $query->where('open_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('open_logs.created_at', '<=', $end);
        }

        return $query->orderBy('open_logs.created_at', 'asc')->get()->groupBy(function ($date) {
            return \Acelle\Library\Tool::dateTime($date->created_at)->format('H'); // grouping by hours
        });
    }

    /**
     * Count click group by hour.
     *
     * @return number
     */
    public function clickHours($start = null, $end = null)
    {
        $query = $this->clickLogs()->select('click_logs.created_at', 'tracking_logs.subscriber_id');

        if (isset($start)) {
            $query = $query->where('click_logs.created_at', '>=', $start);
        }
        if (isset($end)) {
            $query = $query->where('click_logs.created_at', '<=', $end);
        }

        return $query->orderBy('click_logs.created_at', 'asc')->get()->groupBy(function ($date) {
            return \Acelle\Library\Tool::dateTime($date->created_at)->format('H'); // grouping by hours
        });
    }
    public function fileInfo($filePath)
    {
        $name = $filePath['filename'];
        $extension = $filePath['extension'];

        return $name.'.'.$extension;
    }

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

    /**
     * Generate SpamScore.
     */
    public function score()
    {
        // raw output
        $test = $this->execSpamc();

        // Get scores / thresholds
        preg_match('/\s*(?<score>[0-9\.\/]+)\s*/', $test, $score);

        if (!array_key_exists('score', $score)) {
            throw new \Exception('Cannot get SpamScore: '.$test);
        }

        $score = $score['score'];
        list($current, $threshold) = preg_split('/\//', $score);
        $passed = ($current <= $threshold) ? true : false;

        // get the details
        $json = [];

        $firstMatch = false;
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $test) as $line) {
            preg_match('/^\s*(?<score>[\-0-9\.]+)\s+(?<rule>[\w]+)\s+(?<desc>.*)/', $line, $result);

            if (array_key_exists('score', $result) && array_key_exists('rule', $result) && array_key_exists('desc', $result)) {
                $firstMatch = true;
                $json[] = [
                    'score' => $result['score'],
                    'rule' => $result['rule'],
                    'desc' => $result['desc'],
                    'status' => ($result['score'] > 0.0) ? 'failed' : (($result['score'] == 0.0) ? 'neutral' : 'passed'),
                ];
            } elseif ($firstMatch) {
                $lastRecord = end($json);
                $lastRecord['desc'] .= ' '.trim($line);
                // replace last record
                $json[sizeof($json) - 1] = $lastRecord;
            }
        }

        return [
            'result' => $passed,
            'score' => $score,
            'details' => $json,
        ];
    }

    /**
     * Generate SpamScore.
     */
    private function execSpamc()
    {
        // @todo: temporarily disable SPAMC
        return;

        $message = $this->getSampleMessage()->toString();

        // Execute SPAMC
        $desc = [
            0 => array('pipe', 'r'), // 0 is STDIN for process
            1 => array('pipe', 'w'), // 1 is STDOUT for process
            2 => array('pipe', 'w'),  // 2 is STDERR for process
        ];

        // command to invoke markup engine
        $cmd = Setting::get('spamassassin.command');

        if (is_null($cmd)) {
            $cmd = 'spamc -R'; // default value
        }

        // spawn the process
        $p = proc_open($cmd, $desc, $pipes);

        // send the wiki content as input to the markup engine
        // and then close the input pipe so the engine knows
        // not to expect more input and can start processing
        fwrite($pipes[0], $message);
        fclose($pipes[0]);

        // read the output from the engine
        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        // all done! Clean up
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($p);

        if (!empty($err) || empty($stdout)) {
            throw new \Exception('Error: cannot get SpamScore: '.$stderr);
        }

        return $stdout;
    }

    /**
     * Get sample message.
     */
    public function getSampleMessage()
    {
        // Build a valid message with a fake contact
        // build a temporary subscriber oject used to pass through the sending methods
        $subscriber = $this->createStdClassSubscriber(['email' => 'admin@outlook.com']);

        // Throw exception in case no server available
        $server = $this->pickSendingServer();

        // build the message from campaign information
        list($message, $msgId) = $this->prepareEmail($subscriber, $server);

        return $message;
    }

    /**
     * Copy new template.
     */
    public function generateTrackingLogCsv($logtype, $progressCallback = null)
    {
        $tmpTableName = 'log_'.$this->uid.'_'.md5(rand());
        $filePath = storage_path(join_paths('app', $tmpTableName.'.csv'));

        DB::statement('DROP TABLE IF EXISTS '.table($tmpTableName));
        
        if ($logtype == 'open_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name as campaign_name,
                s.email as subscriber_email,
                l.status as delivery_status,
                l.created_at as sent_at,
                o.created_at AS open_at,
                o.user_agent AS device,
                ip.ip_address,
                ip.country_code,
                ip.country_name,
                ip.region_code,
                ip.region_name,
                ip.city,
                ip.zipcode,
                ip.latitude,
                ip.longitude,
                ip.metro_code
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('open_logs').' o ON o.message_id = l.message_id
              LEFT JOIN '.table('ip_locations').' ip ON ip.ip_address = o.ip_address
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'open_at',
                'device',
                'ip_address',
                'country_code',
                'country_name',
                'region_code',
                'region_name',
                'city',
                'zipcode',
                'latitude',
                'longitude',
                'metro_code',
            ];
        } elseif ($logtype == 'click_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name as campaign_name,
                s.email as subscriber_email,
                l.status as delivery_status,
                l.created_at as sent_at,
                ck.created_at AS click_at,
                ck.url,
                ck.user_agent AS device,
                ip.ip_address,
                ip.country_code,
                ip.country_name,
                ip.region_code,
                ip.region_name,
                ip.city,
                ip.zipcode,
                ip.latitude,
                ip.longitude,
                ip.metro_code
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('click_logs').' ck ON ck.message_id = l.message_id
              LEFT JOIN '.table('ip_locations').' ip ON ip.ip_address = ck.ip_address
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'click_at',
                'url',
                'device',
                'ip_address',
                'country_code',
                'country_name',
                'region_code',
                'region_name',
                'city',
                'zipcode',
                'latitude',
                'longitude',
                'metro_code',
            ];
        } elseif ($logtype == 'unsubscribe_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name as campaign_name,
                s.email as subscriber_email,
                l.status as delivery_status,
                l.created_at as sent_at,
                u.created_at AS unsubscribe_at,
                u.user_agent AS device,
                ip.ip_address,
                ip.country_code,
                ip.country_name,
                ip.region_code,
                ip.region_name,
                ip.city,
                ip.zipcode,
                ip.latitude,
                ip.longitude,
                ip.metro_code
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('unsubscribe_logs').' u ON u.message_id = l.message_id
              LEFT JOIN '.table('ip_locations').' ip ON ip.ip_address = u.ip_address
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'unsubscribe_at',
                'device',
                'ip_address',
                'country_code',
                'country_name',
                'region_code',
                'region_name',
                'city',
                'zipcode',
                'latitude',
                'longitude',
                'metro_code',
            ];
        } elseif ($logtype == 'feedback_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name AS campaign_name,
                s.email AS subscriber_email,
                l.status AS delivery_status,
                l.created_at AS sent_at,
                f.created_at AS feedback_at,
                f.feedback_type AS feedback_type,
                f.raw_feedback_content AS feedback_content
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('feedback_logs').' f ON f.message_id = l.message_id
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'feedback_at',
                'feedback_type',
                'feedback_content',
            ];
        } elseif ($logtype == 'bounce_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name AS campaign_name,
                s.email AS subscriber_email,
                l.status AS delivery_status,
                l.created_at AS sent_at,
                b.created_at AS bounce_at,
                b.bounce_type AS bounce_type,
                b.raw AS bounce_content
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              JOIN '.table('bounce_logs').' b ON b.message_id = l.message_id
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
                'bounce_at',
                'bounce_type',
                'bounce_content',
            ];
        } elseif ($logtype == 'tracking_logs') {
            DB::statement('CREATE TEMPORARY TABLE '.table($tmpTableName).' AS SELECT
                c.name AS campaign_name,
                s.email AS subscriber_email,
                l.status AS delivery_status,
                l.created_at AS sent_at
              FROM '.table('tracking_logs').' l
              JOIN '.table('campaigns').' c ON l.campaign_id = c.id
              JOIN '.table('subscribers').' s ON l.subscriber_id = s.id
              WHERE c.id = '.$this->id. ' ORDER BY c.name, l.created_at');

            $headers = [
                'campaign_name',
                'subscriber_email',
                'delivery_status',
                'sent_at',
            ];
        } else {
            throw new \Exception('Unknown export type: '.$logtype);
        }

        $total = DB::table($tmpTableName)->count();
        $limit = 1000;
        $pages = ceil($total / $limit);

        // insert header
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->insertOne($headers);

        for ($i = 0; $i < $pages; $i += 1) {
            $items = DB::table($tmpTableName)->select('*')
                                             ->limit($limit)
                                             ->offset($i)
                                             ->get()
                                             ->map(function ($r) {
                                                 return (array) $r;
                                             })
                                             ->toArray();
            $csv->insertAll($items);

            // callback progress
            if (!is_null($progressCallback)) {
                $percentage = ($i + 1) / $pages;
                $progressCallback($percentage, $filePath);
            }
        }

        // callback progress
        if (!is_null($progressCallback)) {
            $progressCallback($percentage = 100, $filePath);
        }

        return $filePath;
    }

    /**
     * Get attachment path.
     */
    public function getAttachmentPath($path = null)
    {
        return $this->customer->getAttachmentsPath($path);
    }

    /**
     * Upload attachment.
     */
    public function uploadAttachment($file)
    {
        $filename = $file->getClientOriginalName();
        $filename = StringHelper::generateUniqueName($this->getAttachmentPath(), $filename);

        $path = $file->move(
            $this->getAttachmentPath(),
            $filename
        );

        return $this->getAttachmentPath($filename);
    }

    /**
     * Upload attachment.
     */
    public function getAttachments()
    {
        $atts = [];
        $path_campaign = $this->getAttachmentPath();

        if (!is_dir($path_campaign)) {
            return $atts;
        }

        $ffs = scandir($path_campaign);

        unset($ffs[array_search('.', $ffs, true)]);
        unset($ffs[array_search('..', $ffs, true)]);

        // prevent empty ordered elements
        if (count($ffs) < 1) {
            return $atts;
        }

        foreach ($ffs as $k => $ff) {
            $atts[] = $ff;
        }

        return $atts;
    }

    public function arrayRandFixed($array, $count)
    {
        $result = array_rand($array, $count);
        if (is_array($result)) {
            return $result;
        } else {
            return [$result];
        }
    }

    public function makeSampleData($params = [])
    {
        $this->cleanSampleData();

        $default = [
            'unconfirmed' => 0.08,
            'delivery_failed' => 0.1, // against all deivered emails
            'open' => 0.45, // against delivery_success
            'click' => 0.85, // against open
            'unsubscribe' => 0.2, // against open
            'bounce' => 0.05,
            'feedback' => 0.05
        ];

        $params = array_merge($default, $params);

        // Tracking log
        $this->getPendingSubscribers(null, function ($subscribers, $page, $total) use (&$i, $params) {
            if ($total == 0) {
                throw new \Exception('No subscribers for campaign, are you sure mail list is set?');
            }
            MailLog::info("Fetching page $page (count: {$subscribers->count()})");

            $i = 0;
            foreach ($subscribers as $subscriber) {
                $i += 1;
                // Pick up an available sending server
                // Throw exception in case no server available
                $server = $this->pickSendingServer($this);
                $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($this->from_email));

                $sent = [
                    'status' => 'sent',
                    'runtime_message_id' => $msgId
                ];

                $this->trackMessage($sent, $subscriber, $server, $msgId);

                // additional log
                MailLog::info("Sending to subscriber `{$subscriber->email}` ({$i}/{$total})");
            }
        });

        echo "All tracking log count: ". $this->trackingLogs()->count()."\n";
        // Failed tracking log
        $failureCount = round($params['delivery_failed'] * $this->trackingLogs()->count());
        $allLogs = $this->trackingLogs()->get()->map(function ($e) {
            return $e->id;
        })->toArray();
        $failedLogs = array_values(array_intersect_key($allLogs, array_flip($this->arrayRandFixed($allLogs, $failureCount))));
        echo "Failed log count: ".$this->trackingLogs()->whereIn('id', $failedLogs)->count()."\n";
        $this->trackingLogs()->whereIn('id', $failedLogs)->update([
            'status' => 'failed',
            'error' => 'RFC8343: mailbox does not exist',
        ]);

        // Open log
        $sentCount = $this->trackingLogs()->where('status', 'sent')->count();
        $openCount = round($params['open'] * $sentCount);
        $sentLogs = $this->trackingLogs()->where('status', 'sent')
                         ->select('tracking_logs.id')
                         ->get()->map(function ($e) {
                             return $e->id;
                         })->toArray();
        $openLogs = array_values(array_intersect_key($sentLogs, array_flip($this->arrayRandFixed($sentLogs, $openCount))));
        echo "Open log count: ".$openCount."\n";
        foreach ($this->trackingLogs()->whereIn('id', $openLogs)->get() as $r) {
            $log = new \Acelle\Model\OpenLog();
            $log->message_id = $r->message_id;
            $location = \Acelle\Model\IpLocation::add(StringHelper::getRandomUSIpAddresses());
            $log->ip_address = $location->ip_address;
            $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';
            $log->save();
        }

        // Open log
        $clickCount = round($params['click'] * $openCount);

        $openLogs = $this->trackingLogs()
                         ->join('open_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
                         ->select('tracking_logs.id')
                         ->get()->map(function ($e) {
                             return $e->id;
                         })->toArray();
        $clickLogs = array_values(array_intersect_key($openLogs, array_flip($this->arrayRandFixed($openLogs, $clickCount))));
        echo "Click log count: ".$this->trackingLogs()->whereIn('id', $clickLogs)->count()."\n";
        foreach ($this->trackingLogs()->whereIn('id', $clickLogs)->get() as $r) {
            $log = new \Acelle\Model\ClickLog();
            $log->message_id = $r->message_id;
            $location = \Acelle\Model\IpLocation::add(StringHelper::getRandomUSIpAddresses());
            $log->ip_address = $location->ip_address;
            $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';
            $log->url = 'https://app.clickfunnels.com/signupflow';
            $result = $log->save();
        }

        // Unsubscribe log
        $unsubscribeCount = round($params['unsubscribe'] * $openCount);
        $unsubscribeLogs = array_values(array_intersect_key($openLogs, array_flip($this->arrayRandFixed($openLogs, $unsubscribeCount))));
        echo "Unsubscribe log count: ".$unsubscribeCount."\n";
        foreach ($this->trackingLogs()->whereIn('id', $unsubscribeLogs)->get() as $r) {
            // Unsubscribe log
            $log = new \Acelle\Model\UnsubscribeLog();
            $log->message_id = $r->message_id;
            $location = \Acelle\Model\IpLocation::add(StringHelper::getRandomUSIpAddresses());
            $log->ip_address = $location->ip_address;
            $log->user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36';
            $log->save();
        }


        // Bounce log
        $bounceCount = round($params['bounce'] * $sentCount);
        $bounceLogs = array_values(array_intersect_key($sentLogs, array_flip($this->arrayRandFixed($sentLogs, $bounceCount))));
        echo "Bounce log count: ".$bounceCount."\n";
        foreach ($this->trackingLogs()->whereIn('id', $bounceLogs)->get() as $r) {
            // Unsubscribe log
            $bounceLog = new BounceLog();
            $bounceLog->runtime_message_id = $r->message_id;
            $bounceLog->message_id = $r->message_id;
            // SendGrid only notifies in case of HARD bounce
            $bounceLog->bounce_type = BounceLog::HARD;
            $bounceLog->raw = 'RFC308433: mailbox is no longer valid or blocked';
            $bounceLog->save();

            // add subscriber's email to blacklist
            $subscriber = $bounceLog->findSubscriberByRuntimeMessageId();
            $subscriber->sendToBlacklist($bounceLog->raw);
        }

        // Bounce log
        $feedbackCount = round($params['feedback'] * $sentCount);
        $feedbackLogs = array_values(array_intersect_key($sentLogs, array_flip($this->arrayRandFixed($sentLogs, $feedbackCount))));
        echo "Feedback log count: ".$feedbackCount."\n";
        foreach ($this->trackingLogs()->whereIn('id', $feedbackLogs)->get() as $r) {
            // Unsubscribe log
            $feedback = new FeedbackLog();
            $feedback->runtime_message_id = $r->message_id;
            $feedback->message_id = $r->message_id;
            $feedback->feedback_type = 'spam';
            $feedback->raw_feedback_content = 'RFC308433: mailbox is no longer valid or blocked';
            $feedback->save();

            // add subscriber's email to blacklist
            $subscriber = $feedback->findSubscriberByRuntimeMessageId();
            $subscriber->markAsSpamReported($feedback->raw);
        }

        // Adjust date/time
        $sample = [
            'subscriber_first_created' => '6 months ago',
            'subscriber_last_created' => '2 day ago',
            'first_open' => '24 hours ago',
            'last_open' => '2 minutes ago',
        ];

        foreach ($sample as $key => $value) {
            $sample[$key] = new Carbon($value);
        }

        DB::update(sprintf(
            'UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('subscribers'),
            $sample['subscriber_first_created'],
            $sample['subscriber_last_created'],
            $sample['subscriber_first_created']
        ));

        DB::update(sprintf(
            'UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('open_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        DB::update(sprintf(
            'UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('click_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        DB::update(sprintf(
            'UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('bounce_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        DB::update(sprintf(
            'UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('feedback_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        DB::update(sprintf(
            'UPDATE %s SET created_at = TIMESTAMPADD(SECOND, FLOOR(RAND() * TIMESTAMPDIFF(SECOND, \'%s\', \'%s\')), \'%s\')',
            table('unsubscribe_logs'),
            $sample['first_open'],
            $sample['last_open'],
            $sample['first_open']
        ));

        // Finalize
        $this->updateCache();
    }

    public function cleanSampleData()
    {
        $this->defaultMailList->subscribers()->update(['status' => 'subscribed']);
        Blacklist::query()->delete();
        $this->trackingLogs()->delete();
    }

    public function appendToBody($html, $content)
    {
        $regexp = '/<\/body\s*>/i';
        if (preg_match($regexp, $html) == true) {
            return preg_replace($regexp, $content.'</body>', $html);
        } else {
            return $html.$content;
        }
    }

    public function wooTransform($body)
    {
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

        return $body;
    }

    public function getDeliveryReport()
    {
        /****
         * Important: there are 4 possible values of `delivery_stats`
         *     + sent
         *     + failed
         *     + new
         *     + skipped
         *
         * Assumption
         *     + subscribers 1:1 email_verification
         *     + [campaign, subscriber] 1:1 tracking_logs
         * */
        $query = $this->subscribers()->leftJoinSub(
            // Why joinSub? Notice that this->subscribers() does not have campaign_id constraint!
            // while this->trackingLogs() does have
            $this->trackingLogs(),
            'tracking_logs',
            function ($join) {
                $join->on('tracking_logs.subscriber_id', 'subscribers.id');
            }
        )->leftJoin('bounce_logs', 'tracking_logs.message_id', 'bounce_logs.message_id')
         ->leftJoin('feedback_logs', 'tracking_logs.message_id', 'feedback_logs.message_id');

        $query->select(DB::raw(strtr("
            CASE WHEN `%bounce_logs`.`id` IS NOT NULL THEN '%bounced'
            ELSE
                CASE WHEN `%feedback_logs`.`id` IS NOT NULL THEN '%feedback'
                ELSE
                    CASE `%tracking_logs`.`status`
                    WHEN '%sent'            THEN '%sent'
                    WHEN '%failed'          THEN '%failed'
                    ELSE
                        CASE `%subscribers`.`status`
                        WHEN '%subscribed' THEN
                            CASE COALESCE(`%email_verifications`.`result`, '-1')
                            WHEN '-1'           THEN '%new'
                            WHEN '%deliverable' THEN '%new'
                            ELSE                     '%skipped'
                            END
                        ELSE
                            '%skipped'
                        END
                    END
                END
            END
            AS delivery_status
        ", [
            '%sent' => self::DELIVERY_STATUS_SENT,
            '%failed' => self::DELIVERY_STATUS_FAILED,
            '%new' => self::DELIVERY_STATUS_NEW,
            '%skipped' => self::DELIVERY_STATUS_SKIPPED,
            '%bounced' => self::DELIVERY_STATUS_BOUNCED,
            '%feedback' => self::DELIVERY_STATUS_FEEDBACK,
            '%subscribed' => Subscriber::STATUS_SUBSCRIBED,
            '%deliverable' => EmailVerification::RESULT_DELIVERABLE,
            '%tracking_logs' => table('tracking_logs'),
            '%subscribers' => table('subscribers'),
            '%email_verifications' => table('email_verifications'),
            '%bounce_logs' => table('bounce_logs'),
            '%feedback_logs' => table('feedback_logs')
        ])));

        return $query;
    }

    public function getDeliveryReportSummary()
    {
        $query = DB::query()->fromSub($this->getDeliveryReport(), 'campaign_subscribers')->groupBy('delivery_status')->select(DB::raw('delivery_status, COUNT(1)'));

        return $query;
    }

    /**
     * Start the campaign. called by daemon job
     */
    public function prepare($callback)
    {
        // Available sending servers
        $sendingServersPool = $this->defaultMailList->getSendingServers();

        // Set up sending servers' webhooks before launching
        foreach($sendingServersPool as $serverId => $fitness) {
            $server = SendingServer::find($serverId)->mapType();

            if ($this->useSendingServerFromEmailAddress()) {
                $fromEmailAddress = $server->default_from_email;
            } else {
                $fromEmailAddress = $this->from_email;
            }

            $server->setupBeforeSend($fromEmailAddress);
        }

        // clean up the tracker to prevent the log file from growing very big
        $this->customer->cleanupQuotaTracker();

        // Reset max_execution_time so that command can run for a long time without being terminated
        self::resetMaxExecutionTime();

        // Query subscribers
        $query = $this->subscribersToSend();

        // Iterate through batches of subscribers, 100 each
        cursorIterate(
            $query,
            $orderBy = 'subscribers.id',
            $perPage = 100,
            function ($subscribers) use (&$i, &$sendingServersPool, $callback) {
                foreach ($subscribers as $subscriber) {
                    $serverId = RouletteWheel::take($sendingServersPool);
                    $server = SendingServer::find($serverId)->mapType();

                    $callback($this, $subscriber, $server);
                }
            }
        );
    }

    public function subscribersToSend()
    {
        // Retrieve subscribers to send!
        $query = $this->subscribers([], ['email_verifications'])
                ->whereRaw(sprintf(table('subscribers').'.email NOT IN (SELECT email FROM %s t JOIN %s s ON t.subscriber_id = s.id WHERE t.campaign_id = %s)', table('tracking_logs'), table('subscribers'), $this->id))
                ->subscribed();

        return $query;
    }

    /**
     * Transform Tags
     * Transform tags to actual values before sending.
     */
    public function tagMessage($message, $subscriber, $msgId, $server = null)
    {

        // DEPRECATED
        if (!is_null($server) && $server->isElasticEmailServer()) {
            $message = $server->addUnsubscribeUrl($message);
        }

        $tags = array(
            'CAMPAIGN_NAME' => $this->name,
            'CAMPAIGN_UID' => $this->uid,
            'CAMPAIGN_SUBJECT' => $this->subject,
            'CAMPAIGN_FROM_EMAIL' => $this->from_email,
            'CAMPAIGN_FROM_NAME' => $this->from_name,
            'CAMPAIGN_REPLY_TO' => $this->reply_to,
            'SUBSCRIBER_UID' => $subscriber->uid,
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'CONTACT_NAME' => $this->defaultMailList->contact->company,
            'CONTACT_COUNTRY' => 'test',
            'CONTACT_STATE' => $this->defaultMailList->contact->state,
            'CONTACT_CITY' => $this->defaultMailList->contact->city,
            'CONTACT_ADDRESS_1' => $this->defaultMailList->contact->address_1,
            'CONTACT_ADDRESS_2' => $this->defaultMailList->contact->address_2,
            'CONTACT_PHONE' => $this->defaultMailList->contact->phone,
            'CONTACT_URL' => $this->defaultMailList->contact->url,
            'CONTACT_EMAIL' => $this->defaultMailList->contact->email,
            'LIST_NAME' => $this->defaultMailList->name,
            'LIST_SUBJECT' => $this->defaultMailList->default_subject,
            'LIST_FROM_NAME' => $this->defaultMailList->from_name,
            'LIST_FROM_EMAIL' => $this->defaultMailList->from_email,
        );
        
        # Subscriber specific
        if (!$this->isStdClassSubscriber($subscriber)) {
            $tags['UPDATE_PROFILE_URL'] = $this->generateUpdateProfileUrl($subscriber);
            $tags['UNSUBSCRIBE_URL'] = $this->generateUnsubscribeUrl($msgId, $subscriber);
            $tags['WEB_VIEW_URL'] = $this->generateWebViewerUrl($msgId);

            # Subscriber custom fields
            foreach ($this->defaultMailList->fields as $field) {
                $tags['SUBSCRIBER_'.$field->tag] = $subscriber->getValueByField($field);
                $tags[$field->tag] = $subscriber->getValueByField($field);
            }

            // Special / shortcut fields
            $tags['NAME'] = 'test';
            $tags['FULL_NAME'] = 'test';
        } else {
            $sampleLink = route('campaign_message', [ 'message' => StringHelper::base64UrlEncode('This is a test link of a test campaign') ]);
            $tags['UNSUBSCRIBE_URL'] = $sampleLink;
            $tags['UPDATE_PROFILE_URL'] = $sampleLink;
            $tags['WEB_VIEW_URL'] = $sampleLink;

            # Subscriber custom fields, including email
            $sample = '%PERSONALIZED-DATA%';
            foreach ($this->defaultMailList->fields as $field) {
                $tags['SUBSCRIBER_'.$field->tag] = $sample;
                $tags[$field->tag] = $sample;
            }

            // Special / shortcut fields
            $tags['NAME'] = 'test';
            $tags['FULL_NAME'] = 'test';

            // Only email is "reserved", overwrite previous $sample
            $tags['SUBSCRIBER_EMAIL'] = $subscriber->email;
        }

        // Actually transform the message
        foreach ($tags as $tag => $value) {
            $message = str_replace('{'.$tag.'}', $value, $message);
        }

        return $message;
    }

    /**
     * Subscribers.
     *
     * @return collect
     */
    public function subscribers($params = [], $extra = ['email_verifications'])
    {
        if ($this->listsSegments->isEmpty()) {
            // this is a trick for returning an empty builder
            return Subscriber::limit(0);
        }

        $query = Subscriber::select('subscribers.*');
        // Tell MySQL to use the right index
        // $query->from(\DB::raw(\DB::getTablePrefix() . 'subscribers FORCE INDEX (subscribers_mail_list_id_email_index)'));
        // Email verification join
        if (in_array('email_verifications', $extra)) {
            $query = $query->leftJoin('email_verifications', 'email_verifications.subscriber_id', '=', 'subscribers.id');
        }
        // Get subscriber from mailist and segment
        $conditions = [];
        foreach ($this->listsSegments as $lists_segment) {
            if (!empty($lists_segment->segment_id)) {
                // Segment
                $conds = $lists_segment->segment->getSubscribersConditions();
                if (!empty($conds['joins'])) {
                    foreach ($conds['joins'] as $joining) {
                        $query = $query->leftJoin($joining['table'], function ($join) use ($joining) {
                            $join->on($joining['ons'][0][0], '=', $joining['ons'][0][1]);
                            if (isset($joining['ons'][1])) {
                                $join->on($joining['ons'][1][0], '=', $joining['ons'][1][1]);
                            }
                        });
                    }
                }
                // WHERE...
                if (!empty($conds['conditions'])) {
                    // IMPORTANT: segment condition does not include list_id constraints
                    $conds['conditions'] = '('.table('subscribers.mail_list_id').' = '.$lists_segment->mail_list_id.' AND ('.$conds['conditions'].'))';

                    $conditions[] = $conds['conditions'];
                }
            } else {
                // Entire list
                $conditions[] = '('.table('subscribers.mail_list_id').' = '.$lists_segment->mail_list_id.')';
            }
        }

        if (!empty($conditions)) {
            $query = $query->whereRaw('('.implode(' OR ', $conditions).')');
        }

        // Filters
        $filters = isset($params['filters']) ? $params['filters'] : null;
        if ((isset($filters) && (isset($filters['open']) || isset($filters['click']) || isset($filters['tracking_status'])))
        ) {
            $query = $query->leftJoin('tracking_logs', 'tracking_logs.subscriber_id', '=', 'subscribers.id');
            $query = $query->whereNotNull('tracking_logs.id');
            $query = $query->where('tracking_logs.campaign_id', '=', $this->id);
        }

        if (isset($filters)) {
            if (isset($filters['open'])) {
                $equal = ($filters['open'] == 'opened') ? 'whereNotNull' : 'whereNull';
                $query = $query->leftJoin('open_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
                    ->$equal('open_logs.id');
            }
            if (isset($filters['click'])) {
                $equal = ($filters['click'] == 'clicked') ? 'whereNotNull' : 'whereNull';
                $query = $query->leftJoin('click_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
                    ->$equal('click_logs.id');
            }
            if (isset($filters['tracking_status'])) {
                $val = ($filters['tracking_status'] == 'not_sent') ? null : $filters['tracking_status'];
                $query = $query->where('tracking_logs.status', '=', $val);
            }
        }
        // keyword
        if (isset($params['keyword']) && !empty(trim($params['keyword']))) {
            foreach (explode(' ', trim($params['keyword'])) as $keyword) {
                $query = $query->leftJoin('subscriber_fields', 'subscribers.id', '=', 'subscriber_fields.subscriber_id');
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('subscribers.email', 'like', '%'.$keyword.'%')
                        ->orWhere('subscriber_fields.value', 'like', '%'.$keyword.'%');
                });
            }
        }

        return $query;
    }

    /**
     * Pick up a delivery server for the campaign.
     *
     * @return mixed
     */
    public function pickSendingServer()
    {
        return $this->defaultMailList->pickSendingServer();
    }

    public function logger()
    {
        if (!is_null($this->logger)) {
            return $this->logger;
        }

        $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");

        $logfile = storage_path(join_paths('logs', php_sapi_name(), '/campaign-'.$this->uid.'.log'));
        $stream = new RotatingFileHandler($logfile, 0, Logger::DEBUG);
        $stream->setFormatter($formatter);

        $pid = getmypid();
        $logger = new Logger($pid);
        $logger->pushHandler($stream);
        $this->logger = $logger;

        return $this->logger;
    }

    public function deleteAndCleanup()
    {
        if ($this->template) {
            $this->template->deleteAndCleanup();
        }

        $this->cancelAndDeleteJobs();

        $this->delete();
    }

    public function isError()
    {
        return $this->status == self::STATUS_ERROR;
    }

    public function cancelAndDeleteJobs($jobType = null)
    {
        $query = $this->jobMonitors();

        if (!is_null($jobType)) {
            $query = $query->byJobType($jobType);
        }

        foreach ($query->get() as $job) {
            $job->cancel();
        }
    }

    public function schedule()
    {
        // Delete previous ScheduleCampaign jobs
        $this->cancelAndDeleteJobs(ScheduleCampaign::class);

        // Schedule Job initialize
        $scheduler = (new ScheduleCampaign($this))->delay($this->run_at);

        // Dispatch using the method provided by TrackJobs
        // to also generate job-monitor record
        $this->dispatchWithMonitor($scheduler);

        // After this job is dispatched successfully, set status to "queuing"
        // Notice the different between the two statuses
        // + Queuing: waiting until campaign is ready to run
        // + Queued: ready to run
        $this->setQueuing();
    }

    public function resume()
    {
        $this->schedule();
    }

    // Should be called by ScheduleCampaign
    public function launch()
    {
        // Pause any previous batch no matter what status it is
        // Notice that batches without a job_monitor will not be retrieved
        $jobs = $this->jobMonitors()->byJobType(LoadCampaign::class)->get();
        foreach ($jobs as $job) {
            $job->cancelWithoutDeleteBatch();
        }

        // Campaign loader job
        $campaignLoader = new LoadCampaign($this);

        // Dispatch it with a batch monitor
        $this->dispatchWithBatchMonitor(
            $campaignLoader,
            function ($batch) {
                // THEN callbak
                //
                // Important:
                // Notice that if user manually cancels a batch, it still reaches trigger "then" callback
                // Only when an exception is thrown, no "then" trigger
                // So, if the campaign is paused by user, do not update its status to done once its batch finishes, keep it as paused
                // IMPORTANT: refresh() is required!
                if (!$this->refresh()->isPaused()) {
                    $this->setDone();
                } else {
                    // previously paused, no need to update its status
                }
            },
            function (Batch $batch, Throwable $e) {
                // CATCH callback
                $errorMsg = "Error sending messages. ".$e->getMessage()."\n".$e->getTraceAsString();
                $this->setError($errorMsg);
            },
            function () {
                // FINALLY callback
                $this->updateCache();
            }
        );

        // SET QUEUED
        $this->setQueued();

        /**** MORE NOTES ****/
        //
        // Important: in case one of the batch's jobs hits an error
        // the batch is automatically set to cancelled and, therefore, all remaining jobs will just finish (return)
        // resulting in the "finally" event to be triggered
        // So, do not update satus here, otherwise it will overwrite any status logged by "catch" event
        // Notice that: if a batch fails (automatically canceled due to one failed job)
        // then, after all jobs finishes (return), [failed job] = [pending job] = 1
        // +------------+--------------+-------------+---------------------------------------------------------------------------------+-------------+
        // | total_jobs | pending_jobs | failed_jobs | failed_job_ids                                                                  | finished_at |
        // +------------+--------------+-------------+---------------------------------------------------------------------------------+-------------+
        // |          7 |            0 |           0 | []                                                                              |  1624848887 | success
        // |          7 |            1 |           1 | ["302130fd-ba78-4a37-8a3b-2304cc3f3455"]                                        |  1624849156 | failed
        // |          7 |            2 |           2 | ["6a17f9bf-96d4-48e5-86a0-73e7bac07e74","7e1b3b3d-a5f4-45b4-be1e-ba5f1cc2e3f3"] |  1624849222 | (*)
        // |          7 |            3 |           2 | ["6a17f9bf-96d4-48e5-86a0-73e7bac07e74","7e1b3b3d-a5f4-45b4-be1e-ba5f1cc2e3f3"] |  1624849222 | (**)
        // |          7 |            2 |           0 | []                                                                              |        NULL | (***)
        // +------------+--------------+-------------+---------------------------------------------------------------------------------+-------------+
        //
        // (*) There is no batch cancelation check in every job
        // as a result, remaining jobs still execute even after the batch is automatically cancelled (due to one failed job)
        // resulting in 2 (or more) failed / pending jobs
        //
        // (**) 2 jobs already failed, there is 1 remaining job to finish (so 3 pending jobs)
        // That is, pending_jobs = failed jobs + remaining jobs
        //
        // (***) If certain jobs are deleted from queue or terminated during action (without failing or finishing)
        // Then the campaign batch does not reach "then" status
        // Then proceed with pause and send again
    }

    public function extractErrorMessage()
    {
        return explode("\n", $this->last_error)[0];
    }
}
