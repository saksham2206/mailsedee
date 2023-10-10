<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Acelle\Model\Product;

class Source extends Model
{
    public static $itemsPerPage = 25;
    public $service;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sources';

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
     * Find item by uid.
     *
     * @return object
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    /**
     * Customer.
     *
     * @return object
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    /**
     * Mail List.
     *
     * @return object
     */
    public function mailList()
    {
        return $this->belongsTo('Acelle\Model\MailList');
    }

    /**
     * Products.
     *
     * @return object
     */
    public function products()
    {
        return $this->hasMany('Acelle\Model\Product');
    }

    /**
     * Get data.
     *
     * @var object | collect
     */
    public function getData()
    {
        if (!$this['meta']) {
            return json_decode('{}', true);
        }

        return json_decode($this['meta'], true);
    }

    /**
     * Update data.
     *
     * @var object | collect
     */
    public function updateData($data)
    {
        $data = (object) array_merge((array) $this->getData(), $data);
        $this['meta'] = json_encode($data);

        $this->save();
    }

    /**
     * Search.
     *
     * @var object | collect
     */
    public function scopeSearch($query, $request)
    {
        $query = $query->where('customer_id', '=', $request->user()->id);
        
        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('sources.type', 'like', '%'.strtolower($keyword).'%');
                });
            }
        }

        return $query;
    }

    /**
     * Check if source connected.
     *
     * @var object | collect
     */
    public function connected()
    {
        return isset($this->id);
    }

    /**
     * Products count.
     *
     * @return object
     */
    public function productsCount()
    {
        return $this->products()->count();
    }
    
    /**
     * Get display name.
     *
     * @return object
     */
    public function getName()
    {
        switch ($this->type) {
            case 'WooCommerce':
                return $this->getData()['data']['name'];
            default:
                return trans('messages.source.' . $this->type);
        }
    }

    /**
     * Get class for exist source.
     *
     * @return object
     */
    public function classMapping()
    {
        $class = '\\Acelle\\Model\\' . $this->type;
        return $class::find($this->id);
    }

    /**
     * Get source list.
     *
     * @return object
     */
    public function getList()
    {
        if ($this->mailList) {
            return $this->mailList;
        }

        // contact
        $contact = new \Acelle\Model\Contact();
        $contact->address_1 = 'empty';
        $contact->country_id = $this->customer->country_id;
        $contact->save();
        
        // list
        $list = new \Acelle\Model\MailList();
        $list->customer_id = $this->customer_id;
        $list->contact_id = $contact->id;
        $list->name = trans('messages.source.list.default_name', [
            'source' => $this->getName(),
        ]);
        $list->default_subject = $this->getName();
        $list->save();

        // list assign
        $this->mail_list_id = $list->id;
        $this->save();

        return $list;
    }
}
