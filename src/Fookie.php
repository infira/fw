<?php

namespace Infira\Fookie;

use Infira\Poesis\DbQueryHistory;
use Infira\Fookie\facade\Session;
use Infira\Fookie\request\Route;
use Infira\Fookie\request\Payload;
use Infira\Fookie\facade\Http;
use Infira\Fookie\facade\Cache;
use Infira\Poesis\Poesis;
use Path;
use App;

class Fookie
{
	private static $options = [];
	
	/**
	 * Displays the eRaama result
	 *
	 * @return null
	 */
	public static function init()
	{
		Autoloader::setPath('PoesisDataMethodsExtendor2', __DIR__ . '/traits/PoesisDataMethodsExtendor2.trait.php');
		Autoloader::init();
		
		Poesis::init();
		\Infira\Poesis\Autoloader::setDataGettersExtendorPath(Path::fookieTraits('PoesisDataMethodsExtendor.trait.php'));
		
		\AppConfig::finalize();
		
		Poesis::setDefaultConnection(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		Poesis::useInfiraErrorHadler();
		Route::init();
	}
	
	public static function beforeRouteBoot()
	{
		if (self::optExists('beforeRouteBoot'))
		{
			$c = self::opt('beforeRouteBoot');
			$c();
		}
	}
	
	public static function boot()
	{
		Route::detect();
		
		$sessionName                 = 'PHPSESSID';
		$differnetSessionForEachRole = true;
		if (Fookie::optExists('differnetSessionForEachRole'))
		{
			$differnetSessionForEachRole = Fookie::opt('differnetSessionForEachRole');
		}
		
		if ($differnetSessionForEachRole)
		{
			$sessionName = Route::getRole();
			if (Http::existsGET('_overrideSessionName'))
			{
				$sessionName = Http::getGET('_overrideSessionName');
			}
		}
		Session::init($sessionName);
		Cache::init();
		Cache::setDefaultDriver(\AppConfig::defaultCacheDriver());
		Payload::init();
		self::beforeRouteBoot();
		Route::boot();
		$payload = Payload::getOutput();
		if (Http::existsGET('showProfile'))
		{
			$payload .= '<pre></pre><div class="_profiler">';
			$payload .= Prof()->dumpTimers();
			$payload .= DbQueryHistory::getHTMLTable();
			$payload .= '</div></pre>';
		}
		
		self::closeConnections();
		
		return $payload;
	}
	
	public static function setDbInstallerController(string $controller)
	{
		self::$options['dbInstallerController'] = $controller;
	}
	
	public static function setCacheFlusherController(string $controller)
	{
		self::$options['cacheFlusherController'] = $controller;
	}
	
	public static function setOperationController(string $controller)
	{
		self::$options['operationController'] = $controller;
	}
	
	public static function setUseDiffernetSessionForEachRole(bool $bool)
	{
		self::$options['differnetSessionForEachRole'] = $bool;
	}
	
	public static function setBeforeRouteBoot(callable $callable)
	{
		self::$options['beforeRouteBoot'] = $callable;
	}
	
	public static function optExists(string $name)
	{
		return array_key_exists($name, self::$options);
	}
	
	/**
	 * @param string $name
	 * @param mixed  $value - if set value is not UNDEFINDED then its used to sed valie
	 * @return mixed|null
	 */
	public static function opt(string $name, $value = UNDEFINDED)
	{
		if ($value !== UNDEFINDED)
		{
			self::$options[$name] = $value;
		}
		
		return self::$options[$name];
	}
	
	
	private static function closeConnections()
	{
		return true;
	}
}

?>