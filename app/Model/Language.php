<?php

/**
 * Language class.
 *
 * Model class for languages
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
use Acelle\Library\Facades\Hook;
use File;

class Language extends Model
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Get users.
     *
     * @return mixed
     */
    public function users()
    {
        return $this->hasMany('Acelle\Model\User');
    }

    /**
     * Customer association.
     *
     * @return mixed
     */
    public function customers()
    {
        return $this->hasMany('Acelle\Model\Customer');
    }

    /**
     * Admin association.
     *
     * @return mixed
     */
    public function admins()
    {
        return $this->hasMany('Acelle\Model\Admin');
    }

    /**
     * Language folder path.
     *
     * @return string
     */
    public function languageDir()
    {
        return resource_path(join_paths('lang', $this->code));
    }

    public static function getDirWhichNewLanguageCopyFrom()
    {
        return base_path('resources/lang/default');
    }

    public static function scopeActive($query)
    {
        $query->where('status', '=', self::STATUS_ACTIVE);
    }

    /**
     * Get select options.
     *
     * @return array
     */
    public static function getSelectOptions()
    {
        $options = self::active()->get()->map(function ($item) {
            return ['value' => $item->id, 'text' => $item->name];
        });

        return $options;
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
            $keyword = trim($keyword);
            foreach (explode(' ', $keyword) as $keyword) {
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('languages.name', 'like', '%'.$keyword.'%')
                        ->orwhere('languages.code', 'like', '%'.$keyword.'%')
                        ->orwhere('languages.region_code', 'like', '%'.$keyword.'%');
                });
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
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            $item->uid = $uid;
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'code', 'region_code',
    ];

    /**
     * Get validation rules.
     *
     * @return object
     */
    public function rules()
    {
        return [
            'name' => 'required',
            'code' => 'required|unique:languages,code,'.$this->id,
        ];
    }

    public static function scopeDefault($query)
    {
        return $query->where('is_default', '=', true);
    }

    /**
     * Get is default language.
     *
     * @var object
     */
    public static function getFirstDefaultLanguage()
    {
        return self::default()->first();
    }

    /**
     * Get locale array from file.
     *
     * @var array
     */
    public function getLocaleArrayFromFile($filename)
    {
        clearstatcache();
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate(join_paths($this->languageDir(), $filename.'.php'));
        }

        $arr = self::fileToArray(join_paths($this->languageDir(), $filename.'.php'));
        return $arr;
    }

    /**
     * Read locale file.
     *
     * @var text
     */
    public function readLocaleFile($filename)
    {
        $text = \File::get(join_paths($this->languageDir(), $filename.'.php'));

        return $text;
    }

    /**
     * Read locale file.
     *
     * @var text
     */
    public function localeToYaml($filename)
    {
        $text = $this->readLocaleFile($filename);

        return yaml_parse($text);
    }

    /**
     * Update language file from yaml.
     *
     * @var text
     */
    public function updateFromYaml($filename, $yaml)
    {
        self::yamlToFile(join_paths($this->languageDir(), $filename.'.php'), $yaml);
    }

    /**
     * Update language file from yaml.
     *
     * @var text
     */
    public function getBuilderLang()
    {
        return include join_paths($this->languageDir(), 'builder.php');
    }

    /**
     * all language code.
     *
     * @return array
     */
    public static function languageCodes()
    {
        $arr = config('languages');

        $result = [];
        foreach ($arr as $key => $name) {
            $result[] = [
                'text' => strtoupper($key).' / '.$name,
                'value' => $key,
            ];
        }

        return $result;
    }

    /**
     * Disable language.
     *
     * @return array
     */
    public function disable()
    {
        $this->status = self::STATUS_INACTIVE;
        $this->save();
    }

    /**
     * Enable language.
     *
     * @return array
     */
    public function enable()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public static function fileToArray($pathToFile)
    {
        return \File::getRequire($pathToFile);
    }

    public static function arrayToYaml($array)
    {
        return \Yaml::dump($array);
    }

    public static function fileToYaml($path)
    {
        return self::arrayToYaml(self::fileToArray($path));
    }

    public static function yamlToFile($pathToFile, $yaml)
    {
        $content = '<?php return '.var_export(\Yaml::parse($yaml), true).' ?>';
        $bytes_written = \File::put($pathToFile, $content);
    }

    public function getAllLanguageFiles()
    {
        $paths = [];

        $files = Hook::execute('add_translation_file');
        foreach ($files as $file) {
            $path = join_paths($file['translation_folder'], $this->code, $file['file_name']);

            if (in_array($file['id'], array_keys($paths))) {
                throw new \Exception('Translation file id already exists: ' . $file['id']);
            }

            if (!file_exists($path)) {
                \Acelle\Library\Tool::xcopy(
                    join_paths($file['translation_folder'], $file['default']),
                    join_paths($file['translation_folder'], $this->code),
                );
            }

            $paths[$file['id']] = [
                'id' => $file['id'],
                'type' => isset($file['type']) ? $file['type'] : 'plugin',
                'path' => $path,
                'file_title' => $file['file_title'],
            ];
        }

        return $paths;
    }

    public function getLanguageFilesByType($type)
    {
        $langFiles = $this->getAllLanguageFiles();
        foreach ($langFiles as $key => $langFile) {
            if ($langFile['type'] != $type) {
                unset($langFiles[$key]);
            }
        }

        return $langFiles;
    }

    public static function newDefaultLanguage()
    {
        $language = new self();
        $language->status = self::STATUS_ACTIVE;

        return $language;
    }

    public static function createFromRequest($request)
    {
        $language = self::newDefaultLanguage();

        $language->fill($request->all());
        $language->status = self::STATUS_INACTIVE;

        // make validator
        $validator = \Validator::make($request->all(), $language->rules());

        // redirect if fails
        if ($validator->fails()) {
            return [$language, $validator];
        }

        // save
        $language->save();

        // copy from default language folder
        $des = $language->languageDir();
        if (!file_exists($des)) {
            $oldmask = umask(0);
            mkdir($des, 0775, true);
            $sou = self::getDirWhichNewLanguageCopyFrom();
            \Acelle\Library\Tool::xcopy($sou, $des);
            umask($oldmask);
        }

        return [$language, true];
    }
    
    public function updateFromRequest($request)
    {
        // make validator
        $validator = \Validator::make($request->all(), $this->rules());

        // redirect if fails
        if ($validator->fails()) {
            return $validator;
        }

        // rename locale folder
        if ($this->code != $request->code) {
            rename(base_path("resources/lang/") . $this->code, base_path("resources/lang/") . $request->code);
        }

        $this->fill($request->all());

        // save
        $this->save();

        return true;
    }

    public function deleteAndCleanup()
    {
        // Change deleting language's users to the default langauge
        $default_language = self::getFirstDefaultLanguage();

        if (!$default_language) {
            throw new \Exception('Something went wrong! Can not find the default language.');
        }

        $this->customers()->update(['language_id' => $default_language->id]);
        $this->admins()->update(['language_id' => $default_language->id]);

        // delete language folder
        $des = $this->languageDir();
        if (file_exists($des)) {
            \Acelle\Library\Tool::xdelete($des);
        }

        $this->delete();
    }

    public function translateFile($fileId, $content)
    {
        $file = $this->findFileById($fileId);

        // make validator
        $validator = \Validator::make(['content' => $content], [
            'content' => 'required',
        ]);

        // test amazon api connection
        $validator->after(function ($validator) use ($file, $content) {
            try {
                var_export(\Yaml::parse($content), true);
            } catch (\Exception $e) {
                $validator->errors()->add('content', $e->getMessage());
            }
        });

        // redirect if fails
        if ($validator->fails()) {
            return [$file, $validator];
        }

        // save
        self::yamlToFile($file['path'], $content);

        \Artisan::call('cache:clear');

        // @todo
        // make sure language file updated.
        sleep(5);

        return [$file, $validator];
    }

    public function findFileById($id)
    {
        if (!isset($this->getAllLanguageFiles()[$id])) {
            throw new \Exception('Can not find translation file with id: ' . $id);
        }

        return $this->getAllLanguageFiles()[$id];
    }

    public function getDefaultFile()
    {
        $files = $this->getAllLanguageFiles();
        return array_shift($files);
    }

    public function upload($request)
    {
        // make validator
        $validator = \Validator::make($request->all(), [
            'file' => 'required',
        ]);

        // test amazon api connection
        $validator->after(function ($validator) use ($request) {
            $zip = new \ZipArchive();

            // check if file is zip achive
            $file_ext = $request->file('file')->guessExtension();
            if ($file_ext != 'zip') {
                $validator->errors()->add('content', 'Upload file is not zip file');
                return;
            }
            
            // move file to temp place
            $tmp_path = storage_path('tmp');
            $file_name = 'language-package';
            $request->file('file')->move($tmp_path, $file_name);
            
            // after moving, request['file'] will no longer be there
            $rules = [];
            $tmp_zip = storage_path("tmp/{$file_name}");
            $openZip = $zip->open($tmp_zip, \ZipArchive::CREATE);

            // read zip file check if zip archive invalid
            if ($openZip !== true) {
                $validator->errors()->add('content', 'Upload file is not valide archive file');
                return;
            }

            // unzip template archive and remove zip file
            $zip->extractTo($this->languageDir());
            $zip->close();
            unlink($tmp_zip);
        });

        return $validator;
    }
}
