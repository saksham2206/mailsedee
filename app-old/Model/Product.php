<?php

namespace Acelle\Model;

use Illuminate\Database\Eloquent\Model;
use Acelle\Model\Source;
use File;

class Product extends Model
{
    public static $itemsPerPage = 16;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['source_item_id'];

    // belongs to customer
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    // belongs to source
    public function source()
    {
        return $this->belongsTo('Acelle\Model\Source');
    }

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            $item->uid = uniqid();
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

    public function scopeSearch($query, $request)
    {
        $query = $query->where('customer_id', '=', $request->user()->id);

        if ($request->q) {
            $query = $query->where('title', 'like', '%'.$request->q.'%');
        }
        
        // Keyword
        if (!empty(trim($request->keyword))) {
            foreach (explode(' ', trim($request->keyword)) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('products.title', 'like', '%'.strtolower($keyword).'%');
                });
            }
        }

        // sort by
        if ($request->sort_by) {
            $sorts = explode('-', $request->sort_by);
            $query = $query->orderBy($sorts[0], $sorts[1]);
        }
        
        $filters = $request->filters;
        if (!empty($filters)) {
            // source
            if (isset($filters['source_uid'])) {
                $source = Source::findByUid($filters['source_uid']);
                $query = $query->where('source_id', '=', $source->id);
            }
        }

        return $query;
    }

    // get image path
    public function getImageDir()
    {
        $path = storage_path("app/users/" . $this->customer->user->uid);
        // create product images folder if not exist
        if (!\File::isDirectory($path)) {
            \File::makeDirectory($path, 0777, true, true);
        }

        $path .= '/products';
        // create product images folder if not exist
        if (!\File::isDirectory($path)) {
            \File::makeDirectory($path, 0777, true, true);
        }

        $path .= '/' . $this->uid;
        // create product images folder if not exist
        if (!\File::isDirectory($path)) {
            \File::makeDirectory($path, 0777, true, true);
        }

        return $path . '/';
    }

    // upload image
    public function uploadImage($url)
    {
        copy($url, $this->getImagePath());
    }

    // get image path
    public function getImagePath()
    {
        return $this->getImageDir() . 'default';
    }
}
