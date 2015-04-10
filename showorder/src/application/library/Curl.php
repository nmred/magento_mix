<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 foldmethod=marker: */
// +---------------------------------------------------------------------------
// | SWAN [ $_SWANBR_SLOGAN_$ ]
// +---------------------------------------------------------------------------
// | Copyright $_SWANBR_COPYRIGHT_$
// +---------------------------------------------------------------------------
// | Version  $_SWANBR_VERSION_$
// +---------------------------------------------------------------------------
// | Licensed ( $_SWANBR_LICENSED_URL_$ )
// +---------------------------------------------------------------------------
// | $_SWANBR_WEB_DOMAIN_$
// +---------------------------------------------------------------------------
 
/**
+------------------------------------------------------------------------------
* Curl 
+------------------------------------------------------------------------------
* 
* @package 
* @version $_SWANBR_VERSION_$
* @copyright Copyleft
* @author $_SWANBR_AUTHOR_$ 
+------------------------------------------------------------------------------
*/
class Curl {
	// {{{ members
	
	private static $method = 'GET';
	private static $options= array();
	private static $httpHeader = array();
	private static $cookies = array();
	private static $urlSuffix = '';
	private static $data = '';
    private static $headerResponse = array();

	// }}}
	// {{{ functions
	// {{{ public static function setData()

	/**
	 * setData 
	 * 
	 * @param mixed $data 
	 * @param string $method 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function setData($data, $method = 'GET') {
		if (is_array($data)) {
			self::$method = strtolower($method);
			$queryStr = http_build_query($data);
			if (self::$method != 'post') {
				self::$urlSuffix = '?' . $queryStr;
			} else {
				self::$data = $queryStr;
			}
		} else {
			self::$method = strtolower($method);
            self::$data = $data; 
        }
	}

	// }}}
	// {{{ public static function setOptions()

	/**
	 * setOptions 
	 * 
	 * @param mixed $key 
	 * @param mixed $val 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function setOptions($key, $val = null) {
		if (is_array($key)) {
			self::$options = $key;
		} else if ($key && $val) {
			self::$options[$key] = $val;
		}
	}

	// }}}
	// {{{ public static function setHttpHeader()

	/**
	 * setHttpHeader 
	 * 
	 * @param mixed $key 
	 * @param mixed $val 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function setHttpHeader($key, $val = null) {
		if (is_array($key)) {
			self::$httpHeader = $key;
		} else if ($key && $val) {
			self::$httpHeader[$key] = $val;
		}
	}

	// }}}
	// {{{ public static function setCookie()

	/**
	 * setCookie 
	 * 
	 * @param mixed $key 
	 * @param mixed $val 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function setCookie($key, $val) {
		if ($key && $val) {
			$cookie = "{$key}=$val";
			self::$cookies[] = $cookie;
		}
	}

	// }}}
	// {{{ public function static setUserPWD()
	
	/**
	 * setUserPWD 
	 * 
	 * @param mixed $user 
	 * @param mixed $password 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function setUserPWD($user, $password) {
		self::$options[CURLOPT_USERPWD] = "{$user}:{$password}";
	}

	// }}}
	// {{{ public static function call()

	/**
	 * 调用接口 
	 * 
	 * @param mixed $urls 
	 * @static
	 * @access public
	 * @return void
	 */
	public static function call($urls) {
		if (!is_array($urls)) {
			$result = self::singleCurl($urls);
		} else {
			$result = self::multiCurl($urls);
		}

		// 清空 header 
		self::$httpHeader = array();
		return $result;
	}

	// }}}
	// {{{ private static function singleCurl()
	
	/**
	 * 单个接口调用方式 
	 * 
	 * @param string $url 
	 * @static
	 * @access private
	 * @return string
	 */
	private static function singleCurl($url) {
        self::$headerResponse = array();
		$curl = self::initCurl($url);
		$content = curl_exec($curl);
		$errno = curl_errno($curl);
		$error = curl_error($curl);
		if ($errno) {
			$content = '{"errno":"'. $errno .'", "error":"' . $error . '"}';
		}
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($content, 0, $headerSize);

        $content = substr($content, $headerSize);
        self::$headerResponse = self::parseHeader($header);
		self::writeCurlInfo(curl_getinfo($curl), $errno);
		curl_close($curl);
		return $content;
	}

	// }}}
	// {{{ private static function multiCurl()
	
	/**
	 * 批量请求(暂只支持GET请求) 
	 * 
	 * @param  array $urls 
	 * 支持以下两种方式调用：
	 * array(
	 * 	'http:://www.weibo.com' => array(
	 * 		CURLOPT_NOSIGNAL => false,
	 * 			.
	 * 			.
	 * 			.	
	 * 	);
	 * 	.
	 * 	.
	 * 	.
	 * );
	 * @return array
	 */
	private static function multiCurl($urls)
	{
		$curls = array();
		$res = array();
		$mCurl = curl_multi_init();

		foreach ($urls as $key => $url) {
			if (!isset($url['url'])) {
				continue;
			}
			if (isset($url['method']) && isset($url['params'])) {
				self::setData($url['params'], $url['method']);
			}

			if (isset($url['header'])) {
				self::setHttpHeader($url['header']);
			}
			
			$timeout = 1;
			if (isset($url['timeout']) && $url['timeout']) {
				$timeout = $url['timeout'];
			}

			if (defined('CURLOPT_TIMEOUT_MS')) {
				$timeoutMS = $timeout * 1000;
				self::setOptions(CURLOPT_NOSIGNAL, 1);
				self::setOptions(CURLOPT_CONNECTTIMEOUT_MS, $timeoutMS);
				self::setOptions(CURLOPT_TIMEOUT_MS, $timeoutMS);
			} else {
				if ($timeout < 1) $timeout = 1;
				self::setOptions(CURLOPT_CONNECTTIMEOUT, $timeout);
				self::setOptions(CURLOPT_TIMEOUT, $timeout);
			}

			$curls[$key] = self::_initCurl($url['url']);
			self::$httpHeader = array();
			curl_multi_add_handle($mCurl, $curls[$key]);
		}
		
		do {
			$mrc = curl_multi_exec($mCurl, $running);
		} while (CURLM_CALL_MULTI_PERFORM == $mrc);

		while ($running && $mrc == CURLM_OK) {
			if (curl_multi_select($mCurl) > -1) {
				do {
					$mrc = curl_multi_exec($mCurl, $running);
				} while (CURLM_CALL_MULTI_PERFORM == $mrc);
			}
		}

		foreach ($curls as $key => $ch) {
			$done = curl_multi_info_read($mCurl);
			$errno = $done['result'];
			if ($errno) {
				$content = '{"errno":"'. $errno .'", "error":"get contents timeout"}';
				$res[$key] = $content;
			} else {
				$res[$key] = curl_multi_getcontent($curls[$key]);
			}
			self::writeCurlInfo(curl_getinfo($ch), $errno);
			curl_multi_remove_handle($mCurl, $ch);           
			curl_close($ch);
		}	

		curl_multi_close($mCurl);
		return $res;
	}

	// }}}
	// {{{ private static function initCurl()

	/**
	 * 初始化 Curl 
	 * 
	 * @param string $url 
	 * @static
	 * @access private
	 * @return void
	 */
	private static function initCurl($url) {
		$urlScheme = parse_url($url, PHP_URL_SCHEME);
		if (false === strpos($url, '?')) {
			$url .= self::$urlSuffix;
		}
		$s = curl_init($url);
		if (self::$httpHeader) curl_setopt($s, CURLOPT_HTTPHEADER, self::$httpHeader);
		if ($urlScheme == 'https') {
			curl_setopt($s, CURLOPT_VERBOSE, 1);
			curl_setopt($s, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($s, CURLOPT_SSL_VERIFYPEER, false);
		}
		if (self::$method == 'post') {
			curl_setopt($s, CURLOPT_POST, true);
			curl_setopt($s, CURLOPT_POSTFIELDS, self::$data);
		}
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($s, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($s, CURLOPT_HEADER, true);

		if (self::$options) {
			foreach(self::$options as $key => $val) {
				if ($val) curl_setopt($s, $key, $val);
			}
		}		
		if (self::$cookies) {			
			$cookie = join(';', self::$_cookies);			
			curl_setopt($s, CURLOPT_COOKIE, $cookie);
			self::$cookies = array();
		}
		return $s;
	}

	// }}}
	// {{{ protected function writeCurlInfo()

	/**
	 * 记录调用接口的信息 
	 * 
	 * @param array $info 
	 * @param int $errno 
	 * @access protected
	 * @return void
	 */
	protected static function writeCurlInfo($info, $errno = 0)
	{
		if (empty($info)) {
			return false;
		}			

		$info['errno'] = $errno;

		// todo
	}

	// }}}
    // {{{ public static function run()

    /**
     * run 
     * 
     * @static
     * @access public
     * @return void
     */
    public static function run($url, $method = 'GET', $timeout = 1, $data = array(), $header = null, $isCookies=false) {
		self::setData($data, $method);
		self::setHttpHeader($header);
		if (defined('CURLOPT_TIMEOUT_MS')) {
			$timeoutMS = $timeout * 1000;
            self::setOptions(CURLOPT_NOSIGNAL, 1);
			self::setOptions(CURLOPT_CONNECTTIMEOUT_MS, $timeoutMS);
			self::setOptions(CURLOPT_TIMEOUT_MS, $timeoutMS);
		} else {
			if ($timeout < 1) $timeout = 1;
			self::setOptions(CURLOPT_CONNECTTIMEOUT, $timeout);
			self::setOptions(CURLOPT_TIMEOUT, $timeout);
		}
		
		if ($isCookies){//通过cookie方式调用
			$sue = urlencode(lib_request::cookie('SUE'));
			$sup = urlencode(lib_request::cookie('SUP'));
			if (!$sue) $sue = 'es%3Dc29810e526c9e3af6571a1195937e776%26ev%3Dv1%26es2%3D6a576c08eff155f923daea59ce9cfe73%26rs0%3DTvgYJnYkS5sPvKnsbmy6%252BibFVLnvnZy0dvKZUCCYQNrqaCiWZLhgGy1b9QhLa8aqob6ScecjRl7aMe08yWCpET2MlCBY6hClogoozyxxHXWX119iReSpK%252BaKRQaNWyMV7eFCqayPMucncZDlHUjzdy7TG8qOX84WCTW0zUhe3OA%253D%26rv%3D0';
			if (!$sup) $sup = 'cv%3D1%26bt%3D1383103780%26et%3D1383190180%26d%3Dc909%26i%3Dffc4%26us%3D1%26vf%3D0%26vt%3D0%26ac%3D0%26st%3D0%26uid%3D1644879743%26name%3Dbaojunbo%2540gmail.com%26nick%3D%25E4%25B8%25AD%25E5%259B%25BD%25E9%2583%25A8%25E9%2598%259F%26fmp%3D%26lcp%3D';
			self::setCookie('SUE', $sue);
			self::setCookie('SUP', $sup);
		}
		$content = self::call($url);
		return $content;
    
    }

    // }}}
    // {{{ public static function getResponseHeader()

    /**
     * 获取响应 Header  
     * 
     * @static
     * @access public
     * @return void
     */
    public static function getResponseHeader()
    {
        return self::$headerResponse; 
    }

    // }}}
    // {{{ private static function parseHeader()

    /**
     * 解析 header 头 
     * 
     * @param string $header 
     * @static
     * @access private
     * @return array
     */
    private static function parseHeader($header)
    {
        $headers = array();
        foreach (explode("\n", $header) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                $headers[$h[0]] = trim($h[1]);
            }
        }

        return $headers;      
    }

    // }}}
	// }}}
}
