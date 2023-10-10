<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SubscriptionLog extends Model
{
    const TYPE_SUBSCRIBE = 'subscribe';
    const TYPE_SUBSCRIBED = 'subscribed';
    const TYPE_PAID = 'paid';
    const TYPE_CLAIMED = 'claimed';
    const TYPE_UNCLAIMED = 'unclaimed';
    const TYPE_STARTED = 'started';
    const TYPE_EXPIRED = 'expired';
    const TYPE_RENEWED = 'renewed';
    const TYPE_RENEW = 'renew';
    const TYPE_RENEW_FAILED = 'renew_failed';
    const TYPE_PLAN_CHANGE = 'plan_change';
    const TYPE_PLAN_CHANGE_CANCELED = 'plan_change_canceled';
    const TYPE_PLAN_CHANGED = 'plan_changed';
    const TYPE_PLAN_CHANGE_FAILED = 'plan_change_failed';
    const TYPE_CANCELLED = 'cancelled';
    const TYPE_CANCELLED_NOW = 'cancelled_now';
    const TYPE_ADMIN_APPROVED = 'admin_approved';
    const TYPE_ADMIN_REJECTED = 'admin_rejected';
    const TYPE_ADMIN_RENEW_APPROVED = 'admin_renew_approved';
    const TYPE_ADMIN_PLAN_CHANGE_APPROVED = 'admin_plan_change_approved';
    const TYPE_ADMIN_RENEW_REJECTED = 'admin_renew_rejected';
    const TYPE_ADMIN_PLAN_CHANGE_REJECTED = 'admin_plan_change_rejected';
    const TYPE_ADMIN_CANCELLED = 'admin_cancelled';
    const TYPE_ADMIN_CANCELLED_NOW = 'admin_cancelled_now';
    const TYPE_ADMIN_RESUMED = 'admin_resumed';
    const TYPE_ADMIN_PLAN_ASSIGNED = 'admin_plan_assigned';
    const TYPE_RESUMED = 'resumed';
    const TYPE_ERROR = 'error';
    public const TYPE_DISABLE_RECURRING = 'disable_recurring';
    public const TYPE_ENABLE_RECURRING = 'enable_recurring';
    public const TYPE_RENEW_INVOICE = 'renew_invoice';
    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type'
    ];

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $item->uid = uniqid();
        });
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function subscription()
    {
        // @todo dependency injection
        return $this->belongsTo('\Acelle\Model\Subscription');
    }

    /**
     * Get metadata.
     *
     * @var object | collect
     */
    public function getData()
    {
        if (!$this->data) {
            return json_decode('{}', true);
        }

        return json_decode($this->data, true);
    }

    /**
     * Get metadata.
     *
     * @var object | collect
     */
    public function updateData($data)
    {
        $data = (object) array_merge((array) $this->getData(), $data);
        $this->data = json_encode($data);

        $this->save();
    }

    public function renderLog()
    {
        $data = $this->getData();

        switch($this->type) {
            case self::TYPE_SELECT_PLAN:
                return trans('messages.subscription.log.select_plan', [
                    'invoice' => $this->invoice_uid,
                    'plan' => $data['plan'],
                    'customer' => $data['customer'],
                    'amount' => $data['amount'],
                ]);
                break;
            case self::TYPE_PAY_SUCCESS:
                return trans('messages.subscription.log.pay_success', [
                    'invoice' => $this->invoice_uid,
                    'amount' => $data['amount'],
                ]);
                break;
            case self::TYPE_PAY_FAILED:
                return trans('messages.subscription.log.pay_failed', [
                    'invoice' => $this->invoice_uid,
                    'amount' => $data['amount'],
                    'error' => $data['error'],
                ]);
                break;
            case self::TYPE_PAYMENT_PENDING:
                return trans('messages.subscription.log.payment_pending', [
                    'invoice' => $this->invoice_uid,
                    'amount' => $data['amount'],
                ]);
                break;
            case self::TYPE_ADMIN_APPROVE:
                return trans('messages.subscription.log.admin_approve', [
                    'invoice' => $this->invoice_uid,
                    'amount' => $data['amount'],
                ]);
                break;
            case self::TYPE_ADMIN_REJECT:
                return trans('messages.subscription.log.admin_reject', [
                    'invoice' => $this->invoice_uid,
                    'amount' => $data['amount'],
                    'reason' => $data['reason'],
                ]);
                break;
            case self::TYPE_RENEW_INVOICE:
                return trans('messages.subscription.log.renew_invoice', [
                    'invoice' => $this->invoice_uid,
                    'amount' => $data['amount'],
                ]);
                break;
            case self::TYPE_CHANGE_PLAN_INVOICE:
                return trans('messages.subscription.log.change_plan_invoice', [
                    'invoice' => $this->invoice_uid,
                    'plan' => $data['plan'],
                    'new_plan' => $data['new_plan'],
                    'amount' => $data['amount'],
                ]);
                break;
            case self::TYPE_CANCEL_INVOICE:
                return trans('messages.subscription.log.cancel_invoice', [
                    'invoice' => $this->invoice_uid,
                    'amount' => $data['amount'],
                ]);
                break;
            case self::TYPE_CANCEL_SUBSCRIPTION:
                return trans('messages.subscription.log.cancel_subscription', [
                    'plan' => $data['plan'],
                ]);
                break;
            case self::TYPE_DISABLE_RECURRING:
                return trans('messages.subscription.log.disable_recurring', [
                    'plan' => $data['plan'],
                ]);
                break;
            case self::TYPE_ENABLE_RECURRING:
                return trans('messages.subscription.log.enable_recurring', [
                    'plan' => $data['plan'],
                ]);
                break;
            case self::TYPE_END:
                return trans('messages.subscription.log.end', [
                    'plan' => $data['plan'],
                    'ends_at' => $data['ends_at'],
                ]);
                break;
            case self::TYPE_TERMINATE:
                return trans('messages.subscription.log.terminate', [
                    'plan' => $data['plan'],
                    'terminate_at' => $data['terminate_at'],
                ]);
                break;
            default:
                throw new \Exception("Log type $this->type is not found!");
        }
    }
}
