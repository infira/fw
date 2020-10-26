<?php

namespace Infira\Fookie;

use Infira\Poesis\DbQueryHistory;
use Infira\Fookie\facade\Session;
use Infira\Fookie\request\Route;
use Infira\Fookie\request\Payload;
use Infira\Fookie\facade\Http;
use Infira\Fookie\facade\Cache;

class Fookie
{
	/**
	 * Displays the eRaama result
	 *
	 * @return null
	 */
	public static function boot()
	{
		Cache::init();
		Session::init();
		Route::init();
		Payload::init();
		Route::handle();
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
	
	
	private static function closeConnections()
	{
		return true;
	}
}

?>