<?php
namespace reading;

/**
 * Class: Config
 *
 * @author Jerry Shen <haifei.shen@eub-inc.com>
 * @version 2017-11-30
 */
abstract class Config
{

	/**
	 * List of all loaded config values
	 *
	 * @public array
	 */
	static private $_config = [];

	/**
     * 加载配置文件（PHP格式）
	 * @author Jerry Shen <haifei.shen@eub-inc.com>
	 * @version 2017-10-13
	 *
	 * @param mixed $file
	 * @return void
	 */
	public static function init($file)
	{
        self::$_config = include($file);
        
		// Set the base_url automatically if none was provided
		if (self::$_config['base_url'] == '') {
			if (isset($_SERVER['HTTP_HOST'])) {
				$base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
				$base_url .= '://'. $_SERVER['HTTP_HOST'];
				$base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
			} else {
				$base_url = 'http://localhost/';
			}
			self::set('base_url', $base_url);
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Fetch a config file item
	 *
	 *
	 * @access	public
	 * @param	string	the config item name
	 * @param	string	the index name
	 * @param	bool
	 * @return	string
	 */
    public static function get($item, $index = '')
	{
		$item = strtolower($item);
		$index = strtolower($index);
		if ('' == $index) {
			if ( !isset(self::$_config[$item])) {
				return FALSE;
			}
			$pref = self::$_config[$item];
		} else {
			if ( !isset(self::$_config[$index])) {
				return FALSE;
			}

			if ( !isset(self::$_config[$index][$item])) {
				return FALSE;
			}

			$pref = self::$_config[$index][$item];
		}

		return $pref;
	}

	// -------------------------------------------------------------

	/**
	 * Set a config file item
	 *
	 * @access	public
	 * @param	string	the config item key
	 * @param	string	the config item value
	 * @return	void
	 */
	public static function set($item, $value)
	{
		$item = strtolower($item);
		self::$_config[$item] = $value;
	}

    /**
     * 加载配置文件（PHP格式）
     * @param string    $file 配置文件名
     * @return void 
     */
    public static function load($file)
    {
        if (is_file($file) && file_exists($file)) {
            self::$_config = array_merge(self::$_config, include($file));
        }
        return self::$_config;
    }

    /**
     * get all config item 
     * @author Jerry Shen <haifei.shen@eub-inc.com>
     * @version 2017-09-20
     *
     * @return void
     */
    public static function all()
    {
        return self::$_config;
    }
}
