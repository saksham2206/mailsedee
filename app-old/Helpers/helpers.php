<?php

/**
 * Globally available helper methods.
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

/**
 * Get full table name by adding the DB prefix.
 *
 * @param string table name
 *
 * @return string fulle table name with prefix
 */
function table($name)
{
    return \DB::getTablePrefix().$name;
}

/**
 * Quote a value with astrophe to inject to an SQL statement.
 *
 * @param string original value
 *
 * @return string quoted value
 * @todo: use MySQL escape function to correctly escape string with astrophe
 */
function quote($value)
{
    return "'$value'";
}

/**
 * Quote a value with astrophe to inject to an SQL statement.
 *
 * @param string original value
 *
 * @return string quoted value
 * @todo: use MySQL escape function to correctly escape string with astrophe
 */
function db_quote($value)
{
    return \DB::connection()->getPdo()->quote($value);
}

/**
 * Break an array into smaller batches (arrays).
 *
 * @param array original array
 * @param int batch size
 * @param bool whether or not to skip the first header line
 * @param callback function
 */
function each_batch($array, $batchSize, $skipHeader, $callback)
{
    $batch = [];
    foreach ($array as $i => $value) {
        // skip the header
        if ($i == 0 && $skipHeader) {
            continue;
        }

        if ($i % $batchSize == 0) {
            $callback($batch);
            $batch = [];
        }
        $batch[] = $value;
    }

    // the last callback
    if (sizeof($batch) > 0) {
        $callback($batch);
    }
}

/**
 * Join filesystem path strings.
 *
 * @param * parts of the path
 *
 * @return string a full path
 */
function join_paths()
{
    $paths = array();
    foreach (func_get_args() as $arg) {
        if (preg_match('/http:\/\//i', $arg)) {
            throw new \Exception('Path contains http://! Use `join_url` instead. Error for '.implode('/', func_get_args()));
        }

        if ($arg !== '') {
            $paths[] = $arg;
        }
    }

    return preg_replace('#/+#', '/', implode('/', $paths));
}

/**
 * Join URL parts.
 *
 * @param * parts of the URL. Note that the first part should be something like http:// or http://host.name
 *
 * @return string a full URL
 */
function join_url()
{
    $paths = array();
    foreach (func_get_args() as $arg) {
        if (!empty($arg)) {
            $paths[] = $arg;
        }
    }

    return preg_replace('#(?<=[^:])/+#', '/', implode('/', $paths));
}

/**
 * Get unique array based on user defined condition.
 *
 * @param array original array
 *
 * @return array unique array
 */
function array_unique_by($array, $callback)
{
    $result = [];
    foreach ($array as $value) {
        $key = $callback($value);
        $result[$key] = $value;
    }

    return array_values($result);
}

/**
 * Get UTC offset of a particular time zone.
 *
 * @param string timezone
 *
 * @return string UTC offset (+02:00 for example)
 */
function utc_offset($timezone)
{
    $offset = \Carbon\Carbon::now($timezone)->offsetHours - \Carbon\Carbon::now('UTC')->offsetHours;

    return sprintf("%+'03d:00", $offset);
}

/**
 * Check if exec() function is available.
 *
 * @return bool
 */
function exec_enabled()
{
    try {
        // make a small test
        exec('ls');

        return function_exists('exec') && !in_array('exec', array_map('trim', explode(', ', ini_get('disable_functions'))));
    } catch (\Exception $ex) {
        return false;
    }
}

function reset_app_url($force = false)
{ // replace if already exists
    // update .env file, set app_url to current host url

    // get .env file path
    $path = base_path('.env');
    $raw = preg_split('/[\r\n]+/', file_get_contents($path));

    // read from .env, load into $settings as [ key1 => value1, key2 => value2, etc. ]
    $settings = [];
    foreach ($raw as $e) {
        preg_match('/^(?<key>[A-Z0-9_]+)=(?<value>.*)/', $e, $matched);

        if (array_key_exists('key', $matched) && array_key_exists('value', $matched)) {
            $settings[$matched['key']] = $matched['value'];
        }
    }

    // add APP_URL setting if not exists
    if (!array_key_exists('APP_URL', $settings)) {
        $settings['APP_URL'] = url('/');
    } elseif ($force) {
        $settings['APP_URL'] = url('/');
    }

    // Write back to .env file
    $file = fopen($path, 'w');
    foreach ($settings as $key => $value) {
        fwrite($file, "{$key}=$value\n");
    }
    fclose($file);
}

/**
 * Run artisan migrate.
 *
 * @return bool
 */
function artisan_migrate()
{
    \Artisan::call('migrate', ['--force' => true]);
}

/**
 * Check if site is in demo mod.
 *
 * @return bool
 */
function isSiteDemo()
{
    return config('app.demo');
}

/**
 * Get language code.
 *
 * @return string
 */
function language_code()
{
    // Get default language code from setting
    $default_language = \Acelle\Model\Language::find(\Acelle\Model\Setting::get('default_language'));

    if (isset($_COOKIE['last_language_code'])) {
        $language_code = $_COOKIE['last_language_code'];
    } elseif (is_object($default_language)) {
        $language_code = $default_language->code;
    } else {
        $language_code = 'en';
    }

    return $language_code;
}

/**
 * Get language code.
 *
 * @return string
 */
function language()
{
    return \Acelle\Model\Language::where('code', '=', language_code())->first();
}

/**
 * Format a number as percentage.
 *
 * @return string
 */
function number_to_percentage($number, $precision = 2)
{
    if (!is_numeric($number)) {
        return $number;
    }

    return sprintf("%.{$precision}f%%", $number * 100);
}

/**
 * Format a number with delimiter.
 *
 * @return string
 */
function number_with_delimiter($number, $precision = 0, $seperator = ',')
{
    if (!is_numeric($number)) {
        return $number;
    }

    return number_format($number, $precision, '.', $seperator);
}

/**
 * Function to convert IP address to IP number (IPv6).
 *
 * @return string
 */
function Dot2LongIPv6($IPaddr)
{
    $int = inet_pton($IPaddr);
    $bits = 15;
    $ipv6long = 0;
    while ($bits >= 0) {
        $bin = sprintf('%08b', (ord($int[$bits])));
        if ($ipv6long) {
            $ipv6long = $bin.$ipv6long;
        } else {
            $ipv6long = $bin;
        }
        --$bits;
    }
    $ipv6long = gmp_strval(gmp_init($ipv6long, 2), 10);

    return $ipv6long;
}

/**
 * Paginate a Laravel collection, used for service only as
 * it DOES NOT return a true Pagination object (for rendering to view).
 *
 * @input Laravel Query Builder
 * @input callback function
 *
 * @return collection result set
 */
function paginate($builder, $callback, $params = [])
{
    $default = [
        'limit' => 10000,
    ];

    $params = array_merge($default, $params);

    // in some cases, use the pre-computed count rather than counting against the builder
    if (!array_key_exists('count', $params)) {
        $params['count'] = $builder->count();
    }

    // count the number of pages needed
    $pages = ceil($params['count'] / $params['limit']);

    for ($page = 1; $page <= $pages; $page += 1) {
        $offset = $params['limit'] * ($page - 1);

        $builder = $builder->skip($offset)->take($params['limit']);
        $callback($builder, $page);
    }
}

/**
 * Overwrite the Laravel's Builder#paginate, accept a $total parameter specifying the total number of records.
 *
 * @param int      $perPage
 * @param array    $columns
 * @param string   $pageName
 * @param int|null $page
 *
 * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
 */
function optimized_paginate($builder, $perPage = 15, $columns = null, $pageName = null, $page = null, $total = null)
{
    $pageName = $pageName ?: 'page';
    $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
    $columns = $columns ?: ['*'];
    $total = is_null($total) ? $builder->getCountForPagination() : $total;
    // in case $total == 0
    $results = $total ? $builder->forPage($page, $perPage)->get($columns) : collect([]);

    return new \Illuminate\Pagination\LengthAwarePaginator($results, $total, $perPage, $page, [
    'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
    'pageName' => $pageName,
  ]);
}

/**
 * Distinct count helper for performance.
 *
 * @return int
 */
function distinctCount($builder, $column = null, $method = 'group')
{
    $q = clone $builder;
    /*
     * There are 2 options to COUNT DISTINCT
     *   1. Use DISTINCT
     *   2. Use GROUP BY
     * Normally GROUP BY yields better performance (for example: 500,000 records, DISTINCT -> 7 seconds, GROUP BY -> 1.9 seconds)
     **/

    if (is_null($column)) {
        // just count it
    } elseif ($method == 'group') {
        $q->groupBy($column)->select($column);
    } elseif ($method == 'distinct') {
        $q->select($column)->distinct();
    }

    // Result
    $count = \DB::table(\DB::raw("({$q->toSql()}) as sub"))
        ->addBinding($q->getBindings()) // you need to get underlying Query Builder
        ->count();

    return $count;
}

/**
 * Measure execution time of a script.
 *
 * @return float execution time
 */
function measure($callback, $tests = 5)
{
    $results = [];

    for ($i = 0; $i <= $tests; $i += 1) {
        $start = microtime(true);
        $callback();
        $time = microtime(true) - $start;
        $results[] = $time;
    }
    $agv = array_sum($results) / count($results);
    echo "$agv\n";

    return $agv;
}

/**
 * Check if function is enabled.
 *
 * @return bool
 */
function func_enabled($name)
{
    try {
        $disabled = explode(',', ini_get('disable_functions'));

        return !in_array($name, $disabled);
    } catch (\Exception $ex) {
        return false;
    }
}

/**
 * Get the current application version.
 *
 * @return string version
 */
function app_version()
{
    return trim(file_get_contents(base_path('VERSION')));
}

/**
 * Extract email from a string
 * For example: get abc@mail.com from "My Name <abc@mail.com>".
 *
 * @return string version
 */
function extract_email($str)
{
    preg_match("/(?<email>[-0-9a-zA-Z\.+_]+@[-0-9a-zA-Z\.+_]+\.[a-zA-Z]+)/", $str, $matched);
    if (array_key_exists('email', $matched)) {
        return $matched['email'];
    } else {
        return;
    }
}

/**
 * Extract name from a string
 * For example: get abc@mail.com from "My Name <abc@mail.com>".
 *
 * @return string version
 */
function extract_name($str)
{
    $parts = explode('<', $str);
    if (count($parts) > 1) {
        return trim($parts[0]);
    }
    $parts = explode('@', extract_email($str));

    return $parts[0];
}

/**
 * Extract domain from an email
 * For example: get mail.com from "My Name <abc@mail.com>".
 *
 * @return string version
 */
function extract_domain($email)
{
    $email = extract_email($email);
    $domain = substr(strrchr($email, '@'), 1);

    return $domain;
}

/**
 * Doublequote a string.
 *
 * @return string
 */
function doublequote($str)
{
    return sprintf('"%s"', preg_replace('/^"+|"+$/', '', $str));
}

function jsonGet($array, $path)
{
    $jsonObject = new \JsonPath\JsonObject($array);
    $result = $jsonObject->get($path)[0];

    return $result;
}

/**
 * Format price.
 *
 * @param string
 *
 * @return string
 */
function format_number($number)
{
    if (is_numeric($number) && floor($number) != $number) {
        return number_format($number, 2, trans('messages.dec_point'), trans('messages.thousands_sep'));
    } elseif (is_numeric($number)) {
        return number_format($number, 0, trans('messages.dec_point'), trans('messages.thousands_sep'));
    } else {
        return $number;
    }
}
/**
 * Format price.
 *
 * @param string
 *
 * @return string
 */
function format_price($price, $format = '{PRICE}')
{
    return str_replace('{PRICE}', format_number($price), $format);
}

/**
 * Check if the app is initiated.
 *
 * @return bool
 */
function isInitiated()
{
    //file_put_contents(storage_path('app/log.txt'),'initiate',FILE_APPEND);
    return file_exists(storage_path('app/installed'));
}

function formatSizeUnits($bytes)
{
    if ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2).' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2).' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2).' KB';
    } elseif ($bytes > 1) {
        $bytes = $bytes.' bytes';
    } elseif ($bytes == 1) {
        $bytes = $bytes.' byte';
    } else {
        $bytes = '0 bytes';
    }

    return $bytes;
}

/**
 * Get random item from array.
 *
 * @return object
 */
function rand_item($arr)
{
    return $arr[array_rand($arr)];
}

/**
 * Return date string.
 *
 * @return object
 */
function toDateString($datetime)
{
    return \Acelle\Library\Tool::dateTime($datetime)->format('Y-m-d');
}

/**
 * Return time string.
 *
 * @return object
 */
function toTimeString($datetime)
{
    return \Acelle\Library\Tool::dateTime($datetime)->format('h:i A');
}

/**
 * Check if string is email.
 *
 * @return object
 */
function checkEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function tinyDocTypeTransform($content)
{
    if (stripos(strtolower($content), '<!doctype') === false) {
        $doctype = '<!DoCtYPe html>';
    } else {
        $doctype = '';
    }

    return $doctype . $content;
}

function demo_auth()
{
    $auth = \Acelle\Model\User::getAuthenticateFromFile();

    return [
        'email' => isset($auth['email']) ? $auth['email'] : '',
        'password' => $auth['password'] ? $auth['password'] : '',
    ];
}

function get_app_identity()
{
    return md5(config('app.key'));
}

function quoteDotEnvValue($value)
{
    $containsSharp = (strpos($value, '#') !== false);

    if ($containsSharp) {
        $value = str_replace('"', '\"', $value);
        $value = '"'.$value.'"';
    }

    return $value;
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Strip Tags Only
 *
 * Just like strip_tags, but only removes the HTML tags specified and not all of
 * them.
 *
 * @param String $text The text to strip the tags from.
 * @param String|Array $allowedTags This can either be one tag (eg. 'p') or an
 *     array, (eg. ['p','br','h1']).
 * @return String The text with the mentioned tags stripped.
 * @author Aalaap Ghag <aalaap@gmail.com>
 */
function strip_tags_only($text, $allowedTags = [])
{
    if (!is_array($allowedTags)) {
        $allowedTags = [
            $allowedTags
        ];
    }
    
    array_map(
        function ($allowedTag) use (&$text) {
            $regEx = '#<' . $allowedTag . '.*?>(.*?)</' . $allowedTag . '>#is';
            $text = preg_replace($regEx, '', $text);
        },
        $allowedTags
    );
    
    return $text;
}

/**
 * Get controller action name
 **/
function controllerAction()
{
    // GET FROM SCREEN OPTION
    $controller = explode('\\', request()->route()->getAction()['controller']);
    return $controller[count($controller)-1];
}

/**
 * Get controller name
 **/
function controllerName()
{
    $controllerAction = controllerAction();
    return explode('@', $controllerAction)[0];
}

/**
 * Get action name
 **/
function actionName()
{
    $controllerAction = controllerAction();
    return explode('@', $controllerAction)[1];
}

/*
 *  Iterate through a Eloquent $query using cursor paginate
 *  The $orderBy parameter is critically required for a cursor pagination
 */
function cursorIterate($query, $orderBy, $size, $callback)
{
    $cursor = null;
    $page = 1;
    do {
        // The 4th parameter contains the offset cursor
        $list = $query->orderBy($orderBy)->cursorPaginate($size, ['*'], 'cursor', $cursor);
        $callback($list->items(), $page);
        $cursor = $list->nextCursor();
        $page += 1;
    } while ($list->hasMorePages());
}

function getEmailTemplate($uid){

    $template = \Acelle\Model\Email::findByUid($uid);
    //dd($template);
    return $template;
}

function getAutomations($id){
    $automation = Acelle\Model\Automation2::where('main_id',$id)->get();
    return $automation;
}

function getAutomationData($uid){
    $automation = Acelle\Model\Automation2::findByUid($uid);
    // var_dump($automation->data);
    // exit;
    $data = json_decode($automation->data,true);
    //dd($data);
    $child = 'trigger';
    $idArray = array();
    $newDataArray = array();
    foreach($data as $key => $newData){
        //echo $child;
        if(!in_array($child,$idArray) && $child != null){
            $search_path = searchForId($child, $data, array('$'));
            if($search_path != null){
                $idArray[] = $child;
                
                $newDataArray[$search_path['id']][] = $search_path;
                if($search_path['child'] != null){
                    $child = $search_path['child'];
                    
                }else if(array_key_exists('childYes', $search_path)){
                    $search_path_yes = searchForId($search_path['childYes'], $data, array('$'));
                    $search_path_no = searchForId($search_path['childNo'], $data, array('$'));
                    $newDataArray[$search_path['id']][] = $search_path_yes;
                    if($search_path_no != null){
                        $newDataArray[$search_path['id']][] = $search_path_no;
                    }
                    $childYes = $search_path_yes['child'];
                    if($search_path_no != null){
                        $childNo = $search_path_no['child'];
                    }else{
                        $childNo = null;
                    }
                    
                    for($i=0; $i<2;$i++){
                        //dd($child);
                        if($i==0){
                            $child = $childYes;
                        }else{

                            $child = $childNo;
                            //dd($child);
                        }
                            if(!in_array($child,$idArray) && $child != null){
                                $search_path = searchForId($child, $data, array('$'));
                                if($search_path != null){
                                    $idArray[] = $child;
                                    
                                    $newDataArray[$search_path['id']][] = $search_path;
                                    if($search_path['child'] != null){
                                        $child = $search_path['child'];
                                        
                                    }else if(array_key_exists('childYes', $search_path)){
                                        $search_path_yes = searchForId($search_path['childYes'], $data, array('$'));
                                        $search_path_no = searchForId($search_path['childNo'], $data, array('$'));
                                        $newDataArray[$search_path['id']][] = $search_path_yes;
                                        $newDataArray[$search_path['id']][] = $search_path_no;
                                        //for($i=0; $i<3;$i++){
                                            $child = $search_path_no['child'];
                                        //}
                                        
                                    }else{
                                        $child = null;
                                    } 
                                    
                                }
                            }
                        
                        
                    }
                    
                }else{
                    $child = null;
                } 
                
            }
        }          

    }
    //dd($newDataArray);
    return $newDataArray;
}
function searchForId($search_value, $array, $id_path) {
  
    // Iterating over main array
    foreach ($array as $key1 => $val1) {
        //var_dump($array);
        //dd($val1);
        // $temp_path = $id_path;
          
        // // Adding current key to search path
        // array_push($temp_path, $key1);
  
        // Check if this value is an array
        // with atleast one element
        
            if(is_array($val1) and count($val1)) {
  
                // Iterating over the nested array
                foreach ($val1 as $key2 => $val2) {
                    if($val1['id'] ==  $search_value){
                        return $val1;
                    }
                    //dd($val1);
                    // if($key1 == 1){
                    //     dd($search_value,$val2);
                    // }
                    // if($val2 == $search_value) {
                              
                    //     // Adding current key to search path
                    //     return $val1;
                    // }else{
                    //     return null;
                    // }
                }
            }
        
        
          
        // elseif($val1 == $search_value) {
        //     return $val1;
        // }
    }
      
    return null;
}


function getBounceData($main_id){
    $automation = Acelle\Model\Automation2::where('main_id',$main_id)->get();
    $totalBouncesDataA = 0;
        $totalBouncesUniqueDataA = 0;
        $totalBouncesDataB = 0;
        $totalBouncesUniqueDataB = 0;
        $totalBouncesUniquePercentageA =0;
        $totalBouncesUniquePercentageB = 0;
        foreach($automation as $key => $automations){
            if($key == 0){
                $totalBouncesDataA = $automations->bounceLogs();
                $totalBouncesUniqueDataA = $automations->bounceLogsUnique();
                if($totalBouncesDataA > 0){
                    $totalBouncesUniquePercentageA = cal_percentage($totalBouncesUniqueDataA,$totalBouncesDataA);
                }
                
            }else{
                $totalBouncesDataB = $automations->bounceLogs();
                $totalBouncesUniqueDataB = $automations->bounceLogsUnique();
                if($totalBouncesDataB > 0){
                    $totalBouncesUniquePercentageB = cal_percentage($totalBouncesUniqueDataB,$totalBouncesDataB);
                }
            }
            
        }
        $totalPercentage = $totalBouncesUniquePercentageB + $totalBouncesUniquePercentageA;
        return $totalPercentage;
}

 function cal_percentage($num_amount, $num_total) {
      $count1 = $num_amount / $num_total;
      $count2 = $count1 * 100;
      $count = number_format($count2, 0);
      return $count;
    }

function getAllBounceHandler(){
    $bounce = Acelle\Model\BounceHandler::all();
    return $bounce;
}

function checkBounceAdded(){
    $data = DB::table('bounce_handeler_notification')->where('user_id',Auth::user()->id)->where('status',0)->get();
    return $data;

}

function getOpenLogDetail($id){
    $logdetail = [];
    $logdetail = DB::table('subscriber_fields')->where('subscriber_id',$id)->orderBy('id','ASC')->get();
    if(!empty($logdetail)){
        return $logdetail;
    }else{
        return $logdetail;
    }
    //dd($id,$logdetail);
}
