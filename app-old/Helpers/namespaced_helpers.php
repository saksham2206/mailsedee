<?php

namespace Acelle\Helpers;

use Acelle\Library\StringHelper;
use Exception;

function generatePublicPath($absPath, $absolute = false)
{
    // Notice: $relativePath must be relative to storage/ folder
    // For example, with a real path of /home/deploy/acellemail/storage/app/sub/example.png
    // then $relativePath should be "app/sub/example.png"

    $excludeBase = storage_path();
    $pos = strpos($absPath, $excludeBase); // Expect pos to be exactly 0

    if ($pos === false) {
        throw new Exception(sprintf("File '%s' cannot be made public", $absPath));
    }

    if ($pos != 0) {
        throw new Exception(sprintf("Invalid path '%s', cannot make it public", $absPath));
    }

    // Do not use string replace, as path parts may occur more than once
    // For example: abc/xyz/abc/xyz...
    $relativePath = substr($absPath, strlen($excludeBase));
    $encodedUrl = StringHelper::base64UrlEncode($relativePath);

    // If Laravel is under a subdirectory
    $subdirectory = getAppSubdirectory();

    if (empty($subdirectory) || $absolute) {
        // Return something like
        //     "http://localhost/{subdirectory if any}/p/assets/ef99238abc92f43e038efb"   # absolute = true, OR
        //     "/p/assets/ef99238abc92f43e038efb"                   # absolute = false
        $url = route('public_assets', [ 'path' => $encodedUrl ], $absolute);
    } else {
        $url = join_paths($subdirectory, route('public_assets', [ 'path' => $encodedUrl ], $absolute));
    }

    return $url;
}


function getAppSubdirectory()
{
    // IMPORTANT: do not use url('/') as it will not work correctly
    // when calling from another file (like filemanager/config/config.php for example)
    // Otherwise, it will always return 'http://localhost' --> without subdirectory
    $appUrlInfo = parse_url(config('app.url'));
    $subdirectory = array_key_exists('path', $appUrlInfo) ? $appUrlInfo['path'] : null;
    return $subdirectory;
}
