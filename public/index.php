<?php
    define('ENVIRONMENT', isset($_SERVER['CUR_ENV']) ? $_SERVER['CUR_ENV'] : 'development');

    switch (ENVIRONMENT)
    {
        case 'development':
            error_reporting(-1);
            ini_set('display_errors', 1);
        break;
        case 'testing':
        case 'production':
            ini_set('display_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        break;

        default:
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'The application environment is not set correctly.';
            exit(1); // EXIT_ERROR
    }

    //别名设置
    define('SCRIPT_START_TIME', microtime(true));
    define('SCRIPT_START_MEM', memory_get_usage());
    define('EXT', '.php');
    define('CONF_EXT', '.php');
    define('DS',DIRECTORY_SEPARATOR);
    define('PS', PATH_SEPARATOR);
    defined('BS') || define('BS', dirname(dirname(__FILE__)) . DS);

    define('APP_ROOT', BS . 'app' . DS);
    //define('LIB_ROOT', BS . 'phpinc' . DS . 'lib');
    define('CORE_ROOT', BS . 'phpinc');
    //define('UTIL_ROOT', BS . 'phpinc' . DS . 'util');
    define('DATA_ROOT', BS . 'data');
    define('VENDOR_ROOT', BS . 'vendor');
    define('LOG_ROOT', DATA_ROOT . DS . 'log');
    define('CACHE_ROOT', DATA_ROOT . DS . 'cache');
    define('TEMP_ROOT', DATA_ROOT . DS . 'temp');
    define('UPLOAD_ROOT', DATA_ROOT . DS . 'upload');
    define('CONF_ROOT', APP_ROOT);


    define('CSS_PATH', '/public/static/css');
    define('JS_PATH', '/public/static/js');
    define('IMAGES_PATH', '/public/static/images');
    define('LIB_PATH', '/public/static/lib');
    define('FONTS_PATH', '/public/static/fonts');

    // 环境常量
    define('IS_CLI', PHP_SAPI == 'cli' ? true : false);
    define('IS_WIN', strpos(PHP_OS, 'WIN') !== false);
    //缓存路径
    define('CACHE_PATH', BS . 'data' . DS . 'cache');

    //系统设置
    mb_internal_encoding('UTF-8');

    //域名
    define('WEB_DOMAIN',isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'');
    
    // print_r($_SERVER);exit;
    //载入Loader类
    require_once(CORE_ROOT . DS . 'Loader.php');

    \reading\Loader::register();

    \reading\App::run()->send();
