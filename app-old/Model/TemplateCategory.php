<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;

class TemplateCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name'
    ];

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
     * The template that belong to the categories.
     */
    public function templates()
    {
        return $this->belongsToMany('Acelle\Model\Template', 'templates_categories', 'category_id', 'template_id');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating item.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            $item->uid = $uid;
        });
    }
}
