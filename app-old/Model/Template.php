<?php

/**
 * Template class.
 *
 * Model class for template
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
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Validator;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Acelle\Library\Rss;
use Illuminate\Validation\ValidationException;
use KubAT\PhpSimple\HtmlDomParser;
use Acelle\Library\Tool;
use Acelle\Library\StringHelper;
use File;
use Auth;

class Template extends Model
{
    const BUILDER_ENABLED = true;
    const BUILDER_DISABLED = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'content', 'builder','subject'
    ];

    /**
     * Items per page.
     *
     * @var array
     */
    public static $itemsPerPage = 25;

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
            if (is_null($item->uid)) {
                $item->uid = $uid;
            }
        });
    }

    /**
     * Associations.
     *
     * @var object | collect
     */
    public function customer()
    {
        return $this->belongsTo('Acelle\Model\Customer');
    }

    public function admin()
    {
        return $this->belongsTo('Acelle\Model\Admin');
    }

    /**
     * The template that belong to the categories.
     */
    public function categories()
    {
        return $this->belongsToMany('Acelle\Model\TemplateCategory', 'templates_categories', 'template_id', 'category_id');
    }

    /**
     * Search.
     *
     * @return collect
     */
    public static function scopeCategoryUid($query, $uid)
    {
        $category = \Acelle\Model\TemplateCategory::findByUid($uid);
        // Category
        if ($category) {
            $query = $query->whereHas('categories', function ($q) use ($category) {
                $q->whereIn('template_categories.id', [$category->id]);
            });
        }
    }

    /**
     * Search.
     *
     * @return collect
     */
    public static function scopeNotAssociated($query)
    {
        $query->whereNotIn('id', function ($q) {
            $q->select('template_id')->from('emails')->whereNotNull('template_id');
        });

        $query->whereNotIn('id', function ($q) {
            $q->select('template_id')->from('campaigns')->whereNotNull('template_id');
        });
    }

    /**
     * Search.
     *
     * @return collect
     */
    public static function scopeSearch($query, $keyword)
    {
        // Keyword
        if (!empty($keyword)) {
            $query = $query->where('name', 'like', '%'.trim($keyword).'%');
        }
    }

    /**
     * Customer templates.
     *
     * @return collect
     */
    public static function scopeCustom($query)
    {
        $query = $query->where('customer_id', '!=', null);
    }

    /**
     * Public/Gallery templates.
     *
     * @return collect
     */
    public static function scopeShared($query)
    {
        $query = $query->where('customer_id', '=', null);
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
     * Template tags.
     *
     * All availabel template tags
     */
    public static function tags($list = null)
    {
        $tags = [];

        $tags[] = ['name' => 'SUBSCRIBER_EMAIL', 'required' => false];

        // List field tags
        if (isset($list)) {
            foreach ($list->fields as $field) {
                if ($field->tag != 'EMAIL') {
                    $tags[] = ['name' => 'SUBSCRIBER_'.$field->tag, 'required' => false];
                }
            }
        }

        $tags = array_merge($tags, [
            ['name' => 'UNSUBSCRIBE_URL', 'required' => false],
            ['name' => 'SUBSCRIBER_UID', 'required' => false],
            ['name' => 'WEB_VIEW_URL', 'required' => false],
            ['name' => 'CAMPAIGN_NAME', 'required' => false],
            ['name' => 'CAMPAIGN_UID', 'required' => false],
            ['name' => 'CAMPAIGN_SUBJECT', 'required' => false],
            ['name' => 'CAMPAIGN_FROM_EMAIL', 'required' => false],
            ['name' => 'CAMPAIGN_FROM_NAME', 'required' => false],
            ['name' => 'CAMPAIGN_REPLY_TO', 'required' => false],
            ['name' => 'CURRENT_YEAR', 'required' => false],
            ['name' => 'CURRENT_MONTH', 'required' => false],
            ['name' => 'CURRENT_DAY', 'required' => false],
            ['name' => 'CONTACT_NAME', 'required' => false],
            ['name' => 'CONTACT_COUNTRY', 'required' => false],
            ['name' => 'CONTACT_STATE', 'required' => false],
            ['name' => 'CONTACT_CITY', 'required' => false],
            ['name' => 'CONTACT_ADDRESS_1', 'required' => false],
            ['name' => 'CONTACT_ADDRESS_2', 'required' => false],
            ['name' => 'CONTACT_PHONE', 'required' => false],
            ['name' => 'CONTACT_URL', 'required' => false],
            ['name' => 'CONTACT_EMAIL', 'required' => false],
            ['name' => 'LIST_NAME', 'required' => false],
            ['name' => 'LIST_SUBJECT', 'required' => false],
            ['name' => 'LIST_FROM_NAME', 'required' => false],
            ['name' => 'LIST_FROM_EMAIL', 'required' => false],
        ]);

        return $tags;
    }

    /**
     * Display creator name.
     *
     * @return string
     */
    public function displayCreatorName()
    {
        return is_object($this->admin) ? $this->admin->user->displayName() : (is_object($this->customer) ? $this->customer->user->displayName() : '');
    }

    /**
     * Contain category
     *
     * @return void
     */
    public function hasCategory($category)
    {
        return $this->categories()->where('template_categories.id', $category->id)->exists();
    }

    /**
     * Add category
     *
     * @return void
     */
    public function addCategory($category)
    {
        if (!$this->hasCategory($category)) {
            $this->categories()->attach($category->id);
        }
    }

    /**
     * Remove category
     *
     * @return void
     */
    public function removeCategory($category)
    {
        if ($this->hasCategory($category)) {
            $this->categories()->detach($category->id);
        }
    }

    /**
     * Copy new template.
     */
    public function copy($attributes = [])
    {
        $copy = $this->replicate();
        
        if (isset($attributes['name'])) {
            $copy->name = $attributes['name'];
        }
    
        // Important: overwrite the UID attributes
        $copy->uid = uniqid();
        $copy->created_at = \Carbon\Carbon::now();
        $copy->updated_at = \Carbon\Carbon::now();
        
        if (isset($attributes['customer_id'])) {
            $copy->admin_id = null;
            $copy->customer_id = $attributes['customer_id'];
        }

        if (isset($attributes['admin_id'])) {
            $copy->admin_id = $attributes['admin_id'];
            $copy->customer_id = null;
        }

        // Copy directory
        Tool::xcopy($this->getStoragePath(), $copy->getStoragePath());

        // Then finally save
        $copy->save();

        // Important: save before adding categories
        foreach ($this->categories as $category) {
            $copy->addCategory($category);
        }

        // return
        return $copy;
    }

    /**
     * Load from directory.
     */
    public function loadContent($directory)
    {
        // try to find the main file, index.html | index.html | file_name.html | ...
        $indexFile = null;
        $thumb = null;
        $sub_path = '';

        // find index
        $possible_indexFile_names = array('index.html', 'index.htm');
        foreach ($possible_indexFile_names as $name) {
            if (is_file($file = join_paths($directory, $name))) {
                $indexFile = $file;
                break;
            }
        }
        // if not find any first html file
        if ($indexFile === null) {
            $objects = scandir($directory);
            foreach ($objects as $object) {
                if ($object != '.' && $object != '..') {
                    if (!is_dir(join_paths($directory, $object))) {
                        if (preg_match('/\.html?$/i', $object)) {
                            $indexFile = $directory.'/'.$object;
                            break;
                        }
                    }
                }
            }
        }

        // can not find main file
        if ($indexFile === null) {
            $validator = Validator::make(['file' => ''], []);
            $validator->errors()->add('file', 'Cannot find index HTML file');
            throw new ValidationException($validator);
        }

        // read main file content
        $html_content = trim(file_get_contents($indexFile));
        $this->content = $html_content;
        Tool::xcopy($directory, $this->getStoragePath());
        $this->transformAssetsUrls(); // already save
        return $this;
    }

    /**
     * Upload a template.
     */
    public static function uploadSystemTemplate($request)
    {
        return self::uploadTemplate($request, true);
    }

    /**
     * Upload a template.
     */
    public static function uploadTemplate($request, $asAdmin = false)
    {
        $user = $request->user();

        $rules = array(
            'file' => 'required|mimetypes:application/zip,application/octet-stream,application/x-zip-compressed,multipart/x-zip',
            'name' => 'required',
        );

        $validator = Validator::make($request->all(), $rules, [
            'file.mimetypes' => 'Input must be a valid .zip file',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // move file to temp place
        $tmpPath = storage_path('tmp/uploaded_template_'.$user->id.'_'.time());
        $tmpName = $request->file('file')->getClientOriginalName();
        $request->file('file')->move($tmpPath, $tmpName);
        $tmpZip = join_paths($tmpPath, $tmpName);

        // read zip file check if zip archive invalid
        $zip = new ZipArchive();
        if ($zip->open($tmpZip, ZipArchive::CREATE) !== true) {
            // @todo hack
            // $validator = Validator::make([], []); // Empty data and rules fields
            $validator->errors()->add('file', 'Cannot open .zip file');
            throw new ValidationException($validator);
        }

        // unzip template archive and remove zip file
        $zip->extractTo($tmpPath);
        $zip->close();
        unlink($tmpZip);

        // Build template's attributes
        $attributes = $request->all();
        $attributes['builder'] = self::BUILDER_DISABLED;

        if ($asAdmin) {
            $attributes['admin_id'] = $request->user()->admin->id;
        } else {
            $attributes['customer_id'] = $request->user()->customer->id;
        }

        // Save new template
        $template = self::createFromDirectory($attributes, $tmpPath);
        Tool::xdelete($tmpPath);

        return $template;
    }

    public function toZip() : string
    {
        // Get real path for our folder
        $rootPath = $this->getStoragePath();
        $outputPath = join_paths('/tmp/', $this->uid.'.zip');

        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($outputPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        return $outputPath;
    }

    /**
     * Get public campaign upload dir.
     */
    public function getStoragePath($path = '/')
    {
        if ($this->customer) {
            // storage/app/users/{uid}/templates
            $base = $this->customer->getTemplatesPath($this->uid);
        } else {
            // storage/app/templates/templates
            // IMPORTANT: templates are created from migration without associating with an admin
            $base = $this->getSystemStoragePath($this->uid);
        }

        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }

        return join_paths($base, $path);
    }

    private function getSystemStoragePath($path = null)
    {
        $base = storage_path('app/templates/');

        if (!\File::exists($base)) {
            \File::makeDirectory($base, 0777, true, true);
        }

        return join_paths($base, $path);
    }

    /**
     * Get thumb.
     */
    public function getThumbName()
    {
        // find index
        $names = array('thumbnail.svg', 'thumbnail.png', 'thumbnail.jpg', 'thumb.svg', 'thumb.png', 'thumb.jpg');
        foreach ($names as $name) {
            $path = $this->getStoragePath($name);
            if (file_exists($path)) {
                return $name;
            }
        }

        return;
    }

    /**
     * Get thumb.
     */
    public function getThumbUrl()
    {
        if (is_null($this->uid)) {
            throw new Exception('Cannot getThumbUrl(), template does not have a UID, cannot transform content');
        }

        if ($this->getThumbName()) {
            return \Acelle\Helpers\generatePublicPath($this->getStoragePath($this->getThumbName()));
        } else {
            return url('assets/images/placeholder.jpg');
        }
    }

    public function transformAssetsUrls()
    {
        $this->content = $this->getContentWithTransformedAssetsUrls();
        return $this->save();
    }

    /**
     * Transform template's relative URLs to application's absolute URL, without hostname.
     * Execute this every time the template is SAVED
     */
    public function getContentWithTransformedAssetsUrls($host = null)
    {
        
        if (is_null($this->uid)) {
            throw new Exception('Template does not have a UID, cannot transform content');
        }

        // Replace #1

        $content = Tool::replaceTemplateUrl($this->content, function ($assetPath, $isRoot) use ($host) {
            if ($isRoot) {
                // If it is a root path like "/files/....", then do nothing
                return ($host) ? join_url($host, $assetPath) : $assetPath;
            } else {
                // Transform relative URLs to PUBLIC ABSOLUTE URLs, but without hostname
                $url = \Acelle\Helpers\generatePublicPath(
                    $this->getStoragePath($assetPath),
                    $absolute = false
                );

                return ($host) ? join_url($host, $url) : $url;
            }
        });

        // By the way, fix <html>
        $content = tinyDocTypeTransform($content);
        return $content;
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

    public function uploadAssetFromBase64($base64)
    {
        // upload file by upload image
        $filename = uniqid();

        // Storage path of the uploaded asset:
        // For example: /storage/templates/{type}/{ID}/604ce5e36d0fa
        $filepath = $this->getStoragePath($filename);

        // Store it
        file_put_contents($filepath, file_get_contents($base64));
        $assetUrl = \Acelle\Helpers\generatePublicPath($filepath);

        return $assetUrl;
    }

    public function uploadAssetFromUrl($url)
    {
        // upload file by upload image
        $filename = uniqid();

        // Storage path of the uploaded asset:
        // For example: /storage/templates/{type}/{ID}/604ce5e36d0fa
        $filepath = $this->getStoragePath($filename);

        // Download the file's content
        $content = file_get_contents($url);

        // Store it:
        $arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        ); 
        file_put_contents($filepath, $content, false, stream_context_create($arrContextOptions));
        $assetUrl = \Acelle\Helpers\generatePublicPath($filepath);

        return $assetUrl;
    }

    /**
     * Upload asset.
     */
    public function uploadAsset($file)
    {
        // Store to template storage storage/app/customers/000000/templates/111111/ASSET.JPG
        $name = StringHelper::sanitizeFilename($file->getClientOriginalName());
        $name = StringHelper::generateUniqueName($this->getStoragePath(), $name);

        // Move uploaded file
        $file->move($this->getStoragePath(), $name);
        $assetUrl = \Acelle\Helpers\generatePublicPath($this->getStoragePath($name));
        
        return $assetUrl;
    }

    /**
     * Template tags.
     *
     * All availabel template tags
     */
    public static function builderTags($list = null)
    {
        $tags = self::tags($list);

        $result = [];

        if (true) {
            
            // Unsubscribe link
            $result[] = [
                'type' => 'label',
                'text' => '<a href="{UNSUBSCRIBE_URL}">' . trans('messages.editor.unsubscribe_text') . '</a>',
                'tag' => '{UNSUBSCRIBE_LINK}',
                'required' => true,
            ];
            
            // web view link
            $result[] = [
                'type' => 'label',
                'text' => '<a href="{WEB_VIEW_URL}">' . trans('messages.editor.click_view_web_version') . '</a>',
                'tag' => '{WEB_VIEW_LINK}',
                'required' => true,
            ];
        }

        foreach ($tags as $tag) {
            $result[] = [
                'type' => 'label',
                'text' => '{'.$tag['name'].'}',
                'tag' => '{'.$tag['name'].'}',
                'required' => true,
            ];
        }

        return $result;
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function getBuilderAdminTemplates()
    {
        $result = [];

        // Gallery
        $templates = self::shared()
            ->get();

        foreach ($templates as $template) {
            $result[] = [
                'name' => $template->name,
                'url' => action('Admin\TemplateController@builderChangeTemplate', ['uid' => $this->uid, 'change_uid' => $template->uid]),
                'thumbnail' => $template->getThumbUrl(),
            ];
        }

        return $result;
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function changeTemplate($template)
    {
        $this->content = $template->content;
        $this->save();

        // delete current template folder
        $this->clearStorage();
        
        // Copy uploaded folder
        if (file_exists($this->getStoragePath())) {
            if (!file_exists($this->getStoragePath())) {
                mkdir($this->getStoragePath(), 0777, true);
            }

            Tool::xcopy($template->getStoragePath(), $this->getStoragePath());
        }
    }

    /**
     * Upload template thumbnail.
     *
     * @return mixed
     */
    public function uploadThumbnail($file)
    {
        $file->move($this->getStoragePath(), 'thumbnail.png');
    }

    /**
     * Upload template thumbnail Url.
     *
     * @return mixed
     */
    public function uploadThumbnailUrl($url)
    {
        $contents = file_get_contents($url);
        file_put_contents($this->getStoragePath('thumbnail.png'), $contents);
    }

    public function inlineHtml($html)
    {
        return $html;
    }

    /**
     * Create template from dir.
     *
     * @return Template
     */
    public static function createFromDirectory($attributes, $directory)
    {
        $template = new self();
        $template->name = $attributes['name'];
        $template->builder = $attributes['builder']; // whether or not this template can be modified using a builder

        // System or Customer template
        if (array_key_exists('customer_id', $attributes)) {
            $template->customer_id = $attributes['customer_id'];
        }
        
        $template->uid = uniqid();
        $template->loadContent($directory); // already saved!
        return $template;
    }

    public function clearStorage()
    {
        Tool::xdelete($this->getStoragePath());
    }

    public function deleteAndCleanup()
    {
        $this->clearStorage();
        $this->delete();
    }

    public static function saveTemplate($request)
    {
        $template = new self();
        $template->customer_id = Auth::user()->customer->id;
        $template->name = $request->name;
        $template->subject = $request->subject;
        $template->content = $request->content;
        $template->save();

        return $template;
    }

    public static function updateTemplate($uid,$request)
    {
        $template = self::findByUid($uid);
        $template->update(['name'=>$request->name,'subject'=>$request->subject,'content'=>$request->content]);
        
        return $template;
    }

}
