<?php
    return [
        // +----------------------------------------------------------------------
        // | 应用设置
        // +----------------------------------------------------------------------

        // 应用命名空间
        'app_namespace'          => 'app',
        // 应用调试模式
        'app_debug'              => true,
        // 应用Trace
        'app_trace'              => false,
        // 是否支持多模块
        'app_multi_module'       => true,
        // 注册的根命名空间
        'root_namespace'         => [],
        // 扩展函数文件
        'extra_file_list'        => [APP_ROOT . 'helper' . EXT, APP_ROOT . 'function' . EXT],
        // 默认输出类型
        'default_return_type'    => 'html',
        // 默认AJAX 数据返回格式,可选json xml ...
        'default_ajax_return'    => 'json',
        // 默认JSONP格式返回的处理方法
        'default_jsonp_handler'  => 'jsonpReturn',
        // 默认JSONP处理方法
        'var_jsonp_handler'      => 'callback',
        // 默认时区
        'default_timezone'       => 'PRC',
        // 默认全局过滤方法 用逗号分隔多个
        'default_filter'         => '',
        // 应用类库后缀
        'class_suffix'           => false,
        // 控制器类后缀
        'controller_suffix'      => false,

        // +----------------------------------------------------------------------
        // | 模块设置
        // +----------------------------------------------------------------------

        // 默认模块名
        'default_module'         => 'index',
        // 禁止访问模块
        'deny_module_list'       => ['common'],
        // 默认控制器名
        'default_controller'     => 'Index',
        // 默认操作名
        'default_action'         => 'index',
        // 默认的空控制器名
        'empty_controller'       => 'Error',
        // 操作方法前缀
        'use_action_prefix'      => false,
        // 操作方法后缀
        'action_suffix'          => '',

        // +----------------------------------------------------------------------
        // | URL设置
        // +----------------------------------------------------------------------

        // PATHINFO变量名 用于兼容模式
        'var_pathinfo'           => 's',
        // 兼容PATH_INFO获取
        'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
        // pathinfo分隔符
        'pathinfo_depr'          => '/',
        // URL伪静态后缀
        'url_html_suffix'        => 'html',
        // URL普通方式参数 用于自动生成
        'url_common_param'       => false,
        // 域名根，如thinkphp.cn
        'url_domain_root'        => '',
        // 默认的访问控制器层
        'url_controller_layer'   => 'controller',
        // 表单请求类型伪装变量
        'var_method'             => '_method',
        // 表单ajax伪装变量
        'var_ajax'               => '_ajax',
        // 表单pjax伪装变量
        'var_pjax'               => '_pjax',

        // +----------------------------------------------------------------------
        // | 模板设置
        // +----------------------------------------------------------------------

        'template'               => [
            // 视图基础目录，配置目录为所有模块的视图起始目录
            'template_dir'    => APP_ROOT,
            // 当前模板的视图目录 留空为自动获取
            'compile_dir'     => TEMP_ROOT . DS . 'html',
            'compile_check'   => true,
            'config_dir'      => '',
            'cache_dir'       => CACHE_ROOT . DS . 'html',
            'plugins_dir'     => '',
            // 模板引擎普通标签开始标记
            'left_delimiter'  => '<%',
            // 模板引擎普通标签结束标记
            'right_delimiter' => '%>',
        ],

        // +----------------------------------------------------------------------
        // | 异常及错误设置
        // +----------------------------------------------------------------------

        // 异常页面的模板文件
        //'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

        // 错误显示信息,非调试模式有效
        'error_message'          => '页面错误！请稍后再试～',
        // 显示错误信息
        'show_error_msg'         => false,
        // 异常处理handle类 留空使用 \think\exception\Handle
        'exception_handle'       => '',

        // +----------------------------------------------------------------------
        // | 日志设置
        // +----------------------------------------------------------------------

        'log'                    => [
            // 日志记录方式，内置 file socket 支持扩展
            // 'type'  => 'File',
            'type'  => 'File',
            // 日志保存目录
            'path'  => LOG_ROOT,
            // 日志记录级别
            'level' => [],
        ],

        // +----------------------------------------------------------------------
        // | 缓存设置
        // +----------------------------------------------------------------------

        'cache'                  => [
            // 驱动方式
            'type'   => 'Redis',
            //主机地址
            'host' => '127.0.0.1',
            //端口
            'port' => '6379',
            // 缓存前缀
            'prefix' => '',
            // 缓存有效期 0表示永久缓存
            'expire' => 0,
        ],

        // +----------------------------------------------------------------------
        // | 会话设置
        // +----------------------------------------------------------------------

        'session'                => [
            'id'             => '',
            // SESSION_ID的提交变量,解决flash上传跨域
            'var_session_id' => '',
            // SESSION 前缀
            'prefix'         => 'think',
            // 驱动方式 支持redis memcache memcached
            'type'           => '',
            // 是否自动开启 SESSION
            'auto_start'     => true,
            'httponly'       => true,
            'secure'         => false,
        ],

        // +----------------------------------------------------------------------
        // | Cookie设置
        // +----------------------------------------------------------------------
        'cookie'                 => [
            // cookie 名称前缀
            'prefix'    => '',
            // cookie 保存时间
            'expire'    => 0,
            // cookie 保存路径
            'path'      => '/',
            // cookie 有效域名
            'domain'    => '',
            //  cookie 启用安全传输
            'secure'    => false,
            // httponly设置
            'httponly'  => '',
            // 是否使用 setcookie
            'setcookie' => true,
        ],

        // +----------------------------------------------------------------------
        // | Upload设置
        // +----------------------------------------------------------------------
        'upload' => [
            'basepath' => UPLOAD_ROOT,
            'size' => '5M'
        ],
        
        // +----------------------------------------------------------------------
        // | Email设置 
        // +----------------------------------------------------------------------
        'email' => [
        
        ],

        // +----------------------------------------------------------------------
        // | 短信设置 
        // +----------------------------------------------------------------------
        'mms' => [
            'type'   => 'dayu',
            'key'    => '23512313',
            'secret' => 'd455f083cf2ffb46937eeaa70196e0e2',
            'sign'   => '注册验证',
            'template_id' => 'SMS_13385110'
        ],
        //微信小程序配置
        'wxopen' => [
            'appid' => 'wx7e96d9b14ad0548d',
            'secret' => 'c105a48df1bb31ade210a3f334912f7f' 
        ],
		'encryption_key' => 'eub_2014',
        'server_hostname' => 'vmdell02', 
		//memcache config
		'memcache_host' => 'dbmaster',
		'memcache_port' => '11211',

		'upload_limitsize' => 2048000,
		'upload_maxsize' => 1024000000,
		'upload_invalidfile' => 'php|php3|php5|html|js|phtml|inc|cgi|bak|asp|jsp|DLL|dll',
		'err_upload_invalid' => -100,
		'err_upload_maxsize' => -200,
		'err_upload_invalidfile' => -300,
		'err_upload_emptyfile' => -400,
		'err_upload' => -500,
		'upload_validfile' => 'jpg|jpeg|png|gif|doc|txt|docx|xml|zip|csv|xlsx',
        'appid'=>'wx7e96d9b14ad0548d',
        'secret' => 'c105a48df1bb31ade210a3f334912f7f',

        // 小程序支付
        'wx_app_id' => 'wx7e96d9b14ad0548d',
        'wx_pay_mch_id' => '1487822602',
        'wx_pay_mch_Key' => '46c0ce9e81ac2f10acde3f3196d5ae10',
        'wx_notify_url' => 'https://'.$_SERVER['HTTP_HOST'].'/Api/Pay/notify',
    ];
