<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
	exit;
}

if (!defined('GMP_MSW_FIRST')) {
    define('GMP_MSW_FIRST', 1);
}
if (!defined('GMP_LSW_FIRST')) {
    define('GMP_LSW_FIRST', 2);
}
if (!defined('GMP_BIG_ENDIAN')) {
    define('GMP_BIG_ENDIAN', 4);
}
if (!defined('GMP_LITTLE_ENDIAN')) {
    define('GMP_LITTLE_ENDIAN', 8);
}

function bbcs_check_favicon($root = BOTBLOCKER_SITE_ROOT)
{
    return file_exists($root . DIRECTORY_SEPARATOR . 'favicon.ico');
}

function bbcs_check_curl()
{
    return extension_loaded('curl');
}

function bbcs_check_zip()
{
    return extension_loaded('zip');
}

function bbcs_check_gd()
{
    return extension_loaded('gd');
}

function bbcs_check_gmp()
{
    return extension_loaded('gmp');
}

function bbcs_check_bcmath()
{
    return extension_loaded('bcmath');
}

function bbcs_check_mbstring()
{
    return extension_loaded('mbstring');
}

function bbcs_check_iconv()
{
    return extension_loaded('iconv');
}

function bbcs_check_xml()
{
    return extension_loaded('xml');
}

function bbcs_check_gd_func()
{
    return function_exists('imagecreatetruecolor');
}

function bbcs_check_json()
{
    return function_exists('json_encode');
}

function bbcs_prefly_check()
{
    return [
        'curl' => bbcs_check_curl(),
        'zip' => bbcs_check_zip(),
        'gd' => bbcs_check_gd(),
        'gmp' => bbcs_check_gmp(),
        'bcmath' => bbcs_check_bcmath(),
        'mbstring' => bbcs_check_mbstring(),
        'iconv' => bbcs_check_iconv(),
        'xml' => bbcs_check_xml(),
        'gd_func' => bbcs_check_gd_func(),
        'json' => bbcs_check_json()
    ];
}

/**
 * Check if the shell_exec function is available
 *
 * @return bool Whether the shell_exec function is available
 */
function bbcs_isShellExecAvailable()
{
    $functionExists = function_exists('shell_exec');
    $disabled_functions = explode(',', ini_get('disable_functions'));
    $functionEnabledInIni = !in_array('shell_exec', $disabled_functions);
    if (function_exists('shell_exec')) {
        $executionTest = shell_exec('echo test');
    }
    return $functionExists && $functionEnabledInIni && $executionTest === "test\n";
}
