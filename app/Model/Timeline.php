<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class Timeline extends Model
{
    protected $fillable = ['automation2_id', 'subscriber_id', 'auto_trigger_id', 'activity', 'activity_type'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($me) {
            $me->uid = uniqid();
        });
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function subscriber()
    {
        return $this->belongsTo('Acelle\Model\Subscriber');
    }
}
