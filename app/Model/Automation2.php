<?php

/**
 * Automation class.
 *
 * Model for automations
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

use DB;
use Acelle\Library\Automation\Action;
use Acelle\Library\Automation\Trigger;
use Acelle\Library\Automation\Send;
use Acelle\Library\Automation\Wait;
use Acelle\Library\Automation\Evaluate;
use Acelle\Library\Automation\Operate;
use Carbon\Carbon;
use Acelle\Library\Log as MailLog;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;
use Acelle\Library\Lockable;
use Acelle\Model\Setting;
use Acelle\Model\Customer;
use Acelle\Model\MailList;
use Acelle\Model\Email;
use Acelle\Model\AutomationList;
use Acelle\Model\OpenLog;
use Acelle\Model\ClickLog;

use Acelle\Model\BounceLog;
use Acelle\Model\TrackingLog;
use DateTime;
use DateTimeZone;
use Exception;
use Throwable;

class Automation2 extends Model
{
    // Automation status
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    const TRIGGER_TYPE_WELCOME_NEW_SUBSCRIBER = 'welcome-new-subscriber';
    const TRIGGER_TYPE_SAY_GOODBYE_TO_SUBSCRIBER = 'say-goodbye-subscriber';
    const TRIGGER_TYPE_SAY_HAPPY_BIRTHDAY = 'say-happy-birthday';
    const TRIGGER_SUBSCRIPTION_ANNIVERSARY_DATE = 'subscriber-added-date'; // in progress
    const TRIGGER_PARTICULAR_DATE = 'specific-date'; // in progress
    const TRIGGER_API = 'api-3-0';
    const TRIGGER_WEEKLY_RECURRING = 'weekly-recurring';
    const TRIGGER_MONTHLY_RECURRING = 'monthly-recurring';

    /**
     * Items per page.
     *
     * @var array
     */
    const ITEMS_PER_PAGE = 25;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name','customer_id', 'mail_list_id', 'status', 'data','smtp_server_id','main_id'
    ];

    /**
     * Association with mailList through mail_list_id column.
     */
    public function mailList()
    {
        return $this->belongsTo('Acelle\Model\MailList');
    }


    /**
     * Get all of the links for the automation.
     */
    public function emailLinks()
    {
        return $this->hasManyThrough('Acelle\Model\EmailLink', 'Acelle\Model\Email');
    }

    /**
     * Get all of the emails for the automation.
     */
    public function emails()
    {
        return $this->hasMany('Acelle\Model\Email');
    }

    /**
     * Association.
     */
    public function autoTriggers()
    {
        return $this->hasMany('Acelle\Model\AutoTrigger');
    }

    public function subscribersNotTriggeredThisYear()
    {
        $thisYear = $this->customer->getCurrentTime()->format('Y');
        $thisId = $this->id;
        return $this->subscribers()->leftJoin('auto_triggers', function ($join) use ($thisId) {
            $join->on('auto_triggers.subscriber_id', 'subscribers.id');
            $join->where('auto_triggers.automation2_id', $thisId);
        })
                                   ->where(function ($query) use ($thisYear) {
                                       $query->whereNull('auto_triggers.id')
                                             ->orWhereRaw(sprintf('year(%s) < %s', table('auto_triggers.created_at'), $thisYear));
                                   });
    }

    /**
     * Association.
     */
    public function segment()
    {
        return $this->belongsTo('Acelle\Model\Segment');
    }

    /**
     * Association: triggers that have not finished
     */
    public function pendingAutoTriggers()
    {
        $leaves = $this->getLeafActions();
        $condition = '('.implode(' AND ', array_map(function ($e) {
            return "executed_index NOT LIKE '%{$e}'";
        }, $leaves)).')';
        $query = $this->autoTriggers()->whereRaw($condition);
        return $query;
    }

    /**
     * Association.
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    /**
     * Association.
     */
    public function timelines()
    {
        return $this->hasMany('Acelle\Model\Timeline')->orderBy('created_at', 'DESC');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating automation.
        static::creating(function ($item) {
            $item->uid = uniqid();
        });

        // Create uid when creating automation.
        static::created(function ($item) {
            $item->updateCache();
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
    public function rules()
    {
        return [
            'name' => 'required',
            'mail_list_uid' => 'required',
        ];
    }

    public function getElementById($id)
    {
        $data = $this->getElements()[$id];

        // search by id

        return $element;
    }

    /**
     * Search items.
     *
     * @return collect
     */
    public static function scopeSearch($query, $keyword)
    {
        // Keyword
        if (!empty(trim($keyword))) {
            $query = $query->where('name', 'like', '%'.trim($keyword).'%');
        }

        return $query;
    }

    /**
     * enable automation.
     */
    public function enable()
    {
        // @IMPORTANT: need more validation like this
        if (empty($this->getTriggerType())) {
            throw new \Exception("Automation trigger not set up");
        }

        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * disable automation.
     */
    public function disable()
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
    }

    public function saveDataAndResetTriggers($data)
    {
        DB::transaction(function () use ($data) {
            $this->resetListRelatedData();
            $this->autoTriggers()->delete();
            $this->saveData($data);
        });
    }

    /**
     * disable automation.
     */
    public function saveData($data)
    {
        
        $this->data = $this->validateData($data);
        $this->save();
    }

    public function validateData($data)
    {
        // Check Element Action => if email UID is no longer valid => remove Action
        $entries = json_decode($data, true);
        $newData = [];
      
        foreach ($entries as $entry) {
            $action = $this->getAction($entry);
             
            // @TODO should be transparent in Action element
            if ($action->getType() == 'ElementAction') {
                if ($action->getOption('init') == "true") { // @IMPORTANT VERY IMPORTANT! Use "true", it is because even "false" == true!!!!
                    
                    if (!$this->emails()->where('uid', $action->getOption('email_uid'))->exists()) {
                      
                        $action->fixInvalidEmailUid();
                    }
                }
            }
           
            $newData[] = $action->toJson();
        }
        return json_encode($newData);
    }

    /**
     * disable automation.
     */
    public function getData()
    {
        return isset($this->data) ? preg_replace('/"([^"]+)"\s*:\s*/', '$1:', $this->data) : '[]';
    }

    /**
     * get all tree elements.
     */
    public function getElements($hash = false) # true => return hash, false (default) => return stdObject
    {
        // $data = AutomationList::where('automation_id',$this->id)->where('is_executed',0)->orderByRaw("UNIX_TIMESTAMP(datetime_data) ASC")->first();
        // MailLog::info('jsonData'.json_encode($data));
        //return isset($data->data) && !empty($data->data) ? json_decode($data->data, $hash) : [];
        return isset($this->data) && !empty($this->data) ? json_decode($this->data, $hash) : [];
    }

    /**
     * get trigger.
     */
    public function getTrigger()
    {
        $elements = $this->getElements();

        return empty($elements) ? new AutomationElement(null) : new AutomationElement($elements[0]);
    }

    /**
     * get element by id.
     */
    public function getElement($id = null)
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            if ($element->id == $id) {
                return new AutomationElement($element);
            }
        }

        return new AutomationElement(null);
    }

    /**
     * Get started time.
     */
    public function getStartedTime()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get delay options.
     */
    public function getDelayOptions($moreOptions=[])
    {
        return array_merge($moreOptions, [
            ['text' => trans_choice('messages.automation.delay.minute', 1), 'value' => '1 minute'],
            ['text' => trans_choice('messages.automation.delay.minute', 30), 'value' => '30 minutes'],
            ['text' => trans_choice('messages.automation.delay.hour', 1), 'value' => '1 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 2), 'value' => '2 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 4), 'value' => '4 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 8), 'value' => '8 hours'],
            ['text' => trans_choice('messages.automation.delay.hour', 12), 'value' => '12 hours'],

            ['text' => trans_choice('messages.automation.delay.day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.automation.delay.day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.automation.delay.day', 3), 'value' => '3 days'],
            ['text' => trans_choice('messages.automation.delay.day', 4), 'value' => '4 days'],
            ['text' => trans_choice('messages.automation.delay.day', 5), 'value' => '5 days'],
            ['text' => trans_choice('messages.automation.delay.day', 6), 'value' => '6 days'],
            ['text' => trans_choice('messages.automation.delay.week', 1), 'value' => '1 week'],
            ['text' => trans_choice('messages.automation.delay.week', 2), 'value' => '2 weeks'],
            ['text' => trans_choice('messages.automation.delay.month', 1), 'value' => '1 month'],
            ['text' => trans_choice('messages.automation.delay.month', 2), 'value' => '2 months'],

            ['text' => trans('messages.automation.delay.custom'), 'value' => 'custom'],
        ]);
    }

    /**
     * Get delay or before options.
     */
    public function getDelayBeforeOptions()
    {
        return [
            ['text' => trans_choice('messages.automation.delay.0_day', 0), 'value' => '0 day'],
            ['text' => trans_choice('messages.automation.delay.day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.automation.delay.day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.automation.delay.day', 3), 'value' => '3 days'],
            ['text' => trans_choice('messages.automation.delay.day', 4), 'value' => '4 days'],
            ['text' => trans_choice('messages.automation.delay.day', 5), 'value' => '5 days'],
            ['text' => trans_choice('messages.automation.delay.day', 6), 'value' => '6 days'],
            ['text' => trans_choice('messages.automation.delay.week', 1), 'value' => '1 week'],
            ['text' => trans_choice('messages.automation.delay.week', 2), 'value' => '2 weeks'],
            ['text' => trans_choice('messages.automation.delay.month', 1), 'value' => '1 month'],
            ['text' => trans_choice('messages.automation.delay.month', 2), 'value' => '2 months'],
        ];
    }

    /**
     * Get email links options.
     */
    public function getEmailLinkOptions()
    {
        $data = [];

        foreach ($this->emailLinks as $link) {
            $data[] = ['text' => $link->link, 'value' => $link->uid];
        }

        return $data;
    }

    /**
     * Get emails options.
     */
    public function getEmailOptions()
    {
        $data = [];

        foreach ($this->emails as $email) {
            $data[] = ['text' => $email->subject, 'value' => $email->uid];
        }

        return $data;
    }

    /**
     * Initiate a trigger with a given subscriber.
     */
    // public function initTrigger($subscriber, $force = false)
    // {
    //     if (!$subscriber->isSubscribed() && $force == false) {
    //         $this->logger()->warning(sprintf('Cannot trigger contact %s, current status is: %s', $subscriber->email, $subscriber->status));
    //         return;
    //     }

    //     $data = json_decode($this->data);
    //     var_dump($data);
    //     //exit;
    //     $subsData = array();
    //     foreach ($data as $key => $value) {
    //         if($key > 0){
    //             if(in_array($subscriber->email, $value->options->subscribers)){
    //                 $subsData[$value->options->email_uid][] = $subscriber->email;
    //                 $email_uid = $value->options->email_uid;
    //             }
    //         }
            
    //     }


    //     $trigger = null;
    //     DB::transaction(function () use (&$trigger, $subscriber,$subsData) {
    //         $trigger = $this->autoTriggers()->create();
    //         $trigger->subscriber()->associate($subscriber); // assign to subscriber_id field
    //         $trigger->updateWorkflow();
    //         foreach ($subsData as $key => $values) {
    //            if(in_array($subscriber->email, $values)){
    //                 $trigger->email_uid = $key;
    //             }
    //         }
            
    //         //$trigger->email_uid = ;
    //         $trigger->save();
    //         $this->logger()->info(sprintf('[%s] NEW TRIGGER for %s', $this->getTriggerType(), $subscriber->email));
    //     });

    //     return $trigger;
    // }
   public function initTrigger($subscriber, $force = false)
    {
        if (!$subscriber->isSubscribed() && $force == false) {
            $this->logger()->warning(sprintf('Cannot trigger contact %s, current status is: %s', $subscriber->email, $subscriber->status));
            return;
        }

        $data = json_decode($this->data,true);
        // $datas = AutomationList::where('automation_id',$this->id)->where('is_executed',0)->orderByRaw("UNIX_TIMESTAMP(datetime_data) ASC")->first();
        // $data = json_decode($datas->data);
        MailLog::info('TriggerData'.json_encode($data));
        MailLog::info('SubsData  :'.json_encode($subscriber));
        
        $subsData = array();
        foreach ($data as $key => $value) {
            if($key > 0){
                //var_dump($value['options']['subscribers']);
                //MailLog::info('SubsData  :'.json_encode());
                //$subscribersData[] = $value->options->subscribers;
                //MailLog::info('SubsData  :'.json_encode($subscribersData));
                if(in_array($subscriber->email, $value['options']['subscribers'] )){
                    $subsData[$value['options']['email_uid']][] = $subscriber->email;
                    $email_uid = $value['options']['email_uid'];
                }
            }
            
        }
        
                //exit;
        MailLog::info('SubsData  :'.json_encode($subsData));
        $trigger = null;
        DB::transaction(function () use (&$trigger, $subscriber,$subsData) {
            $trigger = $this->autoTriggers()->create();
            $trigger->subscriber()->associate($subscriber); // assign to subscriber_id field
            $trigger->updateWorkflow();
            foreach ($subsData as $key => $values) {
               if(in_array($subscriber->email, $values)){
                    $trigger->email_uid = $key;
                }
            }
            
            //$trigger->email_uid = ;
            $trigger->save();
            $this->logger()->info(sprintf('[%s] NEW TRIGGER for %s', $this->getTriggerType(), $subscriber->email));
        });

        return $trigger;
    }

    /**
     * Scan for triggers updates and new triggers.
     */
    public static function run()
    {
        //file_put_contents(storage_path('app/log.txt'),'Automation Run',FILE_APPEND);
        $customers = Customer::all();

        //file_put_contents(storage_path('app/log.txt'),json_encode($customers).PHP_EOL,FILE_APPEND);
        foreach ($customers as $customer) {
            // Only one automation process to run at one given time
            //file_put_contents(storage_path('app/log.txt'),$customer->user->displayName().PHP_EOL,FILE_APPEND);
            $automations = $customer->activeAutomation2s;
            //dd($automations);
            foreach ($automations as $automation) {
                //MailLog::info('automations  :'.json_encode($automations));
                //file_put_contents(storage_path('app/log.txt'),$automation->name.PHP_EOL,FILE_APPEND);
                $automationLockFile = $customer->getLockPath('automation-lock-'.$automation->uid);
                $lock = new Lockable($automationLockFile);
                $timeout = 5; // seconds
                $timeoutCallback = function() use ($automation) {
                    // pass this ot the getExclusiveLock method 
                    // to have it silently quit, without throwing an exception
                    $automation->logger()->info(sprintf('LOCK timeout, another process is currently handling automation "%s"', $automation->name));
                    return;
                };
                //file_put_contents(storage_path('app/log.txt'),'Automation Mid',FILE_APPEND);
                $lock->getExclusiveLock(function ($f) use ($customer, $automation) {
                    if ($customer->isSubscriptionActive()) {
                        $automation->logger()->info(sprintf('Checking automation "%s"', $automation->name));
                        $automation->check();
                    } else {
                        $automation->logger()->warning(sprintf('Automation "%s" skipped, user "%s" not on active subscription', $automation->name, $customer->user->displayName()));
                    }
                }, $timeout, $timeoutCallback);
            }
        }
        //file_put_contents(storage_path('app/log.txt'),'Automation End',FILE_APPEND);
    }

    // Automation::check() ==> AutoTrigger::check() ==> Action::execute()
    public function check()
    {
        try {
            $this->checkForNewTriggers();
            $this->checkForExistingTriggersUpdate();
            $this->updateCache();
            $this->setLastError(null);
        } catch (Throwable $ex) {
            $this->setLastError($ex->getMessage());
            $this->logger()->warning(sprintf('Error while executing automation "%s". %s', $this->name, $ex->getMessage()));
            throw new Exception($ex);
        }
    }

    /**
     * Check for new triggers.
     */
    public function checkForNewTriggers()
    {
        $this->logger()->info(sprintf('NEW > Start checking for new trigger'));

        $triggerName = $this->getTriggerAction()->getOption('key');

        // @TODO workaround for automations that are enabled (!) but without a trigger
        if (empty($triggerName)) {
            return;
        }

        switch ($triggerName) {
            case self::TRIGGER_TYPE_WELCOME_NEW_SUBSCRIBER:
                /* triggered immediately by MailListSubscription event */
                break;
            case self::TRIGGER_PARTICULAR_DATE:
                $this->checkForSpecificDatetime();
                break;
            case self::TRIGGER_TYPE_SAY_GOODBYE_TO_SUBSCRIBER:
                /* triggered immediately by MailListSubscription event */
                break;
            case self::TRIGGER_TYPE_SAY_HAPPY_BIRTHDAY:
                $this->checkForDateOfBirth();
                break;
            case self::TRIGGER_API:
                // Just wait for API call
                break;
            case self::TRIGGER_WEEKLY_RECURRING:
                $this->checkForWeeklyRecurring();
                break;
            case self::TRIGGER_MONTHLY_RECURRING:
                $this->checkForMonthlyRecurring();
                break;
            case self::TRIGGER_SUBSCRIPTION_ANNIVERSARY_DATE:
                // In progress
                break;
            case 'others':
                // others
                break;
            default:
                throw new \Exception(sprintf('[Automation: `%s`, Customer account: `%s`] Unknown Automation trigger type: %s', $this->name, $this->customer->uid, $triggerName));
        }
        $this->updateCache();
        $this->logger()->info(sprintf('NEW > Finish checking for new trigger'));
    }

    public function checkForWeeklyRecurring()
    {
        $thisId = $this->id;
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $this->logger()->info(sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s')));
            return;
        }

        $selectedWeekDays = $this->getTriggerAction()->getOptions()['days_of_week'];

        $today = Carbon::now($this->customer->timezone);

        if (!in_array($today->dayOfWeek, $selectedWeekDays)) {
            $this->logger()->info(sprintf('Not the right week day: CURRENT %s (%s) != %s', $today->dayOfWeek, $today->toString(), implode(' ', $selectedWeekDays)));
            return;
        }

        // Only trigger once a day
        // Counting by user timezone
        $startOfDayUtc = $today->startOfDay()->timezone('UTC');

        $subscribers = $this->subscribers()->leftJoin('auto_triggers', function ($join) use ($thisId, $startOfDayUtc) {
            $join->on('auto_triggers.subscriber_id', 'subscribers.id');
            $join->where('auto_triggers.automation2_id', $thisId);
            $join->where('auto_triggers.created_at', '>=', $startOfDayUtc);
        })
        ->whereNull('auto_triggers.subscriber_id')->get();

        // init trigger
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > ??? > Weekly recurring for %s, at %s', $subscriber->email, $startOfDayUtc->toString()));
        }
    }

    public function checkForMonthlyRecurring()
    {
        $thisId = $this->id;
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $this->logger()->info(sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s')));
            return;
        }

        $selectedDays = $this->getTriggerAction()->getOptions()['days_of_month'];

        $today = Carbon::now($this->customer->timezone);

        if (!in_array($today->day, $selectedDays)) {
            $this->logger()->info(sprintf('Not the right day: CURRENT %s (%s) != %s', $today->day, $today->toString(), implode(' ', $selectedDays)));
            return;
        }

        // Only trigger once a day
        // Counting by user timezone
        $startOfDayUtc = $today->startOfDay()->timezone('UTC');

        $subscribers = $this->subscribers()->leftJoin('auto_triggers', function ($join) use ($thisId, $startOfDayUtc) {
            $join->on('auto_triggers.subscriber_id', 'subscribers.id');
            $join->where('auto_triggers.automation2_id', $thisId);
            $join->where('auto_triggers.created_at', '>=', $startOfDayUtc);
        })
        ->whereNull('auto_triggers.subscriber_id')->get();

        // init trigger
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > ??? > Monthly recurring for %s, at %s', $subscriber->email, $startOfDayUtc->toString()));
        }
    }

    public function getSubscribersWithDateOfBirth()
    {
        $formatSql = config('custom.date_format_sql');
        $formatCarbon  = 'Y-m-d'; // DO NOT READ FROM config(), as it is used here only
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        // Get the modify interval: 1 days, 2 days... for example
        $interval = $this->getTriggerAction()->getOptions()['before'];
        $thisId = $this->id;

        $today = Carbon::now($this->customer->timezone)->modify($interval);
        $todayStr = $today->format($formatCarbon);
        $dobFieldUid = $this->getTriggerAction()->getOptions()['field'];
        $dobFieldId = Field::findByUid($dobFieldUid)->id;
        $query = $this->subscribers()
            ->join('subscriber_fields', 'subscribers.id', 'subscriber_fields.subscriber_id')
            ->where('subscriber_fields.field_id', $dobFieldId)
            /*
            ->whereIn(DB::raw(sprintf('SUBSTRING(%s, 1, 10)', table('subscriber_fields.value'))), [
                $today->format('Y-m-d'),
                $today->format('Y:m:d'),
                $today->format('m/d/Y'),
                $today->format('m-d-Y'),
            ])
            */
            ->addSelect('auto_triggers.created_at AS trigger_at')
            ->addSelect('auto_triggers.id AS auto_trigger_id')
            ->addSelect('subscriber_fields.value as dob')
            ->addSelect(
                // IMPORTANT HARD CODED "-" as y/m/d separator
                DB::raw(
                    "DATEDIFF(STR_TO_DATE(CONCAT('".$today->format('Y')."-', DATE_FORMAT(STR_TO_DATE(".table('subscriber_fields.value').", '{$formatSql}'), '%m-%d')), '".$formatSql."'), STR_TO_DATE('".$todayStr."', '".$formatSql."')) AS datediff"
                )
            )
            ->leftJoin('auto_triggers', function ($join) use ($thisId) {
                $join->on('auto_triggers.subscriber_id', 'subscribers.id');
                $join->where('auto_triggers.automation2_id', $thisId);
            });
        //->whereNull('auto_triggers.subscriber_id'); // chưa trigger
        //
        return $query;
    }

    public function checkForDateOfBirth()
    {
        // TODAY + CURRENT TIME
        $currentTime = new DateTime(null, new DateTimeZone($this->customer->timezone));
        // TODAY + GIVEN TIME
        $triggerTime = new DateTime($this->getTriggerAction()->getOptions()['at'], new DateTimeZone($this->customer->timezone));

        if ($currentTime < $triggerTime) {
            $this->logger()->info(sprintf('Not the right time: CURRENT %s < %s', $currentTime->format('Y-m-d H:i:s'), $triggerTime->format('Y-m-d H:i:s')));
            return;
        }

        // Get the modify interval: 1 days, 2 days... for example
        $interval = $this->getTriggerAction()->getOptions()['before'];
        $thisId = $this->id;

        $today = Carbon::now($this->customer->timezone)->modify($interval);
        $dobFieldUid = $this->getTriggerAction()->getOptions()['field'];
        $dobFieldId = Field::findByUid($dobFieldUid)->id;
        $subscribers = $this->subscribersNotTriggeredThisYear()
            ->join('subscriber_fields', 'subscribers.id', 'subscriber_fields.subscriber_id')
            ->where('subscriber_fields.field_id', $dobFieldId)
            ->whereIn(
                DB::raw("DATE_FORMAT(STR_TO_DATE(".table('subscriber_fields.value').", '".config('custom.date_format_sql')."'), '%m-%d')"),
                [$today->format('m-d')]
            )->get();

        // init trigger
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > ??? > Say happy birthday (%s) to %s', $interval, $subscriber->email));
        }
    }

    /**
     * Check for existing triggers update.
     */
    public function checkForExistingTriggersUpdate()
    {
        $this->logger()->info(sprintf('UPDATE > Start checking for trigger update'));
        
        /* FORCE RECHECK
        foreach ($this->autoTriggers as $trigger) {
            $trigger->check();
        }*/

        foreach ($this->pendingAutoTriggers as $trigger) {
            $trigger->check();
        }

        $this->logger()->info(sprintf('UPDATE > Finish checking for trigger update'));
    }

    /**
     * Check for list-subscription events.
     */
    public function triggerImportedContacts()
    {
        $this->logger()->info(sprintf('Start triggering imported contacts'));
        $subscribers = $this->notTriggeredSubscribers()->where('subscribers.subscription_type', '=', Subscriber::SUBSCRIPTION_TYPE_IMPORTED)->get();
        $total = count($subscribers);
        $this->logger()->info(sprintf('Triggering %s imported contacts', $total));

        $i = 0;
        foreach ($subscribers as $subscriber) {
            $i += 1;
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('(%s/%s) triggered imported contact: %s', $i, $total, $subscriber->email));
        }
    }

    /**
     * Check for specific-datetimetime events.
     */
    public function checkForSpecificDatetime()
    {
        $this->logger()->info(sprintf('NEW > Check for Specific Date/Time'));
        
        // this is a one-time triggered automation event
        // just abort if it is already triggered
        //if ($this->autoTriggers()->exists()) {
        //    $this->logger()->info(sprintf('NEW > Already triggered'));
        //    return;
        //}

        $trigger = $this->getTriggerAction();
        $now = $this->customer->getCurrentTime();
        $eventDate = $this->customer->parseDateTime(sprintf('%s %s', $trigger->getOption('date'), $trigger->getOption('at')));

        // Condition same date but after the specified time
        $format = 'Y-m-d';
        $checked = $now->gte($eventDate) && $now->format($format) == $eventDate->format($format);
        
        if (!$checked) {
            $this->logger()->info(sprintf("It is now ({$now->toString('Y-m-d H:M:S')}) NOT the right date/time ({$eventDate->toString('Y-m-d H:M:S')}) for triggering "));
            return;
        }

        $this->logger()->info(sprintf('NEW > It is %s hours due! triggering!', $now->diffInHours($eventDate)));

        $notTriggered = $this->notTriggeredSubscribers()->get();
        $total = $notTriggered->count();
        $i = 0;

        foreach ($notTriggered as $subscriber) {
            $i += 1;
            $this->initTrigger($subscriber);
            $this->logger()->info(sprintf('NEW > (%s/%s) > Adding new trigger for %s', $i, $total, $subscriber->email));
        }
    }

    public function logger()
    {
        $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message%\n");

        if (getenv('LOGFILE') != false) {
            $stream = new RotatingFileHandler(getenv('LOGFILE'), 0, Logger::DEBUG);
        } else {
            $logfile = storage_path('logs/' . php_sapi_name() . '/automation-'.$this->uid.'.log');
            $stream = new RotatingFileHandler($logfile, 0, Logger::DEBUG);
        }

        $stream->setFormatter($formatter);

        $pid = getmypid();
        $logger = new Logger($pid);
        $logger->pushHandler($stream);

        return $logger;
    }

    public function timelinesBy($subscriber)
    {
        $trigger = $this->autoTriggers()->where('subscriber_id', $subscriber->id)->first();

        return (is_null($trigger)) ? Timeline::whereRaw('1=2') : $trigger->timelines(); // trick - return an empty Timeline[] array
    }

    public function getInsight()
    {
        if (!$this->data) {
            return [];
        }

        $actions = json_decode($this->data, true);
        $insights = [];
        foreach ($actions as $action) {
            $insights[$action['id']] = $this->getActionStats($action);
        }

        return $insights;
    }

    public function getActionStats($attributes)
    {
        $total = $this->mailList->readCache('SubscriberCount');

        // The following implementation is prettier but 'countBy' is supported in Laravel 5.8 only
        // $count = $this->autoTriggers()->countBy(function($trigger) {
        //    return $trigger->isActionExecuted($action['id']);
        // });

        // IMPORTANT: this action does not associate with a particular trigger
        $action = $this->getAction($attributes);

        // @DEPRECATED
        // $count = 0;
        // foreach ($this->autoTriggers as $trigger) {
        //     $count += ($trigger->isActionExecuted($action->getId())) ? 1 : 0;
        // }

        // Count the number subscribers whose action has been executed
        $count = $this->subscribersByExecutedAction($action->getId())->count();

        if ($action->getType() == 'ElementTrigger') {
            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.triggered', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementOperation') {
            $count = 0;
            foreach ($this->autoTriggers as $trigger) {
                $count += (is_null($trigger->getActionById($action->getId())->getLastExecuted())) ? 0 : 1;
            }

            $insight = [
                'count' => $count,
                'subtitle' => '#Operation TBD',
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementWait') {
            // Count the numbe contacts with this action as last executed one
            $queue = $this->subscribersByLatestAction($action->getId())->count();

            // since the previous $count also covers $queue ones, so get the already passed one by subtracting
            $passed = $count - $queue;

            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.in-queue2', ['queue' => $queue, 'passed' => $passed]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        } elseif ($action->getType() == 'ElementAction') {
            $Deliver = $this->subscribersByExecutedActionDeliever($action->getId())->get();
            $click = $this->subscribersByExecutedActionClick($action->getId())->get();
            $open = $this->subscribersByExecutedActionOpen($action->getId())->get();
            //dd(count($open));

            $insight = [
                'count' => $count,
                'subtitle' => __('messages.automation.stats.sent', ['count' => $count]),
                'percentage' => ($total != 0) ? ($count / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
                'deliver' => count($Deliver),
                'click' => count($click),
                'open' => count($open),
            ];
        } elseif ($action->getType() == 'ElementCondition') {
            $yes = $this->subscribersByExecutedAction($action->getChildYesId())->count();
            $no = $this->subscribersByExecutedAction($action->getChildNoId())->count();

            $insight = [
                'count' => $yes + $no,
                'subtitle' => __('messages.automation.stats.condition', ['yes' => $yes]),
                'percentage' => ($total != 0) ? (($yes + 0) / $total) : 0,
                'latest_activity' => $this->autoTriggers()->max('created_at'),
            ];
        }

        return $insight;
    }

    public function getSummaryStats()
    {
        $total = $this->subscribers()->count();
        $involved = $this->autoTriggers()->count();

        $leaves = $this->getLeafActions();
        $complete = 0;
        foreach ($leaves as $leaf) {
            $complete += $this->subscribersByLatestAction($leaf)->count();
        }

        $completePercentage = ($total == 0) ? 0 : $complete / $total;

        return [
            'total' => $total,
            'involed' => $involved,
            'complete' => $completePercentage,
            'pending' => $this->getSubscribersWithTriggerInfo()->whereNull('auto_triggers.id')->count(),
        ];
    }

    // for debugging only
    public function getTriggerAction() : Trigger
    {
        $trigger = null;
        $this->getActions(function ($e) use (&$trigger) {
            if ($e->getType() == 'ElementTrigger') {
                $trigger = $e;
            }
        });

        return  $trigger;
    }

    // by Louis
    public function getActions($callback)
    {
        $actions = $this->getElements(true);
        
        foreach ($actions as $action) {
            $instance = $this->getAction($action);
            //MailLog::info('getAction :'.json_encode($action));
            $callback($instance);
        }
    }

    // by Louis
    public function getLeafActions()
    {
        $leaves = [];
        $actions = $this->getElements(true);

        $this->getActions(function ($e) use (&$leaves) {
            if ($e->getNextActionId() == null) {
                $leaves[] = $e->getId();
            }
        });

        return $leaves;
    }

    // IMPORTANT: object returned by this function is not associated with a particular AutoTrigger
    public function getAction($attributes) : Action
    {
        switch ($attributes['type']) {
            case 'ElementTrigger':
                $instance = new Trigger($attributes);
                break;
            case 'ElementAction':
                $instance = new Send($attributes);
                break;
            case 'ElementCondition':
                $instance = new Evaluate($attributes);
                break;
            case 'ElementWait':
                $instance = new Wait($attributes);
                break;
            case 'ElementOperation':
                $instance = new Operate($attributes);
                break;
            default:
                throw new \Exception('Unknown Action type '.$attributes['type']);
        }

        return $instance;
    }

    // get all subscribers
    // if $actionId is provided, get only subscribers who have been triggered and have gone through the action
    public function subscribers($actionId = null)
    {
        if ($this->segment()->exists()) {
            $query = $this->segment->subscribers();
        } else {
            $query = $this->mailList->subscribers()->select('subscribers.*');
        }

        if (!is_null($actionId)) {
            // @deprecated, use the subscribersByLatestAction() method instead
            $query->join('auto_triggers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId.'%')
                 ->where('auto_triggers.automation2_id', $this->id);
        }

        return $query;
    }

    public function subscribersByLatestAction($actionId)
    {
        $query = $this->autoTriggers()->join('subscribers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId)->select('subscribers.*');
        return $query;
    }

    public function subscribersByExecutedAction($actionId)
    {
        $query = $this->autoTriggers()->join('subscribers', 'auto_triggers.subscriber_id', '=', 'subscribers.id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId.'%')->select('subscribers.*');
        return $query;
    }

    public function subscribersByExecutedActionDeliever($actionId)
    {
        $query = $this->autoTriggers()->join('tracking_logs', 'auto_triggers.id', '=', 'tracking_logs.auto_trigger_id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId.'%')->select('tracking_logs.subscriber_id')->groupBy('tracking_logs.subscriber_id');
        

        return $query;

    }

    public function subscribersByExecutedActionClick($actionId)
    {
        $query = $this->autoTriggers()->join('tracking_logs', 'auto_triggers.id', '=', 'tracking_logs.auto_trigger_id')->join('click_logs','click_logs.message_id', '=', 'tracking_logs.message_id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId.'%')->select('tracking_logs.subscriber_id')->groupBy('tracking_logs.subscriber_id');
        return $query;
    }

    public function subscribersByExecutedActionOpen($actionId)
    {
        $query = $this->autoTriggers()->join('tracking_logs', 'auto_triggers.id', '=', 'tracking_logs.auto_trigger_id')->join('open_logs','open_logs.message_id', '=', 'tracking_logs.message_id')
                 ->where('auto_triggers.executed_index', 'LIKE', '%'.$actionId.'%')->select('tracking_logs.subscriber_id')->groupBy('tracking_logs.subscriber_id');
        return $query;
    }

    public function getIntro()
    {
        $triggerType = $this->getTriggerAction()->getOption('key');
        $translationKey = 'messages.automation.intro.'.$triggerType;

        return __($translationKey, ['list' => $this->mailList->name]);
    }

    public function getBriefIntro()
    {
        $triggerType = $this->getTriggerAction()->getOption('key');
        $translationKey = 'messages.automation.brief-intro.'.$triggerType;

        return __($translationKey, ['list' => $this->mailList->name]);
    }

    public function countEmails()
    {
        $count = 0;
        $this->getActions(function ($e) use (&$count) {
            if ($e->getType() == 'ElementAction') {
                $count += 1;
            }
        });

        return $count;
    }

    /**
     * Get recent automations for switch.
     */
    public function getSwitchAutomations($customer)
    {
        return $customer->automation2s()->where('id', '<>', $this->id)->orderBy('updated_at', 'desc')->limit(50);
    }

    /**
     * Get list fields options.
     */
    public function getListFieldOptions()
    {
        $data = [];

        foreach ($this->mailList->getFields()->get() as $field) {
            $data[] = ['text' => $field->label, 'value' => $field->uid];
        }

        return $data;
    }

    /**
     * Produce sample data.
     */
    public function produceSampleData()
    {
        if (is_null(config('app.demo')) || config('app.demo') == false) {
            throw new Exception("Please switch to DEMO mode in .env");
        }

        // Important: set DEMO=true
        // Reset all
        $this->resetListRelatedData();

        $count = $this->mailList->readCache('SubscriberCount');

        $min = (int) ($count * 0.2);
        $max = (int) ($count * 0.7);

        // Generate triggers
        $subscribers = $this->subscribers()->inRandomOrder()->limit(rand($min, $max))->get();
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
        }

        // Run through trigger check
        $this->checkForExistingTriggersUpdate();

        // Update cache
        $this->updateCache();
    }

    /**
     * Clean up after list change.
     */
    public function resetListRelatedData()
    {
        // Delete autoTriggers will also delete
        // + tracking_logs
        // + open logs
        // + click logs
        // + timelines
        $this->autoTriggers()->delete();
        $this->updateCache();
        $this->setLastError(null);
    }

    /**
     * Change mail list.
     */
    public function updateMailList($new_list)
    {
        if ($this->mail_list_id != $new_list->id) {
            $this->mail_list_id = $new_list->id;
            $this->save();

            // reset automation list
            $this->resetListRelatedData();
        }
    }

    /**
     * Fill from request.
     */
    public function fillRequest($request)
    {
        // fill attributes
        $this->fill($request->all());

        // fill segments
        $segments = [];
        $this->segment_id = null;
        if (!empty($request->segment_uid)) {
            foreach ($request->segment_uid as $segmentUid) {
                $segments[] = \Acelle\Model\Segment::findByUid($segmentUid)->id;
            }

            if (!empty($segments)) {
                $this->segment_id = implode(',', $segments);
            }
        }
    }

    /**
     * Get segments.
     */
    public function getSegments()
    {
        if (!$this->segment_id) {
            return collect([]);
        }

        $segments = \Acelle\Model\Segment::whereIn('id', explode(',', $this->segment_id))->get();

        return $segments;
    }

    /**
     * Get segments uids.
     */
    public function getSegmentUids()
    {
        return $this->getSegments()->map->uid->toArray();
    }

    // for debugging only
    public function updateActionOptions($actionId, $data = [])
    {
        $json = json_decode($this->data, true);

        for ($i = 0; $i < sizeof($json); $i += 1) {
            $action = $json[$i];
            if ($action['id'] != $actionId) {
                continue;
            }

            $action['options'] = array_merge($action['options'], $data);

            $json[$i] = $action;
            $this->data = json_encode($json);
            $this->save();
        }
    }

    /**
     * Get segments uids.
     */
    public function execute()
    {
        $subscribers = $this->notTriggeredSubscribers()->get();
        foreach ($subscribers as $subscriber) {
            $this->initTrigger($subscriber);
        }
    }

    public function notTriggeredSubscribers()
    {
        return $this->mailList->activeSubscribers()
                                      ->leftJoin('auto_triggers', function ($join) {
                                          $join->on('subscribers.id', '=', 'auto_triggers.subscriber_id')->where('auto_triggers.automation2_id', '=', $this->id);
                                      })
                                      ->whereNull('auto_triggers.id')->select('subscribers.*');
    }

    public function allowApiCall()
    {
        // Usually invoked by API call
        $type = $this->getTriggerAction()->getOption('key');

        return $type == 'api-3-0';
    }

    /**
     * Update Campaign cached data.
     */
    public function updateCache($key = null)
    {
        // cache indexes
        $index = [
            // @note: SubscriberCount must come first as its value shall be used by the others
            'SummaryStats' => function (&$auto) {
                return $auto->getSummaryStats();
            }
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

    public function updateCacheInBackground()
    {
        dispatch(new \Acelle\Jobs\UpdateAutomation($this));
    }

    public function setLastError($message)
    {
        $this->last_error = $message;
        $this->save();
    }

    public function dump()
    {
        $this->subscribers();
    }

    /**
     * Frequency time unit options.
     *
     * @return array
     */
    public static function waitTimeUnitOptions($moreOptions=[])
    {
        return array_merge($moreOptions, [
            ['value' => 'day', 'text' => trans('messages.day')],
            ['value' => 'week', 'text' => trans('messages.week')],
            ['value' => 'month', 'text' => trans('messages.month')],
            ['value' => 'year', 'text' => trans('messages.year')],
        ]);
    }

    /**
     * Frequency time unit options.
     *
     * @return array
     */
    public static function cartWaitTimeUnitOptions($moreOptions=[])
    {
        return array_merge($moreOptions, [
            ['value' => 'minute', 'text' => trans('messages.minute')],
            ['value' => 'hour', 'text' => trans('messages.hour')],
            ['value' => 'day', 'text' => trans('messages.day')],
            ['value' => 'week', 'text' => trans('messages.week')],
            ['value' => 'month', 'text' => trans('messages.month')],
            ['value' => 'year', 'text' => trans('messages.year')],
        ]);
    }

    /**
     * Get first email.
     *
     * @return email
     */
    public function getAbandonedCartEmail()
    {
        return \Acelle\Model\Email::findByUid($this->getElements()[1]->options->email_uid);
    }

    public function getTriggerType()
    {
        return $this->getTriggerAction()->getOption('key');
    }

    public function isActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    public function getSubscribersWithTriggerInfo()
    {
        $thisId = $this->id;
        return $this->subscribers()
                ->leftJoin('auto_triggers', function ($join) use ($thisId) {
                    $join->on('auto_triggers.subscriber_id', 'subscribers.id');
                    $join->where('auto_triggers.automation2_id', $thisId);
                })
                ->addSelect('auto_triggers.id as auto_trigger_id')
                ->addSelect('auto_triggers.created_at as triggered_at');
    }

    public function getAutoTriggerFor($subscriber)
    {
        $trigger = $this->autoTriggers()->where('subscriber_id', $subscriber->id)->latest()->first();
        return $trigger;
    }

    public function sendingServerList()
    {
        return $this->belongsTo('Acelle\Model\SendingServer');
    }

    public function sendigServer(){
        return $this->smtp_server_id;
    }

    public static function getAllCustomer(){
        $customers = Customer::all();
        dd($customers);
    }

    public function openLogs()
    {
        $total = array();
        $datas = array();
        foreach($this->autoTriggers as $autoTriggers){
            $datas[] = $autoTriggers->id;
        }
        if(count($datas)>0){
            //foreach($this->autoTriggers as $autoTriggers){
                $total = OpenLog::select('open_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
                ->whereIn('tracking_logs.auto_trigger_id', $datas)->get();
            //}

        }
        

        return count($total);
        
    }

    public function openLogsUnique()
    {
        $total = 0;
        $data = array();
        $datas = array();
         foreach($this->autoTriggers as $autoTriggers){
            $datas[] = $autoTriggers->id;
            
        }
        if(count($datas)>0){
        //foreach($this->autoTriggers as $autoTriggers){
            $data =  OpenLog::select('open_logs.message_id')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
            ->whereIn('tracking_logs.auto_trigger_id', $datas)->groupBy('open_logs.message_id')->get();
        //}
        }

        return count($data);
        
    }

    public function clickLogs()
    {
        $total = array();
        $data = array();
         foreach($this->autoTriggers as $autoTriggers){
            $data[] = $autoTriggers->id;
            
        }
        if(count($data)>0){
        //foreach($this->autoTriggers as $autoTriggers){
            $total = ClickLog::select('click_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->whereIn('tracking_logs.auto_trigger_id', $data)->get();
        //}
        }

        return count($total);
        
    }

    public function clickLinksTrigger()
    {
        $total = 0;
        $data = array();
        foreach($this->autoTriggers as $autoTriggers){
            $data[] = $autoTriggers->id;
            
        }
        
        
        return $data;
    }

    public function clickLinks($data = array()){
        if(count($data)>0){
            $implodeData = implode(',',$data);
            $finaldata = ClickLog::selectRaw('count(click_logs.id) as countData, url')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->whereIn('tracking_logs.auto_trigger_id',$data)->groupBy('url')->orderBy('countData','DESC')->get();
            return $finaldata;
        }
        return array();
    }

       public function openLinks($data = array(),$start,$limit){
        //dd($data);
        if(count($data)>0){
            $implodeData = implode(',',$data);
            $finaldata = OpenLog::selectRaw('count(open_logs.id) /4 as countData,subscriber_fields.value,subscriber_fields.subscriber_id as subs_id')->Join('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')->join('subscriber_fields','subscriber_fields.subscriber_id','=','tracking_logs.subscriber_id')
            ->whereIn('tracking_logs.auto_trigger_id',$data)->groupByRaw('subs_id')->orderBy('countData','DESC')->offset($start)->limit($limit)->get();
            
            return $finaldata;
        }
        return array();
    }

     public function openLinksCount($data = array()){
        //dd($data);
        if(count($data)>0){
            $implodeData = implode(',',$data);

            $finaldata = OpenLog::selectRaw('count(open_logs.id) / 4 as countData,subscriber_fields.value,subscriber_fields.subscriber_id as subs_id')->Join('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')->join('subscriber_fields','subscriber_fields.subscriber_id','=','tracking_logs.subscriber_id')
            ->whereIn('tracking_logs.auto_trigger_id',$data)->groupByRaw('subs_id')->orderBy('countData','DESC')->get();
            return $finaldata;
        }
        return array();
    }

    public function topClickEmail($data = array(),$start,$limit){
        //dd($data);
        if(count($data)>0){
            $implodeData = implode(',',$data);
      
            
              $finaldataClick = ClickLog::selectRaw('count(click_logs.id) /4 as countData,subscriber_fields.value,subscriber_fields.subscriber_id as subs_id')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')->join('subscriber_fields','subscriber_fields.subscriber_id','=','tracking_logs.subscriber_id')
            ->whereIn('tracking_logs.auto_trigger_id',$data)->groupBy('subs_id')->orderBy('countData','DESC')->offset($start)->limit($limit)->get();
            //dd($finaldataClick->count());
            return $finaldataClick;
        
           
        }
        return array();
    }
    public function topClickEmailCount($data = array()){
        //dd($data);
        if(count($data)>0){
            $implodeData = implode(',',$data);
      
            
              $finaldataClick = ClickLog::selectRaw('count(click_logs.id) / 4 as countData,subscriber_fields.value,subscriber_fields.subscriber_id as subs_id')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')->join('subscriber_fields','subscriber_fields.subscriber_id','=','tracking_logs.subscriber_id')
            ->whereIn('tracking_logs.auto_trigger_id',$data)->groupBy('subs_id')->orderBy('countData','DESC')->get();
            
            //dd($finaldataClick->count());
            return $finaldataClick;
        
           
        }
        return array();
    }

    public function clickLogsUnique()
    {
        $total = 0;
        $data = array();
        $datas = array();
        DB::enableQueryLog();

        foreach($this->autoTriggers as $autoTriggers){
            $datas[] = $autoTriggers->id;
            
        }
        //foreach($this->autoTriggers as $autoTriggers){
        if(count($datas)>0){
            $data = ClickLog::selectRaw('count(click_logs.id)')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
            ->whereIn('tracking_logs.auto_trigger_id', $datas)->groupBy('click_logs.message_id')->get();
            // if(count($queryData)>0){
            //     $data[] = $queryData;
            // }
        }  
        //}
        //dd(count($data));
        return count($data);
        
    }

    public function bounceLogs()
    {
        $total = 0;
        foreach($this->autoTriggers as $autoTriggers){
            $total += BounceLog::select('bounce_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'bounce_logs.message_id')
            ->where('tracking_logs.auto_trigger_id', '=', 1094)->count();
        }

        return $total;
        
    }

    public function bounceLinksTrigger()
    {
        $total = 0;
        $data = array();
        foreach($this->autoTriggers as $autoTriggers){
            $data[] = $autoTriggers->id;
            
        }
        
        
        return $data;
    }

    public function bounceLinks($data = array()){
        if(count($data)>0){
            $implodeData = implode(',',$data);
            $finaldata = BounceLog::selectRaw('count(bounce_logs.id) as countData, url')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'bounce_logs.message_id')
            ->whereIn('tracking_logs.auto_trigger_id',$data)->groupBy('url')->orderBy('countData','DESC')->limit(5)->get();
            return $finaldata;
        }
        return array();
    }

    public function bounceLogsUnique()
    {
        $total = 0;
        $data = array();
        $datas = array();
        DB::enableQueryLog();
        foreach($this->autoTriggers as $autoTriggers){
            $datas[] = $autoTriggers->id;
            
        }
        //foreach($this->autoTriggers as $autoTriggers){
        if(count($datas)>0){

            $queryData = BounceLog::selectRaw('count(bounce_logs.id)')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'bounce_logs.message_id')
            ->whereIn('tracking_logs.auto_trigger_id', $datas)->groupBy('bounce_logs.message_id')->get();
            if(count($queryData)>0){
                $data[] = $queryData;
            }
            
        //}
        }
        
        return count($data);
        
    }

    public function deliveredCount()
    {
        $total = 0;
       // dd($this->autoTriggers->count());
        if($this->autoTriggers->count()>0){
               foreach($this->autoTriggers as $autoTriggers){
            $total += $autoTriggers->trackingLogs()->where('status', '!=', TrackingLog::STATUS_FAILED)->count();
            }
        }
     
        return $total;
    }
     public function openLogs_new()
    {
        DB::enableQueryLog();
        $total = 0;
        $autoTriggersiDS = array();
        foreach($this->autoTriggers as $autoTriggers){
           $autoTriggersiDS[] = $autoTriggers->id;
            /*$total = OpenLog::select('open_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
            ->where('tracking_logs.auto_trigger_id', '=', $autoTriggers->id)->count();*/
       
        //dd($today,$date);
          
        }
       // dd($autoTriggersiDS);
           $total = OpenLog::Join('tracking_logs', function ($join) use($autoTriggersiDS) {
                    $join->on('tracking_logs.message_id', '=', 'open_logs.message_id')
                         ->whereIn('tracking_logs.auto_trigger_id', $autoTriggersiDS);
                    });
        $total =  $total->groupByRaw('date(open_logs.created_at)');
       // $total =  $total->havingRaw('createDate >= ? and createDate <= ?',[$to,$from]);
        $total =  $total->selectRaw('count(open_logs.id) ,date(open_logs.created_at) as createDate')->get();

        if(!empty($total)){
            $total = $total;
        }else{
            $total = 0;
        }
    //dd($total);
        return $total;
        
    }

    public function clickLogs_new()
    {
        DB::enableQueryLog();
        $total = 0;
        $autoTriggersiDS = array();
        foreach($this->autoTriggers as $autoTriggers){
           $autoTriggersiDS[] = $autoTriggers->id;
            /*$total = OpenLog::select('open_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
            ->where('tracking_logs.auto_trigger_id', '=', $autoTriggers->id)->count();*/
       
        //dd($today,$date);
          
        }
       // dd($autoTriggersiDS);
           $total = ClickLog::Join('tracking_logs', function ($join) use($autoTriggersiDS) {
                    $join->on('tracking_logs.message_id', '=', 'click_logs.message_id')
                         ->whereIn('tracking_logs.auto_trigger_id', $autoTriggersiDS);
                    });
        $total =  $total->groupByRaw('date(click_logs.created_at)');
       // $total =  $total->havingRaw('createDate >= ? and createDate <= ?',[$to,$from]);
        $total =  $total->selectRaw('count(click_logs.id) ,date(click_logs.created_at) as createDate')->get();

        if(!empty($total)){
            $total = $total;
        }else{
            $total = 0;
        }
    //dd($total);
        return $total;
        
    }

    public function bounceLogs_new()
    {
        DB::enableQueryLog();
        $total = 0;
        $autoTriggersiDS = array();
        foreach($this->autoTriggers as $autoTriggers){
           $autoTriggersiDS[] = $autoTriggers->id;
            /*$total = OpenLog::select('open_logs.*')->leftJoin('tracking_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
            ->where('tracking_logs.auto_trigger_id', '=', $autoTriggers->id)->count();*/
       
        //dd($today,$date);
          
        }
       // dd($autoTriggersiDS);
           $total = BounceLog::Join('tracking_logs', function ($join) use($autoTriggersiDS) {
                    $join->on('tracking_logs.message_id', '=', 'bounce_logs.message_id')
                         ->whereIn('tracking_logs.auto_trigger_id', $autoTriggersiDS);
                    });
        $total =  $total->groupByRaw('date(bounce_logs.created_at)');
       // $total =  $total->havingRaw('createDate >= ? and createDate <= ?',[$to,$from]);
        $total =  $total->selectRaw('count(bounce_logs.id) ,date(bounce_logs.created_at) as createDate')->get();

        if(!empty($total)){
            $total = $total;
        }else{
            $total = 0;
        }
    //dd($total);
        return $total;
        
    }




    public function deliveredCount_New()
    { 
        DB::enableQueryLog();
        $total = 0;
        $autoTriggersiDS = array();
        foreach($this->autoTriggers as $autoTriggers){

            $autoTriggersiDS[] = $autoTriggers->id;

            //dd($autoTriggers->id);
            


                      /*      $total = DB::table('tracking_logs')
                        ->join('auto_triggers', 'auto_triggers.id', '=', 'tracking_logs.auto_trigger_id')
                        ->join('automation2s', 'automation2s.id', '=', 'auto_triggers.automation2_id')
                        ->where('tracking_logs.auto_trigger_id', '=', $autoTriggers->id)
                        ->where('tracking_logs.status', '!=', TrackingLog::STATUS_FAILED)
                        ->groupByRaw('trigger_id,date(tracking_logs.created_at)')
                        ->selectRaw('count(tracking_logs.id) ,date(tracking_logs.created_at) as createDate, tracking_logs.auto_trigger_id as trigger_id')
                        ->get();*/
                 /*  $total = TrackingLog::selectRaw('count(id) ,date(created_at) as createDate, auto_trigger_id as trigger_id')->where('tracking_logs.auto_trigger_id', '=', $autoTriggers->id)->where('status', '!=', TrackingLog::STATUS_FAILED)->groupByRaw('trigger_id,date(created_at)')->get();   */

               /*    $total = TrackingLog::Join('auto_triggers', function ($join) use($autoTriggers) {
                    $join->on('auto_triggers.id', '=', 'tracking_logs.auto_trigger_id')
                         ->where('tracking_logs.auto_trigger_id', '=', $autoTriggers->id);
                    });
        $total =  $total->groupByRaw('trigger_id,date(tracking_logs.created_at)');
       // $total =  $total->havingRaw('createDate >= ? and createDate <= ?',[$to,$from]);
        $total =  $total->selectRaw('count(tracking_logs.id) ,date(tracking_logs.created_at) as createDate, tracking_logs.auto_trigger_id as trigger_id')->get(); */       
       // dd($total,DB::getQueryLog());
        }
        $implodeIds = implode(',',$autoTriggersiDS);
        $total = TrackingLog::whereIn('auto_trigger_id',$autoTriggersiDS)->where('status', '!=', TrackingLog::STATUS_FAILED)->groupByRaw('date(created_at)')->selectRaw('count(id) as countData, date(created_at) as createDate ')->get();
         if(!empty($total)){
            $total = $total;
        }else{
            $total = 0;
        }
        //dd($total);
        return $total;
    }

    public static function getConditionWaitOptions($custom=null)
    {
        $result = [
            ['text' => trans_choice('messages.count_hour', 12), 'value' => '12 hours'],
            ['text' => trans_choice('messages.count_day', 1), 'value' => '1 day'],
            ['text' => trans_choice('messages.count_day', 2), 'value' => '2 days'],
            ['text' => trans_choice('messages.count_day', 3), 'value' => '3 days'],
        ];

        if ($custom) {
            if (!in_array($custom, array_map(function ($item) {
                return $item['value'];
            }, $result))) {
                $vals = explode(' ', $custom);
                $result[] = [
                    'text' => trans_choice('messages.count_' . $vals[1], $vals[0]),
                    'value' => $custom,
                ];
            }
        }

        $result[] = ['text' => trans('messages.wait.custom'), 'value' => 'custom'];

        return $result;
    }

}
