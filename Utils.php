<?php
/**
 * 工具类
 */
class Utils 
{
	// 计时器
    public static $requestTime;
    private static $_times = array(
        'hour' => 3600000,
        'min'  => 60000,
        'sec'  => 1000,
    );
	
	// 日期
	const MINUTE = 60;
    const HOUR   = 3600;
    const DAY    = 86400;
    const WEEK   = 604800;      // 7 days
    const MONTH  = 2592000;     // 30 days
    const YEAR   = 31536000;    // 365 days
    const SQL_FORMAT = 'Y-m-d H:i:s';
    const SQL_NULL   = '0000-00-00 00:00:00';
	
	// 字符串
	public static $encoding = 'UTF-8'; // 默认字符串
	
	// Cli
	const STDIN  = 0;
    const STDOUT = 1;
    const STDERR = 2;
	
	// 环境
	const VAR_NULL   = 1;
    const VAR_BOOL   = 2;
    const VAR_INT    = 4;
    const VAR_FLOAT  = 8;
    const VAR_STRING = 16;
	
	// 图片
	const TOP_LEFT     = 'tl';
    const LEFT         = 'l';
    const BOTTOM_LEFT  = 'bl';
    const TOP          = 't';
    const CENTER       = 'c';
    const BOTTOM       = 'b';
    const TOP_RIGHT    = 'tr';
    const RIGHT        = 'r';
    const BOTTOM_RIGHT = 'bt';
	
	// Url
	/**
     * URL constants as defined in the PHP Manual under "Constants usable with http_build_url()".
     * @see http://us2.php.net/manual/en/http.constants.php#http.constants.url
     */
    const URL_REPLACE        = 1;
    const URL_JOIN_PATH      = 2;
    const URL_JOIN_QUERY     = 4;
    const URL_STRIP_USER     = 8;
    const URL_STRIP_PASS     = 16;
    const URL_STRIP_AUTH     = 32;
    const URL_STRIP_PORT     = 64;
    const URL_STRIP_PATH     = 128;
    const URL_STRIP_QUERY    = 256;
    const URL_STRIP_FRAGMENT = 512;
    const URL_STRIP_ALL      = 1024;

    const ARG_SEPARATOR = '&';

    const PORT_HTTP  = 80;
    const PORT_HTTPS = 443;
	
	/**
	 * 获取post数据
	 * @param string $var_name
	 * @return string false
	 */
	public static function postVar($var_name = NULL) 
	{
		if (!$var_name) {
			return self::daddslashes($_POST);
		}
		if(array_key_exists($var_name, $_POST)) {
			return self::daddslashes($_POST[$var_name]);
		}
		return false;
	}
	
	/**
	 * 取消反斜线
	 * @param string/array $data
	 * @param int $filter
	 * @return string $data
	 */
	public static function daddslashes($data, $filter = 0) 
	{
		if(!is_array($data) && !is_object($data)) {
			MAGIC_QUOTES_GPC && $data = stripslashes($data);
			return $filter ?htmlspecialchars(trim($data)) : $data;
		}
		foreach($data as $key => $value) {
			$data[$key] = self::daddslashes($value, $filter);
		}
		return $data;
	}

	/**
	 * 格式化打印变量
	 *
	 * @param $var
	 * @param bool $echo
	 * @param null $label
	 * @param bool $strict
	 *
	 * @return mixed|null|string
	 */
	public static function dump($var, $echo=true, $label=null, $strict=true) 
	{
	    $label = ($label === null) ? '' : rtrim($label) . ' ';
	    if (!$strict) {
	        if (ini_get('html_errors')) {
	            $output = print_r($var, true);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        } else {
	            $output = $label . print_r($var, true);
	        }
	    } else {
	        ob_start();
	        var_dump($var);
	        $output = ob_get_clean();
	        if (!extension_loaded('xdebug')) {
	            $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
	            $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES) . '</pre>';
	        }
	    }
	    if ($echo) {
	        echo($output);
	        return null;
	    }else
	        return $output;
	}
	
	/**
     * @param $url 内容url地址
     * @param $url $before 提示信息前面内容
     * @return mixed $ch 资源
     */
    public static function getContent($url, $before='') 
	{
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        echo $before.'the url: '.$url.' is OK';
        echo '<hr>';
        echo '<script type="text/javascript">var h = document.documentElement.scrollHeight || document.body.scrollHeight;
  window.scrollTo(h,h);</script>'; // 滚动条随屏幕往下移动
        ob_flush();
        flush();
        return curl_exec($ch);
    }


    /**
     * 功能：php多种方式完美实现下载远程图片保存到本地
     * 参数：文件url,保存文件名称，使用的下载方式
     * 当保存文件名称为空时则使用远程文件原来的名称
     * @param string $url 文件url路径
     * @param string $dir 文件保存路径
     * @param int $type 获取图片方式 0:readfile else:curl
     * @param string $filename 保存图片的新文件名称
     * @return string $filename;
     */
    public static function getImage($url, $dir='./', $type=0, $filename='')
	{
        if($url == '') {
            return false;
        }
        // 文件名
        if($filename == '') {
            $ext = strrchr($url,'.');
            if($ext != '.gif' && $ext != '.jpg'){ 
              return false;
            }
            $filename = time().rand(0,10000).$ext;
        }

        // 文件保存路径
        if(!is_dir($dir)) {
            mkdir($dir,'0755',true);
        }

        // 按类型获取文件 
        if($type) {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $img = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start(); 
            readfile($url);
            $img = ob_get_contents(); 
            ob_end_clean(); 
        }
        
        // 写入保存文件
        $fp2 = @fopen($dir.$filename,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        return $filename;
    }

	/**
	 * 获取IP地址
	 *
	 * @return string
	 */
	public static function getIp() 
	{
		static $ip = '';
		$ip = $_SERVER['REMOTE_ADDR'];
		if (isset($_SERVER['HTTP_CDN_SRC_IP'])) {
			$ip = $_SERVER['HTTP_CDN_SRC_IP'];
		} elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}
		return $ip;
	}

	/**
	 * 是否base64编码
	 *
	 * @param $str
	 *
	 * @return bool
	 */
	public static function isBase64($str)
	{
		if(!is_string($str)){
			return false;
		}
		return $str == base64_encode(base64_decode($str));
	}

	/**
	 * 判断字符串是否存在字符串中
	 *
	 * @param $string
	 * @param $find
	 *
	 * @return bool
	 */
	public static function strExists($string, $find)
	{
		return !(strpos($string, $find) === FALSE);
	}
	
	//----------------------------------  变量  ------------------------------------
	
	/**
     * 将许多等同于真或假的英语单词转换成布尔。
     *
     * @param  string $string The string to convert to boolean
     * @return boolean
     *
     * @deprecated See JBZoo\Utils\Filter
     */
    public static function varsBool($string)
    {
        return self::filterBool($string);
    }

    /**
     * @param string $value
     * @param int    $round
     * @return float
     *
     * @deprecated See JBZoo\Utils\Filter
     */
    public static function varsFloat($value, $round = 10)
    {
        return self::filterFloat($value, $round);
    }

    /**
     * 智能转换任何字符串int
     *
     * @param string $value
     * @return int
     *
     * @deprecated See JBZoo\Utils\Filter
     */
    public static function varsInt($value)
    {
        return self::filterInt($value);
    }

    /**
     * 返回仅有数字的字符
     *
     * @param $value
     * @return mixed
     *
     * @deprecated See JBZoo\Utils\Filter
     */
    public static function varsDigits($value)
    {
        return self::filterDigits($value);
    }

    /**
     * 返回仅有alpha的字符
     *
     * @param $value
     * @return mixed
     *
     * @deprecated See JBZoo\Utils\Filter
     */
    public static function varsAlpha($value)
    {
        return self::filterAlpha($value);
    }

    /**
     * 返回仅有alpha和数字的字符
     *
     * @param $value
     * @return mixed
     *
     * @deprecated See JBZoo\Utils\Filter
     */
    public static function varsAlphaDigets($value)
    {
        return self::filterAlphanum($value);
    }

    /**
     * 验证邮箱
     *
     * @param $email
     * @return mixed
     *
     * @deprecated See JBZoo\Utils\Email
     */
    public static function varsEmail($email)
    {
        return self::filterEmail($email);
    }

    /**
     * 访问一个数组索引，检索存储在那里的值，如果它存在或默认情况下如果他不存在。
	 * 此功能允许您简要地访问一个索引，它可能或可能不存在，不提高一个警告。
     *
     * @param  array $var     Array value to access
     * @param  mixed $default Default value to return if the key is not
     * @return mixed
     */
    public static function varsGet(&$var, $default = null)
    {
        if (isset($var)) {
            return $var;
        }

        return $default;
    }

    /**
     * 如果数字在最小和最大值之内，则返回真值。
     *
     * @param int|float $number
     * @param int|float $min
     * @param int|float $max
     * @return bool
     */
    public static function varsIsIn($number, $min, $max)
    {
        return ($number >= $min && $number <= $max);
    }

    /**
     * 当前的值是否为偶数
     *
     * @param int $number
     * @return bool
     */
    public static function varsIsEven($number)
    {
        return ($number % 2 === 0);
    }

    /**
     * 当前值为负；小于零。
     *
     * @param int $number
     * @return bool
     */
    public static function varsIsNegative($number)
    {
        return ($number < 0);
    }

    /**
     * 当前的值是否为奇数
     *
     * @param int $number
     * @return bool
     */
    public static function varsIsOdd($number)
    {
        return !self::varIsEven($number);
    }

    /**
     * 当前的值为正的；大于或等于零。
     *
     * @param int  $number
     * @param bool $zero
     * @return bool
     */
    public static function varsIsPositive($number, $zero = true)
    {
        return ($zero ? ($number >= 0) : ($number > 0));
    }

    /**
     * 限制两个界限之间的数量。
     *
     * @param int $number
     * @param int $min
     * @param int $max
     * @return int
     */
    public static function varsLimit($number, $min, $max)
    {
        return self::varsMax(self::min($number, $min), $max);
    }

    /**
     * 如果低于阈值，将数字增加到最小值。
     *
     * @param int $number
     * @param int $min
     * @return int
     */
    public static function varsMin($number, $min)
    {
        if ($number < $min) {
            $number = $min;
        }
        return $number;
    }

    /**
     * 如果超过阈值，将数字降低到最大值。
     *
     * @param int $number
     * @param int $max
     * @return int
     */
    public static function varsMax($number, $max)
    {
        if ($number > $max) {
            $number = $max;
        }
        return $number;
    }

    /**
     * 如果数字在最小和最大值之外，则返回真值。
     *
     * @param int $number
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function varsOut($number, $min, $max)
    {
        return ($number < $min || $number > $max);
    }

    /**
     * 得到相对百分
     *
     * @param float $normal
     * @param float $current
     * @return string
     */
    public static function varsRelativePercent($normal, $current)
    {
        $normal  = (float)$normal;
        $current = (float)$current;

        if (!$normal || $normal == $current) {
            return '100';

        } else {
            $normal  = abs($normal);
            $percent = round($current / $normal * 100);

            return number_format($percent, 0, '.', ' ');
        }
    }
	
	//---------------------------------- 字符串 ------------------------------------
	
	
	/**
     * 将给定字符串中的空格、制表符、换页符等等替换成空
     *
     * @param  string $string The string to strip
     * @return string
     */
    public static function strStripSpace($string)
    {
        return preg_replace('/\s+/', '', $string);
    }
	
	/**
     * 按照线条解析文本
     *
     * @param string $text
     * @param bool   $toAssoc
     * @return array
     */
    public static function strParseLines($text, $toAssoc = true)
    {
        $text = htmlspecialchars_decode($text);
        $text = self::clean($text, false, false);

        $text  = str_replace(array("\n", "\r", "\r\n", PHP_EOL), "\n", $text);
        $lines = explode("\n", $text);

        $result = array();
        if (!empty($lines)) {
            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                if ($toAssoc) {
                    $result[$line] = $line;
                } else {
                    $result[] = $line;
                }
            }
        }

        return $result;
    }
	
	/**
     * 生成一个通用唯一标识符（UUID V4）根据RFC 4122
	 * 4版的UUID是伪随机！
     *
     * Returns Version 4 UUID format: xxxxxxxx-xxxx-4xxx-Yxxx-xxxxxxxxxxxx where x is
     * any random hex digit and Y is a random choice from 8, 9, a, or b.
     *
     * @see http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
     *
     * @return string
     */
    public static function strUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,
            // 48 bits for "node"
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
	
	/**
     * 获取没有命名空间的类名称
     *
     * @param mixed $object
     * @param bool  $toLower
     * @return mixed|string
     */
    public static function strGetClassName($object, $toLower = false)
    {
        if (is_object($object)) {
            $className = get_class($object);
        } else {
            $className = $object;
        }

        $result = $className;
        if (strpos($className, '\\') !== false) {
            $className = explode('\\', $className);
            reset($className);
            $result = end($className);
        }

        if ($toLower) {
            $result = strtolower($result);
        }

        return $result;
    }
	
	/**
     * 让字符串安全
     * - Remove UTF-8 chars
     * - Remove all tags
     * - Trim
     * - Addslashes (opt)
     * - To lower (opt)
     *
     * @param string $string
     * @param bool   $toLower
     * @param bool   $addslashes
     * @return string
     */
    public static function clean($string, $toLower = false, $addslashes = false)
    {
        $string = Slug::removeAccents($string);
        $string = strip_tags($string);
        $string = trim($string);

        if ($addslashes) {
            $string = addslashes($string);
        }

        if ($toLower) {
            $string = self::low($string);
        }

        return $string;
    }
	
	/**
     * 转换 >, <, ', " 和 & 到html实体, 但保留已编码的实体。
     *
     * @param string $string The text to be converted
     * @param bool   $encodedEntities
     * @return string
     */
    public static function strHtmlEnt($string, $encodedEntities = false)
    {
        if ($encodedEntities) {
            // @codeCoverageIgnoreStart
            if (defined('HHVM_VERSION')) {
                $transTable = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
            } else {
                /** @noinspection PhpMethodParametersCountMismatchInspection */
                $transTable = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES, self::$encoding);
            }
            // @codeCoverageIgnoreEnd

            $transTable[chr(38)] = '&';

            $regExp = '/&(?![A-Za-z]{0,4}\w{2,3};|#[0-9]{2,3};)/';

            return preg_replace($regExp, '&amp;', strtr($string, $transTable));
        }

        return htmlentities($string, ENT_QUOTES, self::$encoding);
    }
	
	/**
     * 逃离字符串在保存它作为XML内容之前
     *
     * @param $string
     * @return mixed
     */
    public static function strEscXml($string)
    {
        $string = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);

        $string = str_replace(
            array("&", "<", ">", '"', "'"),
            array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;"),
            $string
        );

        return $string;
    }
	
	/**
     * 规避 UTF-8 字符串
     *
     * @param string $string
     * @return string
     */
    public static function strEsc($string)
    {
        return htmlspecialchars($string, ENT_NOQUOTES, self::$encoding);
    }
	
	//---------------------------------- 序列化 ------------------------------------
	
	/**
     * 检查找到的值是否已经被序列化
     * 如果 $data 不是一个字符串，然后返回值将永远是假的。序列化的数据始终是一个字符串。
     *
     * @param  mixed $data Value to check to see if was serialized
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public static function serIs($data)
    {
        // If it isn't a string, it isn't serialized
        if (!is_string($data)) {
            return false;
        }

        $data = trim($data);

        // Is it the serialized NULL value?
        if ($data === 'N;') {
            return true;

        } elseif ($data === 'b:0;' || $data === 'b:1;') { // Is it a serialized boolean?
            return true;
        }

        $length = strlen($data);

        // Check some basic requirements of all serialized strings
        if (self::_serCheckBasic($data, $length)) {
            return false;
        }

        return @unserialize($data) !== false;
    }

    /**
     * 序列化数据, 如果需要的话。
     *
     * @param  mixed $data Data that might need to be serialized
     * @return mixed
     */
    public static function serMaybe($data)
    {
        if (is_array($data) || is_object($data)) {
            return serialize($data);
        }

        return $data;
    }

    /**
     * 反序列化值，如果它已经被序列化过了
     *
     * @param  string $data A variable that may or may not be serialized
     * @return mixed
     */
    public static function serMaybeUn($data)
    {
        // If it isn't a string, it isn't serialized
        if (!is_string($data)) {
            return $data;
        }

        $data = trim($data);

        // Is it the serialized NULL value?
        if ($data === 'N;') {
            return null;
        }

        $length = strlen($data);

        // Check some basic requirements of all serialized strings
        if (self::_serCheckBasic($data, $length)) {
            return $data;
        }

        // $data is the serialized false value
        if ($data === 'b:0;') {
            return false;
        }

        // Don't attempt to unserialize data that isn't serialized
        $uns = @unserialize($data);

        // Data failed to unserialize?
        if ($uns === false) {
            $uns = @unserialize(self::fix($data));

            if ($uns === false) {
                return $data;

            } else {
                return $uns;
            }

        } else {
            return $uns;
        }
    }

    /**
     * unserializes 部分损坏的阵列，时有发生。解决具体的` unserialize()：误差偏移量XXX YYY字节`误差。
     * 
     *
     * NOTE: 这个错误可以经常发生在不匹配的字符集和高于ASCII字符。
     * Contributed by Theodore R. Smith of PHP Experts, Inc. <http://www.phpexperts.pro/>
     *
     * @param  string $brokenSerializedData
     * @return string
     */
    public static function serFix($brokenSerializedData)
    {
        $fixdSerializedData = preg_replace_callback('!s:(\d+):"(.*?)";!', function ($matches) {
            $snip = $matches[2];
            return 's:' . strlen($snip) . ':"' . $snip . '";';
        }, $brokenSerializedData);

        return $fixdSerializedData;
    }

    /**
     * 检查所有的序列化字符串的一些基本要求
     *
     * @param string $data
     * @param int    $length
     * @return bool
     */
    protected static function _serCheckBasic($data, $length)
    {
        return $length < 4 || $data[1] !== ':' || ($data[$length - 1] !== ';' && $data[$length - 1] !== '}');
    }
	
	//----------------------------------- 计时器 -------------------------------------
	
	/**
     * 格式化过去时间作为字符串。
     *
     * @param  float $time
     * @return string
     */
    public static function timerFormat($time)
    {
        $time = round($time * 1000);
        foreach (self::$_times as $unit => $value) {
            if ($time >= $value) {
                $time = floor($time / $value * 100.0) / 100.0;
                return $time . ' ' . $unit . ($time == 1 ? '' : 's');
            }
        }

        return $time . ' ms';
    }

    /**
     * 格式化过去时间作为字符串。
     *
     * @param  float $time
     * @return string
     */
    public static function timerFormatMS($time)
    {
        $time = round($time * 1000, 3);
        $dec  = 3;

        if (!$time || $time >= 10 || $time >= 100) {
            $dec = 0;
        } elseif ($time < 10 && $time >= 0.1) {
            $dec = 1;
        } elseif ($time <= 0.01) {
            $dec = 3;
        }

        return number_format($time, $dec, '.', ' ') . ' ms';
    }

    /**
     * 格式化请求的开始以来所用的时间作为一个字符串。
     *
     * @return float
     */
    public static function timerTimeSinceStart()
    {
        return microtime(true) - self::timerGetRequestTime();
    }

    /**
     * 获取请求时间
     *
     * @return float
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function timerGetRequestTime()
    {
        return $_SERVER['REQUEST_TIME_FLOAT'];
    }
	
	//----------------------------------- 日期 -------------------------------------
	
	/**
     * 转换为时间戳
     *
     * @param string|DateTime $time
     * @param bool            $currentIsDefault
     * @return int
     */
    public static function dateToStamp($time = null, $currentIsDefault = true)
    {
        if ($time instanceof DateTime) {
            return $time->format('U');
        }

        if (!empty($time)) {
            if (is_numeric($time)) {
                $time = (int)$time;
            } else {
                $time = strtotime($time);
            }
        }

        if (!$time) {
            if ($currentIsDefault) {
                $time = time();
            } else {
                $time = 0;
            }
        }

        return $time;
    }
	
	/**
	 * 时间日期工厂
     * @param mixed $time
     * @param null  $timeZone
     * @return DateTime
     */
    public static function dateFactory($time = null, $timeZone = null)
    {
        $timeZone = self::dateTimezone($timeZone);

        if ($time instanceof DateTime) {
            return $time->setTimezone($timeZone);
        }

        $dateTime = new DateTime('@' . self::dateToStamp($time));
        $dateTime->setTimezone($timeZone);

        return $dateTime;
    }
	
	/**
     * 返回一个基于当前时区datetimezone对象。
     *
     * @param mixed $timezone
     * @return \DateTimeZone
     */
    public static function dateTimezone($timezone = null)
    {
        if ($timezone instanceof DateTimeZone) {
            return $timezone;
        }

        $timezone = $timezone ?: date_default_timezone_get();

        return new DateTimeZone($timezone);
    }
	
	/**
     * 检查字符串是否是日期
     *
     * @param string $date
     * @return bool
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public static function dateIs($date)
    {
        $time = strtotime($date);
        return $time > 0;
    }
	
	/**
     * 转换为SQL格式的时间
     *
     * @param null|int $time
     * @return string
     */
    public static function dateSql($time = null)
    {
        return self::dateFactory($time)->format(self::SQL_FORMAT);
    }
	
	/**
	 * 转换时间为人类可读模式
     * @param string|int $date
     * @param string     $format
     * @return string
     */
    public static function dateHuman($date, $format = 'd M Y H:i')
    {
        return self::dateFactory($date)->format($format);
    }

    /**
     * 判断时间是否在本周内
     *
     * @param string|int $time
     * @return bool
     */
    public static function dateIsThisWeek($time)
    {
        return (self::dateFactory($time)->format('W-Y') === self::dateFactory()->format('W-Y'));
    }

    /**
     * 判断时间是否在本月内
     *
     * @param string|int $time
     * @return bool
     */
    public static function dateIsThisMonth($time)
    {
        return (self::dateFactory($time)->format('m-Y') === self::dateFactory()->format('m-Y'));
    }

    /**
     * 判断时间是否在本年内
     *
     * @param string|int $time
     * @return bool
     */
    public static function dateIsThisYear($time)
    {
        return (self::dateFactory($time)->format('Y') === self::dateFactory()->format('Y'));
    }

    /**
     * 判断时间是否是明天
     *
     * @param string|int $time
     * @return bool
     */
    public static function dateIsTomorrow($time)
    {
        return (self::dageFactory($time)->format('Y-m-d') === self::dateFactory('tomorrow')->format('Y-m-d'));
    }

    /**
     * 判断时间是否是今天
     *
     * @param string|int $time
     * @return bool
     */
    public static function dateIsToday($time)
    {
        return (self::dateFactory($time)->format('Y-m-d') === self::dateFactory()->format('Y-m-d'));
    }

    /**
     * 判断时间是否是昨天
     *
     * @param string|int $time
     * @return bool
     */
    public static function dateIsYesterday($time)
    {
        return (self::DateFactory($time)->format('Y-m-d') === self::dateFactory('yesterday')->format('Y-m-d'));
    }
	
	//----------------------------------- Cli -------------------------------------
	
	/**
     * 判断当前环境是否是cli
     *
     * @return bool
     */
    public static function cliCheck()
    {
        return PHP_SAPI === 'cli' || defined('STDOUT');
    }
	
	/**
     * 打印行到标准输出
     *
     * @param string $message
     * @param bool   $addEol
     * @codeCoverageIgnore
     */
    public static function cliOut($message, $addEol = true)
    {
        if ($addEol) {
            $message .= PHP_EOL;
        }

        if (defined('STDOUT')) {
            fwrite(STDOUT, $message);
        } else {
            echo $message;
        }
    }

    /**
     * 打印行到标准错误
     *
     * @param string $message
     * @param bool   $addEol
     * @codeCoverageIgnore
     */
    public static function cliErr($message, $addEol = true)
    {
        if ($addEol) {
            $message .= PHP_EOL;
        }

        if (defined('STDERR')) {
            fwrite(STDERR, $message);
        } else {
            echo $message;
        }
    }

    /**
     * CLI命令执行
     *
     * @param string $command
     * @param array  $args
     * @param null   $cwd
     * @param bool   $verbose
     * @return string
     * @throws ProcessFailedException
     * @throws \Exception
     */
    public static function cliExec($command, $args = array(), $cwd = null, $verbose = false)
    {
        if (!class_exists('\Symfony\Component\Process\Process')) {
            throw new \Exception("Symfony/Process package required for Cli::exec() method"); // @codeCoverageIgnore
        }

        $cmd = self::cliBuild($command, $args);
        $cwd = $cwd ? $cwd = realpath($cwd) : null;

        //@codeCoverageIgnoreStart
        if ($verbose) {
            // Only in testing mode
            if (function_exists('\JBZoo\PHPUnit\cliMessage')) {
                \JBZoo\PHPUnit\cliMessage('Process: ' . $cmd);
                \JBZoo\PHPUnit\cliMessage('CWD: ' . $cwd);
            } else {
                Cli::cliOut('Process: ' . $cmd);
                Cli::cliOut('CWD: ' . $cwd);
            }

        }
        //@codeCoverageIgnoreEnd

        // execute command
        $process = new Process($cmd, $cwd);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * 为Cli选项建立参数
     *
     * @param string $command
     * @param array  $args
     * @return string
     */
    public static function cliBuild($command, $args = array())
    {
        $stringArgs  = array();
        $realCommand = $command;

        if (count($args) > 0) {
            foreach ($args as $key => $value) {
                $value = trim($value);
                $key   = trim($key);

                if (strpos($key, '-') !== 0) {
                    if (strlen($key) == 1) {
                        $key = '-' . $key;
                    } else {
                        $key = '--' . $key;
                    }
                }

                if (strlen($value) > 0) {
                    $stringArgs[] = $key . '="' . addcslashes($value, '"') . '"';
                } else {
                    $stringArgs[] = $key;
                }
            }
        }

        if (count($stringArgs)) {
            $realCommand = $command . ' ' . implode(' ', $stringArgs);
        }

        return $realCommand;
    }

    /**
     * 如果STDOUT支持彩色返回true。
     *
     * This code has been copied and adapted from
     * Symfony\Component\Console\Output\OutputStream.
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public static function cliHasColorSupport()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            $winColor = Env::get('ANSICON', Env::VAR_BOOL)
                || 'ON' === Env::get('ConEmuANSI')
                || 'xterm' === Env::get('TERM');

            return $winColor;
        }

        if (!defined('STDOUT')) {
            return false;
        }

        return self::isInteractive(STDOUT);
    }

    /**
     * 返回终端的列数。
     *
     * @return int
     * @codeCoverageIgnore
     */
    public static function cliGetNumberOfColumns()
    {
        if (DIRECTORY_SEPARATOR == '\\') {
            $columns = 80;

            if (preg_match('/^(\d+)x\d+ \(\d+x(\d+)\)$/', trim(getenv('ANSICON')), $matches)) {
                $columns = $matches[1];

            } elseif (function_exists('proc_open')) {
                $process = proc_open(
                    'mode CON',
                    array(
                        1 => array('pipe', 'w'),
                        2 => array('pipe', 'w'),
                    ),
                    $pipes,
                    null,
                    null,
                    array('suppress_errors' => true)
                );

                if (is_resource($process)) {
                    $info = stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($process);
                    if (preg_match('/--------+\r?\n.+?(\d+)\r?\n.+?(\d+)\r?\n/', $info, $matches)) {
                        $columns = $matches[2];
                    }
                }
            }

            return $columns - 1;
        }

        if (!self::cliIsInteractive(self::STDIN)) {
            return 80;
        }

        if (preg_match('#\d+ (\d+)#', shell_exec('stty size'), $match) === 1) {
            if ((int)$match[1] > 0) {
                return (int)$match[1];
            }
        }

        if (preg_match('#columns = (\d+);#', shell_exec('stty'), $match) === 1) {
            if ((int)$match[1] > 0) {
                return (int)$match[1];
            }
        }

        return 80;
    }

    /**
     * 返回，如果文件描述符是一个交互式终端或不。
     *
     * @param int|resource $fileDescriptor
     * @return bool
     */
    public static function cliIsInteractive($fileDescriptor = self::STDOUT)
    {
        return function_exists('posix_isatty') && @posix_isatty($fileDescriptor);
    }
	
	//----------------------------------- Email -------------------------------------
	
	/**
     * 检查如果电子邮件（S）是有效的。你可以发送一个或一个数组的电子邮件。
     *
     * @param string|array $emails
     * @return array
     */
    public static function emailCheck($emails)
    {
        $result = array();

        if (empty($emails)) {
            return $result;
        }

        $emails = self::_emailHandleEmailsInput($emails);

        foreach ($emails as $email) {
            if (!self::_isValid($email)) {
                continue;
            }
            if (!in_array($email, $result)) {
                $result[] = $email;
            }
        }

        return $result;
    }
	
	/**
     * 从电子邮件地址获取域。无效的电子邮件地址
	 *将被跳过。
     *
     * @param string|array $emails
     * @return array
     */
    public static function emailGetDomain($emails)
    {
        $result = array();

        if (empty($emails)) {
            return $result;
        }

        $emails = self::_emialHandleEmailsInput($emails);

        foreach ($emails as $email) {
            if (!self::_emailIsValid($email)) {
                continue;
            }

            $domain = self::_emailExtractDomain($email);
            if (!empty($domain) && !in_array($domain, $result)) {
                $result[] = $domain;
            }
        }

        return $result;
    }
	
	/**
     * 从电子邮件地址的按字母顺序排列。
     *
     * @param array $emails
     * @return array
     */
    public static function emailGetDomainSorted(array $emails)
    {
        $domains = self::getDomain($emails);

        if (count($domains) < 2) {
            return $domains;
        }

        sort($domains, SORT_STRING);

        return $domains;
    }
	
	/**
     * 生成一个个人全球统一标识URL
     *
     * Size of the image:
     * * The default size is 32px, and it can be anywhere between 1px up to 2048px.
     * * If requested any value above the allowed range, then the maximum is applied.
     * * If requested any value bellow the minimum, then the default is applied.
     *
     * Default image:
     * * It can be an URL to an image.
     * * Or one of built in options that Gravatar has. See Email::getGravatarBuiltInImages().
     * * If none is defined then a built in default is used. See Email::getGravatarBuiltInDefaultImage().
     *
     * @param string $email
     * @param int    $size
     * @param string $defaultImage
     * @return null|string
     * @link http://en.gravatar.com/site/implement/images/
     */
    public static function emailGetGravatarUrl($email, $size = 32, $defaultImage = 'identicon')
    {

        if (empty($email) || self::_emailIsValid($email) === false) {
            return null;
        }

        $hash = md5(strtolower(trim($email)));

        $parts = array('scheme' => 'http', 'host' => 'www.gravatar.com');
        if (Url::isHttps()) {
            $parts = array('scheme' => 'https', 'host' => 'secure.gravatar.com');
        }

        // Get size
        $size = Vars::limit(Filter::int($size), 32, 2048);

        // Prepare default images
        $defaultImage = trim($defaultImage);
        if (preg_match('/^(http|https)./', $defaultImage)) {
            $defaultImage = urldecode($defaultImage);

        } else {
            $defaultImage = strtolower($defaultImage);
            if (!(Arr::in((string)$defaultImage, self::emailGetGravatarBuiltInImages()))) {
                $defaultImage = self::emailGetGravatarBuiltInDefaultImage();
            }
        }

        // Build full url
        $parts['path']  = '/avatar/' . $hash . '/';
        $parts['query'] = array(
            's' => $size,
            'd' => $defaultImage,
        );

        $url = Url::create($parts);

        return $url;
    }
	
	/**
	 * 检查是否有效
     * @param string $email
     * @return bool
     */
    private static function _emailIsValid($email)
    {
        if (empty($email)) {
            return false;
        }

        $email = filter_var($email, FILTER_SANITIZE_STRING);

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        return true;
    }

    /**
     * @param string $email
     * @return string
     */
    private static function _emailExtractDomain($email)
    {
        $parts  = explode('@', $email);
        $domain = array_pop($parts);

        if (self::sysIsFunc('idn_to_ascii')) {
            return idn_to_ascii($domain);
        }

        return $domain;
    }
	
	//----------------------------------- 系统 -------------------------------------
	
	/**
     * 检查当前是否为windows系统
     *
     * @return bool
     */
    public static function sysIsWin()
    {
        return strncasecmp(PHP_OS, 'WIN', 3) === 0;
    }

    /**
     * 检查当前是否为root用户
     *
     * @return bool
     */
    public static function sysIsRoot()
    {
        if (self::sysIsFunc('posix_geteuid')) {
            return posix_geteuid() === 0;
        }

        return false; // @codeCoverageIgnore
    }
	
	/**
     * 返回当前用户的主目录。
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function sysGetHome()
    {
        if (self::arrayCheckIsExistsKey('HOMEDRIVE', $_SERVER)) {
            return $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
        }

        return $_SERVER['HOME'];
    }

    /**
     * ini_set function 的别名
     *
     * @param string $varName
     * @param string $newValue
     * @return mixed
     */
    public static function sysIniSet($varName, $newValue)
    {
        if (self::sysIsFunc('ini_set')) {
            return self::filterBool(ini_set($varName, $newValue));
        }

        return null; // @codeCoverageIgnore
    }

    /**
     * ini_get function 的别名
     *
     * @param string $varName
     * @return mixed
     */
    public static function sysIniGet($varName)
    {
        if (self::sysIsFunc('ini_get')) {
            return ini_get($varName);
        }

        return null; // @codeCoverageIgnore
    }

    /**
     * @param $funcName
     * @return bool
     */
    public static function sysIsFunc($funcName)
    {
        return is_callable($funcName) || (is_string($funcName) && function_exists($funcName) && is_callable($funcName));
    }
	
	/**
     * 设置PHP执行时间限制（在安全模式下不工作）
     *
     * @param int $newLimit
     */
    public static function sysSetTime($newLimit = 0)
    {
        $newLimit = (int)$newLimit;

        self::sysIniSet('set_time_limit', $newLimit);
        self::sysIniSet('max_execution_time', $newLimit);
        if (self::sysIsFunc('set_time_limit') && !ini_get('safe_mode')) {
            set_time_limit($newLimit);
        }
    }

    /**
     * 设置新的内存限制
     *
     * @param string $newLimit
     */
    public static function sysSetMemory($newLimit = '256M')
    {
        self::sysIniSet('memory_limit', $newLimit);
    }

    /**
	 * 判断PHP是否为传入的版本
     * @param string $version
     * @param string $current
     * @return bool
     */
    public static function sysIsPHP($version, $current = PHP_VERSION)
    {
        $version = trim($version, '.');
        return preg_match('#^' . preg_quote($version) . '#i', $current);
    }

    /**
	 * 判断PHP是否为5.3版本
     * @param string $current
     * @return bool
     */
    public static function sysIsPHP53($current = PHP_VERSION)
    {
        return self::sysIsPHP('5.3', $current);
    }

    /**
	 * 判断PHP是否为7版本
     * @param string $current
     * @return bool
     */
    public static function sysIsPHP7($current = PHP_VERSION)
    {
        return self::sysIsPHP('7', $current);
    }

    /**
     * 获取使用的内存
     *
     * @param bool $isPeak
     * @return string
     */
    public static function sysGetMemory($isPeak = true)
    {
        if ($isPeak) {
            $memory = memory_get_peak_usage(false);
        } else {
            $memory = memory_get_usage(false);
        }

        $result = self::fsFormat($memory, 2);

        return $result;
    }

    /**
     * 返回客户端的IP地址。
     *
     * @param   boolean $trustProxy Whether or not to trust the proxy headers HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR.
     *                              ONLY use if your server is behind a proxy that sets these values
     * @return  string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public static function sysGetClientIp($trustProxy = false)
    {
        if (!$trustProxy) {
            return $_SERVER['REMOTE_ADDR'];
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];

        } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
            $ipAddress = $_SERVER['HTTP_X_REAL_IP'];

        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];

        } else {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }

        return $ipAddress;
    }

    /**
     * 返回根目录
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @return string
     */
    public static function sysGetDocRoot()
    {
        $result = '.';
        if ($root = self::arrayCheckIsExistsKey('DOCUMENT_ROOT', $_SERVER, true)) {
            $result = $root;
        }

        $result = self::fsClean($result);
        $result = self::fsReal($result);

        if (!$result) {
            $result = self::fsReal('.'); // @codeCoverageIgnore
        }

        return $result;
    }

    /**
     * 当Xdebug支持或返回true或者运行时使用的是phpdbg（PHP > = 7）。返回true
     *
     * @return bool
     */
    public static function sysCanCollectCodeCoverage()
    {
        return self::sysHasXdebug() || self::sysHasPHPDBGCodeCoverage();
    }

    /**
     * @var string
     */
    private static $_binary;

    /**
     * 返回当前运行时的二进制路径。
	 * 追加，PHP的路径运行时hhvm。
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @codeCoverageIgnore
     */
    public static function sysGetBinary()
    {
        // HHVM
        if (self::$_binary === null && self::sysIsHHVM()) {
            if ((self::$_binary = getenv('PHP_BINARY')) === false) {
                self::$_binary = PHP_BINARY;
            }
            self::$_binary = escapeshellarg(self::$_binary) . ' --php';
        }

        // PHP >= 5.4.0
        if (self::$_binary === null && defined('PHP_BINARY')) {
            self::$_binary = escapeshellarg(PHP_BINARY);
        }

        // PHP < 5.4.0
        if (self::$_binary === null) {
            if (PHP_SAPI == 'cli' && isset($_SERVER['_'])) {
                if (strpos($_SERVER['_'], 'phpunit') !== false) {
                    $file = file($_SERVER['_']);

                    if (strpos($file[0], ' ') !== false) {
                        $tmp           = explode(' ', $file[0]);
                        self::$_binary = escapeshellarg(trim($tmp[1]));
                    } else {
                        self::$_binary = escapeshellarg(ltrim(trim($file[0]), '#!'));
                    }

                } elseif (strpos(basename($_SERVER['_']), 'php') !== false) {
                    self::$_binary = escapeshellarg($_SERVER['_']);
                }
            }
        }

        if (self::$_binary === null) {
            $binaryLocations = array(
                PHP_BINDIR . '/php',
                PHP_BINDIR . '/php-cli.exe',
                PHP_BINDIR . '/php.exe',
            );

            foreach ($binaryLocations as $binary) {
                if (is_readable($binary)) {
                    self::$_binary = escapeshellarg($binary);
                    break;
                }
            }
        }

        if (self::$_binary === null) {
            self::$_binary = 'php';
        }

        return self::$_binary;
    }

    /**
	 * 获取调试工具名称和版本
     * @return string
     */
    public static function sysGetNameWithVersion()
    {
        return self::sysGetName() . ' ' . self::sysGetVersion();
    }

    /**
	 * 获取调试工具名称
     * @return string
     */
    public static function sysGetName()
    {
        if (self::isHHVM()) {
            return 'HHVM';

        } elseif (self::isPHPDBG()) {
            return 'PHPDBG';
        }

        return 'PHP';
    }

    /**
	 * 获取调试工具官网
     * @return string
     */
    public static function sysGetVendorUrl()
    {
        if (self::isHHVM()) {
            return 'http://hhvm.com/';
        } else {
            return 'http://php.net/';
        }
    }

    /**
	 * 获取调试工具版本
     * @return string
     */
    public static function SysGetVersion()
    {
        if (self::isHHVM()) {
            return HHVM_VERSION;
        } else {
            return PHP_VERSION;
        }
    }

    /**
     * 返回true，当运行时使用的是PHP和加载了Xdebug。
     *
     * @return bool
     */
    public static function sysHasXdebug()
    {
        return (self::sysIsRealPHP() || self::sysIsHHVM()) && extension_loaded('xdebug');
    }

    /**
     * 返回true，当运行时使用的是hhvm。
     *
     * @return bool
     */
    public static function sysIsHHVM()
    {
        return defined('HHVM_VERSION');
    }

    /**
     * 返回true，当运行时使用的是没有加载phpdbg SAPI的PHP。
     *
     * @return bool
     */
    public static function sysIsRealPHP()
    {
        return !self::isHHVM() && !self::isPHPDBG();
    }

    /**
     * 返回true，当运行时使用的是已经加载phpdbg SAPI的PHP。
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public static function sysIsPHPDBG()
    {
        return PHP_SAPI === 'phpdbg' && !self::sysIsHHVM();
    }

    /**
     * 返回true，当运行时使用的是已加载phpdbg SAPI的PHP
	 * 和phpdbg_ * _oplog()功能可用（PHP > = 7）。
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public static function sysHasPHPDBGCodeCoverage()
    {
        return self::sysIsPHPDBG() && function_exists('phpdbg_start_oplog');
    }
	
	//----------------------------------- 环境 -------------------------------------
	
	/**
	 * 返回当前运行时的二进制路径。
	 * 追加，PHP的路径运行时hhvm。
     * @return string
     * @deprecated
     */
    public static function envGetBinary()
    {
        return self::sysGetBinary();
    }

    /**
	 * 获取调试工具名称和版本
     * @return string
     * @deprecated
     */
    public static function envGetNameWithVersion()
    {
        return self::sysGetNameWithVersion();
    }

    /**
	 * 获取调试工具名称
     * @return string
     * @deprecated
     */
    public static function envGetName()
    {
        return self::sysGetName();
    }

    /**
	 * 获取调试工具官网
     * @return string
     * @deprecated
     */
    public static function envGetVendorUrl()
    {
        return self::sysGetVendorUrl();
    }

    /**
	 * 获取调试工具版本
     * @return string
     * @deprecated
     */
    public static function envGetVersion()
    {
        return self::sysGetVersion();
    }

    /**
	 * 返回true，当运行时使用的是PHP和加载了Xdebug。
     * @return bool
     * @deprecated
     */
    public static function envHasXdebug()
    {
        return self::sysHasXdebug();
    }

    /**
	 * 返回true，当运行时使用的是hhvm。
     * @return bool
     * @deprecated
     */
    public static function envIsHHVM()
    {
        return self::sysIsHHVM();
    }

    /**
	 * 返回true，当运行时使用的是没有加载phpdbg SAPI的PHP。
     * @return bool
     * @deprecated
     */
    public static function envIsPHP()
    {
        return self::sysIsRealPHP();
    }

    /**
	 * 返回true，当运行时使用的是已经加载phpdbg SAPI的PHP。
     * @return bool
     * @deprecated
     */
    public static function envIsPHPDBG()
    {
        return self::sysIsPHPDBG();
    }

    /**
	 * 返回true，当运行时使用的是已加载phpdbg SAPI的PHP
	 * 和phpdbg_ * _oplog()功能可用（PHP > = 7）。
     * @return bool
     * @deprecated
     */
    public static function envHasPHPDBGCodeCoverage()
    {
        return self::sysHasPHPDBGCodeCoverage();
    }

    /**
     * 返回一个环境变量
     *
     * @param string $name
     * @param string $default
     * @param int    $options
     * @return mixed
     */
    public static function envGet($name, $default = null, $options = self::VAR_STRING)
    {
        $value = getenv(trim($name));

        if ($value === false) {
            return $default;
        }

        return self::envConvert($value, $options);
    }

    /**
     * 转换值的类型比如 "true", "false", "null" or "123".
     *
     * @param string $value
     * @param int    $options
     * @return mixed
     */
    public static function envConvert($value, $options = self::VAR_STRING)
    {
        $options = (int)$options;

        if ($options & self::VAR_STRING && !empty($value)) {
            return trim(self::filterStripQuotes($value));
        }

        if ($options & self::VAR_FLOAT) {
            return slef::filterFloat($value, 12);
        }

        if ($options & self::VAR_INT) {
            return self::filterInt($value);
        }

        if ($options & self::VAR_BOOL || $options & self::VAR_NULL) {
            if (null === $value || 'null' === strtolower(trim($value))) {
                return null;
            }

            return self::filterBool($value);
        }

        return (string)$value;
    }

	//----------------------------------- 数组 -------------------------------------
	
	/**
	 * 从数组中删除重复.
	 *
	 * @param array $array
	 * @param bool  $keepKeys
	 * @return array
	 */
	public static function arrayDeleteUnique($array, $keepKeys = false)
	{
		if ($keepKeys) {
			$array = array_unique($array);

		} else {
			// 这是比内置array_unique()更快的版本。
			// http://stackoverflow.com/questions/8321620/array-unique-vs-array-flip
			// http://php.net/manual/en/function.array-unique.php
			$array = array_keys(array_flip($array));
		}

		return $array;
	}

	/**
	 * 检查键是否存在于数组中
	 *
	 * @param string $key
	 * @param mixed  $array
	 * @param bool   $returnValue
	 * @return mixed
	 */
	public static function arrayCheckIsExistsKey($key, $array, $returnValue = false)
	{
		$isExists = array_key_exists((string)$key, (array)$array);

		if ($returnValue) {
			if ($isExists) {
				return $array[$key];
			}

			return null;
		}

		return $isExists;
	}

	/**
	 * 检查值是否存在于数组中
	 *
	 * @param string $value
	 * @param mixed  $array
	 * @param bool   $returnKey
	 * @return mixed
	 *
	 * @SuppressWarnings(PHPMD.ShortMethodName)
	 */
	public static function arrayCheckIsExistsValue($value, array $array, $returnKey = false)
	{
		$inArray = in_array($value, $array, true);

		if ($returnKey) {
			if ($inArray) {
				return array_search($value, $array, true);
			}

			return null;
		}

		return $inArray;
	}

	/**
	 * 返回数组中的第一个元素的值
	 *
	 * @param  array $array
	 * @return mixed
	 */
	public static function arrayReturnFirstValue(array $array)
	{
		return reset($array);
	}

	/**
	 * 返回数组中的最后一个元素的值
	 *
	 * @param  array $array
	 * @return mixed
	 */
	public static function arrayReturnLastValue(array $array)
	{
		return end($array);
	}

	/**
	 * 返回数组中的第一个元素的键
	 *
	 * @param  array $array
	 * @return int|string
	 */
	public static function arrayReturnFirstKey(array $array)
	{
		reset($array);
		return key($array);
	}

	/**
	 * 返回数组中的最后一个元素的键
	 *
	 * @param  array $array
	 * @return int|string
	 */
	public static function arrayReturnLastKey(array $array)
	{
		end($array);
		return key($array);
	}

	/**
	 * 自定义规则清除数组
	 *
	 * @param array $haystack
	 * @return array
	 */
	public static function arrayClean($haystack)
	{
		return array_filter($haystack);
	}

	/**
	 * 在序列化Json之前清除数组中无效的参数
	 *
	 * @param array $array
	 * @return array
	 */
	public static function arrayCleanBeforeJson(array $array)
	{
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = self::arrayCleanBeforeJson($array[$key]);
			}

			if ($array[$key] === '' || is_null($array[$key])) {
				unset($array[$key]);
			}
		}

		return $array;
	}

	/**
	 * 检查是否为关联数组
	 *
	 * @param $array
	 * @return bool
	 */
	public static function arrayIsTypeAssoc($array)
	{
		return array_keys($array) !== range(0, count($array) - 1);
	}
	
	/**
     * 通过字段获取值数组的值在数组或者对象中
     *
     * @param array  $arrayList
     * @param string $fieldName
     * @return array
     */
    public static function arrayGetField($arrayList, $fieldName = 'id')
    {
        $result = array();

        if (!empty($arrayList) && is_array($arrayList)) {
            foreach ($arrayList as $option) {
                if (is_array($option)) {
                    $result[] = $option[$fieldName];

                } elseif (is_object($option)) {
                    if (isset($option->{$fieldName})) {
                        $result[] = $option->{$fieldName};
                    }
                }
            }
        }

        return $result;
    }
	
	/**
     * 按另一个数组的键对一个数组进行排序
     *
     * @param array $array
     * @param array $orderArray
     * @return array
     */
    public static function arraySortByArray(array $array, array $orderArray)
    {
        return array_merge(array_flip($orderArray), $array);
    }
	
	/**
     * 给数组中的每个键增加前缀
     *
     * @param array  $array
     * @param string $prefix
     * @return array
     */
    public static function arrayAddEachKey(array $array, $prefix)
    {
        $result = array();

        foreach ($array as $key => $item) {
            $result[$prefix . $key] = $item;
        }

        return $result;
    }

    /**
     * 将关联数组转为注释风格的索引数组
     *
     * @param array $data
     * @return string
     */
    public static function arrayToComment(array $data)
    {
        $result = array();
        foreach ($data as $key => $value) {
            $result[] = $key . ': ' . $value . ';';
        }

        return implode(PHP_EOL, $result);
    }
	
	/**
     * 包含参数到一个数组中，如果已经是数组除外
     *
     * @example
     *   Arr.wrap(null)      # => []
     *   Arr.wrap([1, 2, 3]) # => [1, 2, 3]
     *   Arr.wrap(0)         # => [0]
     *
     * @param mixed $object
     * @return array
     */
    public static function arrayWrap($object)
    {
        if (is_null($object)) {
            return array();
        } elseif (is_array($object) && !self::arrayIsTypeAssoc($object)) {
            return $object;
        }

        return array($object);
    }
	
	//----------------------------------- 过滤器 -------------------------------------

	/**
     * 将自定义筛选器应用到变量
     *
     * @param mixed           $value
     * @param string|\Closure $filters
     * @return mixed
     * @throws Exception
     */
    public static function _filter($value, $filters = 'raw')
    {
        if (is_string($filters)) {
            $filters = Str::filterTrim($filters);
            $filters = explode(',', $filters);

            if (count($filters) > 0) {
                foreach ($filters as $filter) {
                    $filterName = self::filterCmd($filter);

                    if ($filterName) {
                        if (method_exists(__CLASS__, $filterName)) {
                            $value = self::$filterName($value);
                        } else {
                            throw new Exception('Undefined filter method: ' . $filter);
                        }
                    }
                }
            }

        } elseif ($filters instanceof \Closure) {
            $value = call_user_func($filters, $value);
        }

        return $value;
    }

    /**
     * 将许多等同于真或假的英语单词转换成布尔。
     *
     * @param  string $string The string to convert to boolean
     * @return boolean
     */
    public static function filterBool($string)
    {
        $yesList = array('affirmative', 'all right', 'aye', 'indubitably', 'most assuredly', 'ok', 'of course', 'oui',
            'okay', 'sure thing', 'y', 'yes', 'yea', 'yep', 'sure', 'yeah', 'true', 't', 'on', '1', 'vrai',
            'да', 'д', '+', '++', '+++', '++++', '+++++', '*');

        $noList = array('no*', 'no way', 'nope', 'nah', 'na', 'never', 'absolutely not', 'by no means', 'negative',
            'never ever', 'false', 'f', 'off', '0', 'non', 'faux', 'нет', 'н', 'немає', '-');

        $string = self::strLow($string);

        if (self::arrayIn($string, $yesList) || self::filterFloat($string) !== 0.0) {
            return true;

        } elseif (self::arrayIn($string, $noList)) {
            return false;
        }

        return filter_var($string, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param string $value
     * @param int    $round
     * @return float
     */
    public static function filterFloat($value, $round = 10)
    {
        $cleaned = preg_replace('#[^0-9eE\-\.\,]#ius', '', $value);
        $cleaned = str_replace(',', '.', $cleaned);

        preg_match('#[-+]?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?#', $cleaned, $matches);
        $result = isset($matches[0]) ? $matches[0] : 0.0;

        $result = round($result, $round);

        return (float)$result;
    }

    /**
     * 智能转换任何字符串int
     *
     * @param string $value
     * @return int
     */
    public static function filterInt($value)
    {
        $cleaned = preg_replace('#[^0-9-+.,]#', '', $value);

        preg_match('#[-+]?[0-9]+#', $cleaned, $matches);
        $result = isset($matches[0]) ? $matches[0] : 0;

        return (int)$result;
    }

    /**
     * 只返回数字(digits)的字符
     *
     * @param $value
     * @return mixed
     */
    public static function filterDigits($value)
    {
        // we need to remove - and + because they're allowed in the filter
        $cleaned = str_replace(array('-', '+'), '', $value);
        $cleaned = filter_var($cleaned, FILTER_SANITIZE_NUMBER_INT);

        return $cleaned;
    }

    /**
     * 只返回alpha字符
     *
     * @param $value
     * @return mixed
     */
    public static function filterAlpha($value)
    {
        return preg_replace('#[^[:alpha:]]#', '', $value);
    }

    /**
     * 只返回alpha和数字字符
     *
     * @param $value
     * @return mixed
     */
    public static function filterAlphanum($value)
    {
        return preg_replace('#[^[:alnum:]]#', '', $value);
    }

    /**
     * 只返回为base64字符
     *
     * @param $value
     * @return string
     */
    public static function filterBase64($value)
    {
        return (string)preg_replace('#[^A-Z0-9\/+=]#i', '', $value);
    }

    /**
     * 移除空格
     *
     * @param $value
     * @return string
     */
    public static function filterPath($value)
    {
        $pattern = '#^[A-Za-z0-9_\/-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$#';

        preg_match($pattern, $value, $matches);
        $result = isset($matches[0]) ? (string)$matches[0] : '';

        return $result;
    }

    /**
     * 删除两边空格
     *
     * @param $value
     * @return string
     */
    public static function filterTrim($value)
    {
        return self::strTrim($value, false);
    }

    /**
     * 清理数组
     *
     * @param mixed           $value
     * @param string|\Closure $filter
     * @return string
     */
    public static function filterArr($value, $filter = null)
    {
        $array = (array)$value;

        if ($filter === 'noempty') {
            $array = self::arrayClean($array);

        } elseif ($filter instanceof \Closure) {
            $array = array_filter($array, $filter); // TODO add support both - key + value
        }

        return $array;
    }

    /**
     * 清理系统命令
     *
     * @param array $value
     * @return string
     */
    public static function filterCmd($value)
    {
        $value = self::strLow($value);
        $value = preg_replace('#[^a-z0-9\_\-\.]#', '', $value);
        $value = self::strTrim($value);

        return $value;
    }

    /**
     * 验证email
     *
     * @param $email
     * @return mixed
     *
     * @deprecated See JBZoo\Utils\Email
     */
    public static function filterEmail($email)
    {
        $email = self::strTrim($email);
        $regex = chr(1) . '^[a-zA-Z0-9.!#$%&’*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$' . chr(1) . 'u';

        $cleaned = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (preg_match($regex, $email) && $cleaned) {
            return $cleaned;
        }

        return false;
    }

    /**
     * 获取安全字符串
     *
     * @param $string
     * @return mixed
     */
    public static function filterStrip($string)
    {
        $cleaned = strip_tags($string);
        $cleaned = self::strTrim($cleaned);

        return $cleaned;
    }

    /**
     * 获取安全字符串
     *
     * @param $string
     * @return mixed
     */
    public static function alias($string)
    {
        $cleaned = self::filterStrip($string);
        $cleaned = self::strSlug($cleaned);

        return $cleaned;
    }

    /**
     * 字符串转小写已经去除两边空格
     *
     * @param $string
     * @return string
     */
    public static function filterLow($string)
    {
        $cleaned = self::strLow($string);
        $cleaned = self::strTrim($cleaned);

        return $cleaned;
    }

    /**
     * 字符串转大写已经去除两边空格
     *
     * @param $string
     * @return string
     *
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public static function filterUp($string)
    {
        $cleaned = self::strUp($string);
        $cleaned = self::strTrim($cleaned);

        return $cleaned;
    }

    /**
     * 将给定字符串中的空格、制表符、换页符等等替换成空
     *
     * @param $string
     * @return string
     */
    public static function filterStripSpace($string)
    {
        return self::strStripSpace($string);
    }

    /**
	 * 字符串安全
     * @param $string
     * @return string
     */
    public static function filterClean($string)
    {
        return self::strClean($string, true, true);
    }

    /**
	 * 转换 >, <, ', " 和 & 到html实体, 但保留已编码的实体。
     * @param $string
     * @return string
     */
    public static function filterHtml($string)
    {
        return self::strHtmlEnt($string);
    }

    /**
	 * 逃离字符串在保存它作为XML内容之前
     * @param $string
     * @return string
     */
    public static function filterXml($string)
    {
        return self::strEscXml($string);
    }

    /**
	 * 规避utf-8字符串
     * @param $string
     * @return string
     */
    public static function filterEsc($string)
    {
        return self::strEsc($string);
    }

    /**
     * @param array|Data $data
     * @return Data
     */
    public static function filterData($data)
    {
        if ($data instanceof Data) {
            return $data;
        }

        return new JSON($data);
    }

    /**
     * RAW 占位符
     *
     * @param $string
     * @return mixed
     */
    public static function filterRaw($string)
    {
        return $string;
    }

    /**
     * 第一个字符转换成大写，其他小写
     *
     * @param $input
     * @return string
     */
    public static function filterUcfirst($input)
    {
        $string = self::strLow($input);
        $string = ucfirst($string);

        return $string;
    }

    /**
     * 解析行到关联列表
     *
     * @param $input
     * @return string
     */
    public static function filterParseLines($input)
    {
        if (is_array($input)) {
            $input = implode(PHP_EOL, $input);
        }

        return self::strParseLines($input, true);
    }

    /**
     * 把文字转换成PHP类名
     *
     * @param $input
     * @return string
     */
    public static function filterClassName($input)
    {
        $output = preg_replace(array('#(?<=[^A-Z\s])([A-Z\s])#i'), ' $0', $input);
        $output = explode(' ', $output);

        $output = array_map(function ($item) {
            $item = preg_replace('#[^a-z0-9]#i', '', $item);
            $item = str::filterUcfirst($item);
            return $item;
        }, $output);

        $output = array_filter($output);

        return implode('', $output);
    }

    /**
     * 去除引号
     *
     * @param string $value
     * @return string
     */
    public static function filterStripQuotes($value)
    {
        if ($value[0] === '"' && substr($value, -1) === '"') {
            $value = trim($value, '"');
        }

        if ($value[0] === "'" && substr($value, -1) === "'") {
            $value = trim($value, "'");
        }

        return $value;
    }
	
	
	//----------------------------------- Http -------------------------------------
	
	/**
     * 发送强制浏览器显示下载文件对话框的标题。
     * 跨浏览器兼容。只有防火墙，如果标题还没有被发送。
     *
     * @param string $filename The name of the filename to display to browsers
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    public static function httpDownload($filename)
    {
        if (!headers_sent()) {
            while (@ob_end_clean()) {
                // noop
            }

            // required for IE, otherwise Content-disposition is ignored
            if (self::sysIniGet('zlib.output_compression')) {
                self::sysIniSet('zlib.output_compression', 'Off');
            }

            self::sysSetTime(0);

            // Set headers
            header('Pragma: public');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Cache-Control: private', false);
            header('Content-Disposition: attachment; filename="' . basename(str_replace('"', '', $filename)) . '";');
            header('Content-Type: application/force-download');
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . filesize($filename));

            // output file
            if (self::sysIsFunc('fpassthru')) {
                $handle = fopen($filename, 'rb');
                fpassthru($handle);
                fclose($handle);

            } else {
                echo file_get_contents($filename);
            }

            return true;
        }

        return false;
    }

    /**
     * 设置标题，以防止不同浏览器的缓存。
     * 不同的浏览器支持不同的非缓存头, 
     * 因此，必须发送几头文件，以便所有的人都有一点，没有缓存应该发生
     *
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    public static function httpNocache()
    {
        if (!headers_sent()) {
            header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');

            return true;
        }

        return false;
    }

    /**
     * 发送UTF-8头内容如果标题没有被发送
     *
     * @param  string $content_type The content type to send out
     * @return boolean
     *
     * @codeCoverageIgnore
     */
    public static function httpUtf8($content_type = 'text/html')
    {
        if (!headers_sent()) {
            header('Content-type: ' . $content_type . '; charset=utf-8');

            return true;
        }

        return false;
    }

    /**
     * 获得所有HTTP头
     * @see https://github.com/symfony/http-foundation/blob/master/ServerBag.php
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function httpGetHeaders()
    {
        $headers = array();

        $contentHeaders = array('CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true);

        foreach ($_SERVER as $key => $value) {
            if (0 === strpos($key, 'HTTP_')) {
                $headers[substr($key, 5)] = $value;
            } elseif (isset($contentHeaders[$key])) { // CONTENT_* are not prefixed with HTTP_
                $headers[$key] = $value;
            }
        }

        if (isset($_SERVER['PHP_AUTH_USER'])) {
            $headers['PHP_AUTH_USER'] = $_SERVER['PHP_AUTH_USER'];
            $headers['PHP_AUTH_PW']   = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';

        } else {
            /*
             * php-cgi under Apache does not pass HTTP Basic user/pass to PHP by default
             * For this workaround to work, add these lines to your .htaccess file:
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             *
             * A sample .htaccess file:
             * RewriteEngine On
             * RewriteCond %{HTTP:Authorization} ^(.+)$
             * RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
             * RewriteCond %{REQUEST_FILENAME} !-f
             * RewriteRule ^(.*)$ app.php [QSA,L]
             */
            $authorizationHeader = null;
            if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];

            } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $authorizationHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            }

            if (null !== $authorizationHeader) {
                if (0 === stripos($authorizationHeader, 'basic ')) {
                    // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
                    $exploded = explode(':', base64_decode(substr($authorizationHeader, 6)), 2);
                    if (count($exploded) == 2) {
                        list($headers['PHP_AUTH_USER'], $headers['PHP_AUTH_PW']) = $exploded;
                    }

                } elseif (empty($_SERVER['PHP_AUTH_DIGEST']) && (0 === stripos($authorizationHeader, 'digest '))) {
                    // In some circumstances PHP_AUTH_DIGEST needs to be set
                    $headers['PHP_AUTH_DIGEST'] = $authorizationHeader;
                    $_SERVER['PHP_AUTH_DIGEST'] = $authorizationHeader;

                } elseif (0 === stripos($authorizationHeader, 'bearer ')) {
                    /*
                     * XXX: Since there is no PHP_AUTH_BEARER in PHP predefined variables,
                     *      I'll just set $headers['AUTHORIZATION'] here.
                     *      http://php.net/manual/en/reserved.variables.server.php
                     */
                    $headers['AUTHORIZATION'] = $authorizationHeader;
                }
            }
        }

        if (isset($headers['AUTHORIZATION'])) {
            return $headers;
        }

        // PHP_AUTH_USER/PHP_AUTH_PW
        if (isset($headers['PHP_AUTH_USER'])) {
            $authorization = 'Basic ' . base64_encode($headers['PHP_AUTH_USER'] . ':' . $headers['PHP_AUTH_PW']);

            $headers['AUTHORIZATION'] = $authorization;

        } elseif (isset($headers['PHP_AUTH_DIGEST'])) {
            $headers['AUTHORIZATION'] = $headers['PHP_AUTH_DIGEST'];
        }

        return $headers;
    }
	
	//----------------------------------- 文件操作 -------------------------------------
	
	/**
     * 作为一个好的字符串返回文件的权限，如rw-R -R -或假如果文件没有找到。
     *
     * @param   string $file  The name of the file to get permissions form
     * @param   int    $perms Numerical value of permissions to display as text.
     * @return  string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public static function fsPerms($file, $perms = null)
    {
        if (null === $perms) {
            if (!file_exists($file)) {
                return false;
            }

            $perms = fileperms($file);
        }

        //@codeCoverageIgnoreStart
        if (($perms & 0xC000) == 0xC000) { // Socket
            $info = 's';

        } elseif (($perms & 0xA000) == 0xA000) { // Symbolic Link
            $info = 'l';

        } elseif (($perms & 0x8000) == 0x8000) { // Regular
            $info = '-';

        } elseif (($perms & 0x6000) == 0x6000) { // Block special
            $info = 'b';

        } elseif (($perms & 0x4000) == 0x4000) { // Directory
            $info = 'd';

        } elseif (($perms & 0x2000) == 0x2000) { // Character special
            $info = 'c';

        } elseif (($perms & 0x1000) == 0x1000) { // FIFO pipe
            $info = 'p';

        } else { // Unknown
            $info = 'u';
        }
        //@codeCoverageIgnoreEnd

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-'));

        // All
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-'));

        return $info;
    }

    /**
     * 递归删除目录（和它的内容）。
     * Contributed by Askar (ARACOOL) <https://github.com/ARACOOOL>
     *
     * @param  string $dir              The directory to be deleted recursively
     * @param  bool   $traverseSymlinks Delete contents of symlinks recursively
     * @return bool
     * @throws \RuntimeException
     */
    public static function fsRmdir($dir, $traverseSymlinks = false)
    {
        if (!file_exists($dir)) {
            return true;

        } elseif (!is_dir($dir)) {
            throw new \RuntimeException('Given path is not a directory');
        }

        if (!is_link($dir) || $traverseSymlinks) {
            foreach (scandir($dir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }

                $currentPath = $dir . '/' . $file;

                if (is_dir($currentPath)) {
                    self::fsRmdir($currentPath, $traverseSymlinks);

                } elseif (!unlink($currentPath)) {
                    // @codeCoverageIgnoreStart
                    throw new \RuntimeException('Unable to delete ' . $currentPath);
                    // @codeCoverageIgnoreEnd
                }
            }
        }

        // @codeCoverageIgnoreStart
        // Windows treats removing directory symlinks identically to removing directories.
        if (is_link($dir) && !defined('PHP_WINDOWS_VERSION_MAJOR')) {
            if (!unlink($dir)) {
                throw new \RuntimeException('Unable to delete ' . $dir);
            }

        } else {
            if (!rmdir($dir)) {
                throw new \RuntimeException('Unable to delete ' . $dir);
            }
        }

        return true;
        // @codeCoverageIgnoreEnd
    }

    /**
     * 二进制安全打开文件
     *
     * @param $filepath
     * @return null|string
     */
    public static function fsOpenFile($filepath)
    {
        $contents = null;

        if ($realPath = realpath($filepath)) {
            $handle   = fopen($realPath, "rb");
            $contents = fread($handle, filesize($realPath));
            fclose($handle);
        }

        return $contents;
    }

    /**
     * 获取第一个文件行的最快的方法
     *
     * @param string $filepath
     * @return string
     */
    public static function fsFirstLine($filepath)
    {
        if (file_exists($filepath)) {
            $cacheRes  = fopen($filepath, 'r');
            $firstLine = fgets($cacheRes);
            fclose($cacheRes);

            return $firstLine;
        }

        return null;
    }

    /**
     * 将一个文件的可写性位设置为最小值，允许用户运行PHP写入它
     *
     * @param  string  $filename The filename to set the writable bit on
     * @param  boolean $writable Whether to make the file writable or not
     * @return boolean
     */
    public static function fsWritable($filename, $writable = true)
    {
        return self::_fsSetPerms($filename, $writable, 2);
    }

    /**
     * 将一个文件的可读性位设置为最小值, 允许用户可以读取他
     *
     * @param  string  $filename The filename to set the readable bit on
     * @param  boolean $readable Whether to make the file readable or not
     * @return boolean
     */
    public static function fsReadable($filename, $readable = true)
    {
        return self::_fsSetPerms($filename, $readable, 4);
    }

    /**
     * 将文件的可执行文件设置为最小值, 允许用户运行PHP读取它。
     *
     * @param  string  $filename   The filename to set the executable bit on
     * @param  boolean $executable Whether to make the file executable or not
     * @return boolean
     */
    public static function fsExecutable($filename, $executable = true)
    {
        return self::_fsSetPerms($filename, $executable, 1);
    }

    /**
     * 以字节为单位返回给定目录的大小。
     *
     * @param string $dir
     * @return integer
     */
    public static function fsDirSize($dir)
    {
        $size = 0;

        $flags = \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS;

        $dirIter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, $flags));

        foreach ($dirIter as $key) {
            if ($key->isFile()) {
                $size += $key->getSize();
            }
        }

        return $size;
    }

    /**
     * 返回目录中的所有路径。
     *
     * @param string $dir
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public static function fsLs($dir)
    {
        $contents = array();

        $flags = \FilesystemIterator::KEY_AS_PATHNAME
            | \FilesystemIterator::CURRENT_AS_FILEINFO
            | \FilesystemIterator::SKIP_DOTS;

        $dirIter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, $flags));

        foreach ($dirIter as $path => $fi) {
            $contents[] = $path;
        }

        natsort($contents);
        return $contents;
    }

    /**
     * 好的格式为计算机大小（字节）。
     *
     * @param   integer $bytes    The number in bytes to format
     * @param   integer $decimals The number of decimal points to include
     * @return  string
     */
    public static function fsFormat($bytes, $decimals = 2)
    {
        $exp    = 0;
        $value  = 0;
        $symbol = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        $bytes = floatval($bytes);

        if ($bytes > 0) {
            $exp   = floor(log($bytes) / log(1024));
            $value = ($bytes / pow(1024, floor($exp)));
        }

        if ($symbol[$exp] === 'B') {
            $decimals = 0;
        }

        return number_format($value, $decimals, '.', '') . ' ' . $symbol[$exp];
    }

    /**
     * @param string $filename
     * @param bool   $isFlag
     * @param int    $perm
     * @return bool
     */
    protected static function _fsSetPerms($filename, $isFlag, $perm)
    {
        $stat = @stat($filename);

        if ($stat === false) {
            return false;
        }

        // We're on Windows
        if (self::sysIsWin()) {
            //@codeCoverageIgnoreStart
            return true;
            //@codeCoverageIgnoreEnd
        }

        list($myuid, $mygid) = array(posix_geteuid(), posix_getgid());

        $isMyUid = $stat['uid'] == $myuid;
        $isMyGid = $stat['gid'] == $mygid;

        //@codeCoverageIgnoreStart
        if ($isFlag) {
            // Set only the user writable bit (file is owned by us)
            if ($isMyUid) {
                return chmod($filename, fileperms($filename) | intval('0' . $perm . '00', 8));
            }

            // Set only the group writable bit (file group is the same as us)
            if ($isMyGid) {
                return chmod($filename, fileperms($filename) | intval('0' . $perm . $perm . '0', 8));
            }

            // Set the world writable bit (file isn't owned or grouped by us)
            return chmod($filename, fileperms($filename) | intval('0' . $perm . $perm . $perm, 8));

        } else {
            // Set only the user writable bit (file is owned by us)
            if ($isMyUid) {
                $add = intval('0' . $perm . $perm . $perm, 8);
                return self::_fsChmod($filename, $perm, $add);
            }

            // Set only the group writable bit (file group is the same as us)
            if ($isMyGid) {
                $add = intval('00' . $perm . $perm, 8);
                return self::_fsChmod($filename, $perm, $add);
            }

            // Set the world writable bit (file isn't owned or grouped by us)
            $add = intval('000' . $perm, 8);
            return self::_fsChmod($filename, $perm, $add);
        }
        //@codeCoverageIgnoreEnd
    }

    /**
     * Chmod 别名
     *
     * @param string $filename
     * @param int    $perm
     * @param int    $add
     * @return bool
     */
    protected static function _fsChmod($filename, $perm, $add)
    {
        return chmod($filename, (fileperms($filename) | intval('0' . $perm . $perm . $perm, 8)) ^ $add);
    }

    /**
	 * 获取文件后缀
     * @param string $path
     * @return string
     */
    public static function fsExt($path)
    {
        if (strpos($path, '?') !== false) {
            $path = preg_replace('#\?(.*)#', '', $path);
        }

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $ext = strtolower($ext);

        return $ext;
    }

    /**
     * @param string $path
     * @return string
     */
    public static function fsBase($path)
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function fsFilename($path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function fsDirname($path)
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }

    /**
     * @param string $path
     * @return string
     */
    public static function fsReal($path)
    {
        return realpath($path);
    }

    /**
     * 防范 去除附加 / 或 \ 在路径名称中
     *
     * @param   string $path   The path to clean.
     * @param   string $dirSep Directory separator (optional).
     * @return  string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function fsClean($path, $dirSep = DIRECTORY_SEPARATOR)
    {
        if (!is_string($path) || empty($path)) {
            return '';
        }

        $path = trim((string)$path);

        if (empty($path)) {
            $path = self::varsGet($_SERVER['DOCUMENT_ROOT'], '');

        } elseif (($dirSep == '\\') && ($path[0] == '\\') && ($path[1] == '\\')) {
            $path = "\\" . preg_replace('#[/\\\\]+#', $dirSep, $path);

        } else {
            $path = preg_replace('#[/\\\\]+#', $dirSep, $path);
        }

        return $path;
    }

    /**
     * 如果它存在的话，就关闭扩展
     *
     * @param string $path
     * @return string
     */
    public static function fsStripExt($path)
    {
        $reg  = '/\.' . preg_quote(self::fsExt($path)) . '$/';
        $path = preg_replace($reg, '', $path);

        return $path;
    }

    /**
     * 检查是否为当前路径目录
     * @param string $path
     * @return bool
     */
    public static function fsIsDir($path)
    {
        $path = self::fsClean($path);
        return is_dir($path);
    }

    /**
     * Check is current path regular file
     * @param string $path
     * @return bool
     */
    public static function fsIsFile($path)
    {
        $path = self::fsClean($path);
        return file_exists($path) && is_file($path);
    }

    /**
     * Find relative path of file (remove root part)
     *
     * @param string      $filePath
     * @param string|null $rootPath
     * @param string      $forceDS
     * @param bool        $toRealpath
     * @return mixed
     */
    public static function fsGetRelative($filePath, $rootPath = null, $forceDS = DIRECTORY_SEPARATOR, $toRealpath = true)
    {
        // Cleanup file path
        if ($toRealpath && !self::fsIsReal($filePath)) {
            $filePath = self::fsReal($filePath);
        }
        $filePath = self::fsClean($filePath, $forceDS);


        // Cleanup root path
        $rootPath = $rootPath ?: self::sysGetDocRoot();
        if ($toRealpath && !self::fsIsReal($rootPath)) {
            $rootPath = self::fsReal($rootPath);
        }
        $rootPath = self::fsClean($rootPath, $forceDS);


        // Remove root part
        $relPath = preg_replace('#^' . preg_quote($rootPath) . '#i', '', $filePath);
        $relPath = ltrim($relPath, $forceDS);

        return $relPath;
    }

    /**
     * @param $path
     * @return bool
     */
    public static function fsIsReal($path)
    {
        $expected = self::fsClean(self::real($path));
        $actual   = self::fsClean($path);

        return $expected === $actual;
    }
	
	//----------------------------------- 图片操作 -------------------------------------
	 
	/**
     * 引入GD库
     * @param bool $thowException
     * @return bool
     * @throws Exception
     */
    public static function imgCheckGD($thowException = true)
    {
        $isGd = extension_loaded('gd');

        // Require GD library
        if ($thowException && !$isGd) {
            throw new Exception('Required extension GD is not loaded.'); // @codeCoverageIgnore
        }

        return $isGd;
    }

    /**
	 * 检测是否为jpeg格式
     * @param string $format
     * @return bool
     */
    public static function imgIsJpeg($format)
    {
        $format = strtolower($format);
        return 'image/jpg' === $format || 'jpg' === $format || 'image/jpeg' === $format || 'jpeg' === $format;
    }

    /**
	 * 检测是否为gif格式
     * @param string $format
     * @return bool
     */
    public static function imgIsGif($format)
    {
        $format = strtolower($format);
        return 'image/gif' === $format || 'gif' === $format;
    }

    /**
	 * 检测是否为png格式
     * @param string $format
     * @return bool
     */
    public static function imgIsPng($format)
    {
        $format = strtolower($format);
        return 'image/png' === $format || 'png' === $format;
    }

    /**
     * 将一个十六进制颜色值转换为RGB等值
     *
     * @param string|array $origColor Hex color string, array(red, green, blue) or array(red, green, blue, alpha).
     *                                Where red, green, blue - integers 0-255, alpha - integer 0-127
     * @return integer[]
     * @throws Exception
     */
    public static function imgNormalizeColor($origColor)
    {
        $result = array();

        if (is_string($origColor)) {
            $result = self::_imgNormalizeColorString($origColor);

        } elseif (is_array($origColor) && (count($origColor) === 3 || count($origColor) === 4)) {
            $result = self::_imgNormalizeColorArray($origColor);
        }

        if (count($result) !== 4) {
            throw new Exception('Undefined color format (string): ' . $origColor); // @codeCoverageIgnore
        }

        return $result;
    }

    /**
     * 规范颜色从字符串中
     *
     * @param string $origColor
     * @return integer[]
     * @throws Exception
     */
    protected static function _imgNormalizeColorString($origColor)
    {
        $color = trim($origColor, '#');
        $color = trim($color);

        if (strlen($color) === 6) {
            list($red, $green, $blue) = array(
                $color[0] . $color[1],
                $color[2] . $color[3],
                $color[4] . $color[5],
            );

        } elseif (strlen($color) === 3) {
            list($red, $green, $blue) = array(
                $color[0] . $color[0],
                $color[1] . $color[1],
                $color[2] . $color[2],
            );

        } else {
            throw new Exception('Undefined color format (string): ' . $origColor); // @codeCoverageIgnore
        }

        $red   = hexdec($red);
        $green = hexdec($green);
        $blue  = hexdec($blue);

        return array($red, $green, $blue, 0);
    }

    /**
     * 规范颜色从数组中
     *
     * @param array $origColor
     * @return integer[]
     * @throws Exception
     */
    protected static function _imgNormalizeColorArray(array $origColor)
    {
        $result = array();

        if (self::arrayCheckIsExistsKey('r', $origColor) && self::arrayCheckIsExistsKey('g', $origColor) && self::arrayCheckIsExistsKey('b', $origColor)) {
            $result = array(
                self::color($origColor['r']),
                self::color($origColor['g']),
                self::color($origColor['b']),
                self::alpha(self::arrayCheckIsExistsKey('a', $origColor) ? $origColor['a'] : 0),
            );

        } elseif (self::arrayCheckIsExistsKey(0, $origColor) && self::arrayCheckIsExistsKey(1, $origColor) && self::arrayCheckIsExistsKey(2, $origColor)) {
            $result = array(
                self::color($origColor[0]),
                self::color($origColor[1]),
                self::color($origColor[2]),
                self::alpha(self::arrayCheckIsExistsKey(3, $origColor) ? $origColor[3] : 0),
            );
        }

        return $result;
    }

    /**
     * 确保$value总是在$min和$max范围内。
     * 如果较低，$min返回。如果更高，$max返回。
     *
     * @param mixed $value
     * @param int   $min
     * @param int   $max
     *
     * @return int
     */
    public static function imgRange($value, $min, $max)
    {
        $value = self::filterInt($value);
        $min   = self::filterInt($min);
        $max   = self::filterInt($max);

        return self::varsLimit($value, $min, $max);
    }

    /**
     * 等同于 PHP's imagecopymerge() 方法, 除了保留 alpha-transparency in 24-bit PNGs
     * @link http://www.php.net/manual/en/function.imagecopymerge.php#88456
     *
     * @param mixed $dstImg   Dist image resource
     * @param mixed $srcImg   Source image resource
     * @param array $dist     Left and Top offset of dist
     * @param array $src      Left and Top offset of source
     * @param array $srcSizes Width and Height  of source
     * @param int   $opacity
     */
    public static function imgImageCopyMergeAlpha($dstImg, $srcImg, array $dist, array $src, array $srcSizes, $opacity)
    {
        list($dstX, $dstY) = $dist;
        list($srcX, $srcY) = $src;
        list($srcWidth, $srcHeight) = $srcSizes;

        // Get image width and height and percentage
        $opacity /= 100;
        $width  = imagesx($srcImg);
        $height = imagesy($srcImg);

        // Turn alpha blending off
        self::imgAddAlpha($srcImg, false);

        // Find the most opaque pixel in the image (the one with the smallest alpha value)
        $minAlpha = 127;
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $alpha = (imagecolorat($srcImg, $x, $y) >> 24) & 0xFF;
                if ($alpha < $minAlpha) {
                    $minAlpha = $alpha;
                }
            }
        }

        // Loop through image pixels and modify alpha for each
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {

                // Get current alpha value (represents the TANSPARENCY!)
                $colorXY = imagecolorat($srcImg, $x, $y);
                $alpha   = ($colorXY >> 24) & 0xFF;

                // Calculate new alpha
                if ($minAlpha !== 127) {
                    $alpha = 127 + 127 * $opacity * ($alpha - 127) / (127 - $minAlpha);
                } else {
                    $alpha += 127 * $opacity;
                }

                // Get the color index with new alpha
                $alphaColorXY = imagecolorallocatealpha(
                    $srcImg,
                    ($colorXY >> 16) & 0xFF,
                    ($colorXY >> 8) & 0xFF,
                    $colorXY & 0xFF,
                    $alpha
                );

                // Set pixel with the new color + opacity
                if (!imagesetpixel($srcImg, $x, $y, $alphaColorXY)) {
                    return;
                }
            }
        }

        // Copy it
        self::imgAddAlpha($srcImg);
        self::imgAddAlpha($dstImg);
        imagecopy($dstImg, $srcImg, $dstX, $dstY, $srcX, $srcY, $srcWidth, $srcHeight);
    }

    /**
     * 检查的不透明度值
     *
     * @param $opacity
     * @return int
     */
    public static function imgOpacity($opacity)
    {
        if ($opacity <= 1) {
            $opacity *= 100;
        }

        $opacity = self::filterInt($opacity);
        $opacity = self::varsLimit($opacity, 0, 100);

        return $opacity;
    }

    /**
     * 将不透明度值转换为Alpha
     * @param int $opacity
     * @return int
     */
    public static function imgOpacity2Alpha($opacity)
    {
        $opacity = self::opacity($opacity);
        $opacity /= 100;

        $aplha = 127 - (127 * $opacity);
        $aplha = self::alpha($aplha);

        return $aplha;
    }

    /**
     * @param int $color
     * @return int
     */
    public static function imgColor($color)
    {
        return self::imgRange($color, 0, 255);
    }

    /**
     * @param int $color
     * @return int
     */
    public static function imgAlpha($color)
    {
        return self::imgRange($color, 0, 127);
    }

    /**
     * @param int $color
     * @return int
     */
    public static function imgRotate($color)
    {
        return self::imgRange($color, -360, 360);
    }

    /**
     * @param int $brightness
     * @return int
     */
    public static function imgBrightness($brightness)
    {
        return self::imgRange($brightness, -255, 255);
    }

    /**
     * @param int $contrast
     * @return int
     */
    public static function imgContrast($contrast)
    {
        return self::imgRange($contrast, -100, 100);
    }

    /**
     * @param int $colorize
     * @return int
     */
    public static function imgColorize($colorize)
    {
        return self::imgRange($colorize, -255, 255);
    }

    /**
     * @param int $smooth
     * @return int
     */
    public static function imgSmooth($smooth)
    {
        return self::imgRange($smooth, 1, 10);
    }

    /**
     * @param string $direction
     * @return string
     */
    public static function imgDirection($direction)
    {
        $direction = trim(strtolower($direction));

        if (in_array($direction, array('x', 'y', 'xy', 'yx'), true)) {
            return $direction;
        }

        return 'x';
    }

    /**
     * @param string $blur
     * @return int
     */
    public static function imgBlur($blur)
    {
        return self::imgRange($blur, 1, 10);
    }

    /**
     * @param string $percent
     * @return int
     */
    public static function imgPercent($percent)
    {
        return self::imgRange($percent, 0, 100);
    }

    /**
     * @param string $percent
     * @return int
     */
    public static function imgQuality($percent)
    {
        return self::imgRange($percent, 0, 100);
    }

    /**
     * 将字符串转换为二进制数据
     *
     * @param $imageString
     * @return string
     */
    public static function imgStrToBin($imageString)
    {
        $cleanedString = str_replace(' ', '+', preg_replace('#^data:image/[^;]+;base64,#', '', $imageString));
        $result        = base64_decode($cleanedString, true);

        if (!$result) {
            $result = $imageString;
        }

        return $result;
    }

    /**
     * Check is format supported by lib
     *
     * @param string $format
     * @return bool
     */
    public static function imgIsSupportedFormat($format)
    {
        if ($format) {
            return self::imgIsJpeg($format) || self::imgIsPng($format) || self::imgIsGif($format);
        }

        return false;
    }

    /**
     * Check is var image GD resource
     *
     * @param mixed $image
     * @return bool
     */
    public static function imgIsGdRes($image)
    {
        return is_resource($image) && strtolower(get_resource_type($image)) === 'gd';
    }

    /**
     * Check position name
     *
     * @param string $position
     * @return string
     */
    public static function imgPosition($position)
    {
        $position = trim(strtolower($position));
        $position = str_replace(array('-', '_'), ' ', $position);

        if (in_array($position, array(self::TOP, 'top', 't'), true)) {
            return self::TOP;

        } elseif (in_array($position, array(self::TOP_RIGHT, 'top right', 'right top', 'tr', 'rt'), true)) {
            return self::TOP_RIGHT;

        } elseif (in_array($position, array(self::RIGHT, 'right', 'r'), true)) {
            return self::RIGHT;

        } elseif (in_array($position, array(self::BOTTOM_RIGHT, 'bottom right', 'right bottom', 'br', 'rb'), true)) {
            return self::BOTTOM_RIGHT;

        } elseif (in_array($position, array(self::BOTTOM, 'bottom', 'b'), true)) {
            return self::BOTTOM;

        } elseif (in_array($position, array(self::BOTTOM_LEFT, 'bottom left', 'left bottom', 'bl', 'lb'), true)) {
            return self::BOTTOM_LEFT;

        } elseif (in_array($position, array(self::LEFT, 'left', 'l'), true)) {
            return self::LEFT;

        } elseif (in_array($position, array(self::TOP_LEFT, 'top left', 'left top', 'tl', 'lt'), true)) {
            return self::TOP_LEFT;
        }

        return self::CENTER;
    }

    /**
     * Determine position
     *
     * @param string $position Position name or code
     * @param array  $canvas   Width and Height of canvas
     * @param array  $box      Width and Height of box that will be located on canvas
     * @param array  $offset   Forced offset X, Y
     * @return array
     */
    public static function imgGetInnerCoords($position, array $canvas, array $box, array $offset = array(0, 0))
    {
        $positionCode = self::imgPosition($position);
        list($canvasW, $canvasH) = $canvas;
        list($boxW, $boxH) = $box;
        list($offsetX, $offsetY) = $offset;

        // Coords map:
        // 00  10  20  =>  tl  t   tr
        // 01  11  21  =>  l   c   r
        // 02  12  22  =>  bl  b   br

        // X coord
        $x0 = $offsetX + 0;                             //  bottom-left     left        top-left
        $x1 = $offsetX + ($canvasW / 2) - ($boxW / 2);  //  bottom          center      top
        $x2 = $offsetX + $canvasW - $boxW;              //  bottom-right    right       top-right

        // Y coord
        $y0 = $offsetY + 0;                             //  top-left        top         top-right
        $y1 = $offsetY + ($canvasH / 2) - ($boxH / 2);  //  left            center      right
        $y2 = $offsetY + $canvasH - $boxH;              //  bottom-left     bottom      bottom-right

        if ($positionCode === self::TOP_LEFT) {
            return array($x0, $y0);

        } elseif ($positionCode === self::LEFT) {
            return array($x0, $y1);

        } elseif ($positionCode === self::BOTTOM_LEFT) {
            return array($x0, $y2);

        } elseif ($positionCode === self::TOP) {
            return array($x1, $y0);

        } elseif ($positionCode === self::BOTTOM) {
            return array($x1, $y2);

        } elseif ($positionCode === self::TOP_RIGHT) {
            return array($x2, $y0);

        } elseif ($positionCode === self::RIGHT) {
            return array($x2, $y1);

        } elseif ($positionCode === self::BOTTOM_RIGHT) {
            return array($x2, $y2);

        } else {
            return array($x1, $y1);
        }
    }

    /**
     * 将阿尔法香奈儿添加到图像资源
     *
     * @param mixed $image   Image GD resource
     * @param bool  $isBlend Add alpha blending
     */
    public static function imgAddAlpha($image, $isBlend = true)
    {
        imagesavealpha($image, true);
        imagealphablending($image, $isBlend);
    }
	
	//----------------------------------- url -------------------------------------
	
	/**
     * URL constants as defined in the PHP Manual under "Constants usable with http_build_url()".
     * @see http://us2.php.net/manual/en/http.constants.php#http.constants.url
     */
    const URL_REPLACE        = 1;
    const URL_JOIN_PATH      = 2;
    const URL_JOIN_QUERY     = 4;
    const URL_STRIP_USER     = 8;
    const URL_STRIP_PASS     = 16;
    const URL_STRIP_AUTH     = 32;
    const URL_STRIP_PORT     = 64;
    const URL_STRIP_PATH     = 128;
    const URL_STRIP_QUERY    = 256;
    const URL_STRIP_FRAGMENT = 512;
    const URL_STRIP_ALL      = 1024;

    const ARG_SEPARATOR = '&';

    const PORT_HTTP  = 80;
    const PORT_HTTPS = 443;

    /**
     * 增加和移除查询参数到URL.
     *
     * @param  mixed $newParams Either newkey or an associative array
     * @param  mixed $uri       URI or URL to append the queru/queries to.
     * @return string
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function urlAddArg(array $newParams, $uri = null)
    {
        $uri = is_null($uri) ? self::varsGet($_SERVER['REQUEST_URI'], '') : $uri;

        // Parse the URI into it's components
        $puri = parse_url($uri);
        if (self::arrayCheckIsExistsKey('query', $puri)) {
            parse_str($puri['query'], $queryParams);
            $queryParams = array_merge($queryParams, $newParams);

        } elseif (self::arrayCheckIsExistsKey('path', $puri) && strstr($puri['path'], '=') !== false) {
            $puri['query'] = $puri['path'];
            unset($puri['path']);
            parse_str($puri['query'], $queryParams);
            $queryParams = array_merge($queryParams, $newParams);

        } else {
            $queryParams = $newParams;
        }

        // Strip out any query params that are set to false.
        // Properly handle valueless parameters.
        foreach ($queryParams as $param => $value) {
            if ($value === false) {
                unset($queryParams[$param]);

            } elseif ($value === null) {
                $queryParams[$param] = '';
            }
        }

        // Re-construct the query string
        $puri['query'] = self::urlBuild($queryParams);

        // Strip = from valueless parameters.
        $puri['query'] = preg_replace('/=(?=&|$)/', '', $puri['query']);

        // Re-construct the entire URL
        $nuri = self::urlBuildAll($puri);

        // Make the URI consistent with our input
        foreach (array('/', '?') as $char) {
            if ($nuri[0] === $char && strstr($uri, $char) === false) {
                $nuri = substr($nuri, 1);
            }
        }

        return rtrim($nuri, '?');
    }

    /**
     * 返回当前URL
     *
     * @param bool $addAuth
     * @return string
     */
    public static function urlCurrent($addAuth = false)
    {
        $current = (string)self::urlRoot($addAuth) . (string)self::urlPath();
        return $current ? $current : null;
    }

    /**
     * 返回当前路径
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function urlPath()
    {
        $url = '';

        // Get the rest of the URL
        if (!self::arrayCheckIsExistsKey('REQUEST_URI', $_SERVER)) {
            // Microsoft IIS doesn't set REQUEST_URI by default
            $queryString = self::arrayCheckIsExistsKey('QUERY_STRING', $_SERVER, true);
            if ($queryString) {
                $url .= '?' . $queryString;
            }

        } else {
            $url .= $_SERVER['REQUEST_URI'];
        }

        return $url ? $url : null;
    }

    /**
     * 返回当前根url
     *
     * @param bool $addAuth
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function urlRoot($addAuth = false)
    {
        $url = '';

        // Check to see if it's over https
        $isHttps = self::urlIsHttps();

        // Was a username or password passed?
        if ($addAuth) {
            $url .= self::urlGetAuth();
        }

        // We want the user to stay on the same host they are currently on,
        // but beware of security issues
        // see http://shiflett.org/blog/2006/mar/server-name-versus-http-host
        $host = self::arrayCheckIsExistsKey('HTTP_HOST', $_SERVER, true);
        $port = self::arrayCheckIsExistsKey('SERVER_PORT', $_SERVER, true);
        $url .= str_replace(':' . $port, '', $host);

        // Is it on a non standard port?
        if ($isHttps && ($port != self::PORT_HTTPS)) {
            $url .= self::arrayCheckIsExistsKey('SERVER_PORT', $_SERVER) ? ':' . $_SERVER['SERVER_PORT'] : '';

        } elseif (!$isHttps && ($port != self::PORT_HTTP)) {
            $url .= self::arrayCheckIsExistsKey('SERVER_PORT', $_SERVER) ? ':' . $_SERVER['SERVER_PORT'] : '';
        }

        if ($url) {
            if ($isHttps) {
                return 'https://' . $url;
            } else {
                return 'http://' . $url;
            }
        }

        return null;
    }

    /**
     * 获取当前 auth info
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function urlGetAuth()
    {
        $result = null;
        $user = self::arrayCheckIsExistsKey('PHP_AUTH_USER', $_SERVER, true);
        if ($user) {
            $result .= $user;

            $password = self::arrayCheckIsExistsKey('PHP_AUTH_PW', $_SERVER, true);
            if ($password) {
                $result .= ':' . $password;
            }

            $result .= '@';
        }

        return $result;
    }

    /**
     * @param array $queryParams
     * @return string
     */
    public static function urlBuild(array $queryParams)
    {
        return http_build_query($queryParams, null, self::ARG_SEPARATOR);
    }

    /**
     * 建立一个网址。第二个网址的部分将被合并成第一个根据标志的参数
     * @author Jake Smith <theman@jakeasmith.com>
     * @see    https://github.com/jakeasmith/http_build_url/
     *
     * @param mixed $url    (part(s) of) an URL in form of a string or associative array like parse_url() returns
     * @param mixed $parts  same as the first argument
     * @param int   $flags  a bitmask of binary or'ed HTTP_URL constants; HTTP_URL_REPLACE is the default
     * @param array $newUrl if set, it will be filled with the parts of the composed url like parse_url() would return
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function urlBuildAll($url, $parts = array(), $flags = self::URL_REPLACE, &$newUrl = array())
    {
        is_array($url) || $url = parse_url($url);
        is_array($parts) || $parts = parse_url($parts);

        self::arrayCheckIsExistsKey('query', $url) && is_string($url['query']) || $url['query'] = null;
        self::arrayCheckIsExistsKey('query', $parts) && is_string($parts['query']) || $parts['query'] = null;
        $keys = array('user', 'pass', 'port', 'path', 'query', 'fragment');

        // HTTP_URL_STRIP_ALL and HTTP_URL_STRIP_AUTH cover several other flags.
        if ($flags & self::URL_STRIP_ALL) {
            $flags |= self::URL_STRIP_USER
                | self::URL_STRIP_PASS
                | self::URL_STRIP_PORT
                | self::URL_STRIP_PATH
                | self::URL_STRIP_QUERY
                | self::URL_STRIP_FRAGMENT;

        } elseif ($flags & self::URL_STRIP_AUTH) {
            $flags |= self::URL_STRIP_USER
                | self::URL_STRIP_PASS;
        }

        // Schema and host are alwasy replaced
        foreach (array('scheme', 'host') as $part) {
            if (self::arrayCheckIsExistsKey($part, $parts)) {
                $url[$part] = $parts[$part];
            }
        }

        if ($flags & self::URL_REPLACE) {
            foreach ($keys as $key) {
                if (self::arrayCheckIsExistsKey($key, $parts) && $parts[$key]) {
                    $url[$key] = $parts[$key];
                }
            }

        } else {
            if (self::arrayCheckIsExistsKey('path', $parts) && ($flags & self::URL_JOIN_PATH)) {
                if (self::arrayCheckIsExistsKey('path', $url) && substr($parts['path'], 0, 1) !== '/') {
                    $url['path'] = rtrim(str_replace(basename($url['path']), '', $url['path']), '/')
                        . '/' . ltrim($parts['path'], '/');

                } else {
                    $url['path'] = $parts['path'];
                }
            }

            if (self::arrayCheckIsExistsKey('query', $parts) && ($flags & self::URL_JOIN_QUERY)) {
                if (self::arrayCheckIsExistsKey('query', $url)) {
                    parse_str($url['query'], $urlQuery);
                    parse_str($parts['query'], $partsQuery);

                    $queryParams  = array_replace_recursive($urlQuery, $partsQuery);
                    $url['query'] = self::build($queryParams);
                }
                // see deadcode else condition from utilphp lib
            }
        }

        if (self::arrayCheckIsExistsKey('path', $url) && substr($url['path'], 0, 1) !== '/') {
            $url['path'] = '/' . $url['path'];
        }

        foreach ($keys as $key) {
            $strip = 'URL_STRIP_' . strtoupper($key);
            if ($flags & constant(__CLASS__ . '::' . $strip)) {
                unset($url[$key]);
            }
        }

        if (self::arrayCheckIsExistsKey('port', $url, true) === self::PORT_HTTPS) {
            $url['scheme'] = 'https';
        } elseif (self::arrayCheckIsExistsKey('port', $url, true) === self::PORT_HTTP) {
            $url['scheme'] = 'http';
        }

        $parsedString = '';
        if (self::arrayCheckIsExistsKey('scheme', $url)) {
            $parsedString .= $url['scheme'] . '://';
        }

        if (self::arrayCheckIsExistsKey('user', $url)) {
            $parsedString .= $url['user'];
            if (self::arrayCheckIsExistsKey('pass', $url)) {
                $parsedString .= ':' . $url['pass'];
            }
            $parsedString .= '@';
        }

        if (self::arrayCheckIsExistsKey('host', $url)) {
            $parsedString .= $url['host'];
        }

        if (self::arrayCheckIsExistsKey('port', $url) && $url['port']
            && $url['port'] !== self::PORT_HTTP
            && $url['port'] !== self::PORT_HTTPS
        ) {
            $parsedString .= ':' . $url['port'];
        }

        if (!empty($url['path'])) {
            $parsedString .= $url['path'];
        } else {
            $parsedString .= '/';
        }

        if (self::arrayCheckIsExistsKey('query', $url) && $url['query']) {
            $parsedString .= '?' . $url['query'];
        }

        if (self::arrayCheckIsExistsKey('fragment', $url) && $url['fragment']) {
            $parsedString .= '#' . trim($url['fragment'], '#');
        }

        $newUrl = $url;

        return $parsedString;
    }

    /**
     * 查看的网页是通过SSL或服务器
     *
     * @param bool $trustProxyHeaders
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function urlIsHttps($trustProxyHeaders = false)
    {
        // Check standard HTTPS header
        if (self::arrayCheckIsExistsKey('HTTPS', $_SERVER)) {
            return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
        }

        if ($trustProxyHeaders && self::arrayCheckIsExistsKey('X-FORWARDED-PROTO', $_SERVER)) {
            return $_SERVER['X-FORWARDED-PROTO'] === 'https';
        }

        // Default to not SSL
        return false;
    }

    /**
     * 从查询字符串中删除一个项目或列表。
     *
     * @param  string|array $keys Query key or keys to remove.
     * @param  bool         $uri  When false uses the $_SERVER value
     * @return string
     */
    public static function urlDelArg($keys, $uri = null)
    {
        if (is_array($keys)) {
            return self::urlAddArg(array_combine($keys, array_fill(0, count($keys), false)), $uri);
        }

        return self::urlAddArg(array($keys => false), $uri);
    }

    /**
     * 把一切字符串转换成HTML链接
     * Part of the LinkifyURL Project <https://github.com/jmrware/LinkifyURL>
     *
     * @param  string $text The string to parse
     * @return string
     */
    public static function urlParseLink($text)
    {
        $text = preg_replace('/&apos;/', '&#39;', $text); // IE does not handle &apos; entity!

        $sectionHtmlPattern = '%            # Rev:20100913_0900 github.com/jmrware/LinkifyURL
                                            # Section text into HTML <A> tags  and everything else.
             (                              # $1: Everything not HTML <A> tag.
               [^<]+(?:(?!<a\b)<[^<]*)*     # non A tag stuff starting with non-"<".
               | (?:(?!<a\b)<[^<]*)+        # non A tag stuff starting with "<".
             )                              # End $1.
             | (                            # $2: HTML <A...>...</A> tag.
                 <a\b[^>]*>                 # <A...> opening tag.
                 [^<]*(?:(?!</a\b)<[^<]*)*  # A tag contents.
                 </a\s*>                    # </A> closing tag.
             )                              # End $2:
             %ix';

        return preg_replace_callback($sectionHtmlPattern, array(__CLASS__, '_linkifyCallback'), $text);
    }

    /**
     * Callback for the preg_replace in the linkify() method.
     * Part of the LinkifyURL Project <https://github.com/jmrware/LinkifyURL>
     *
     * @param  string $matches Matches from the preg_ function
     * @return string
     */
    protected static function _urlLinkifyCallback($matches)
    {
        if (isset($matches[2])) {
            return $matches[2];
        }

        return self::_urlLinkifyRegex($matches[1]);
    }

    /**
     * 在linkify() 中 preg_replace的回调方法。
     * Part of the LinkifyURL Project <https://github.com/jmrware/LinkifyURL>
     *
     * @param  string $text Matches from the preg_ function
     * @return mixed
     */
    protected static function _urlLinkifyRegex($text)
    {
        $urlPattern = '/                                            # Rev:20100913_0900 github.com\/jmrware\/LinkifyURL
                                                                    # Match http & ftp URL that is not already linkified
                                                                    # Alternative 1: URL delimited by (parentheses).
            (\()                                                    # $1 "(" start delimiter.
            ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+) # $2: URL.
            (\))                                                    # $3: ")" end delimiter.
            |                                                       # Alternative 2: URL delimited by [square brackets].
            (\[)                                                    # $4: "[" start delimiter.
            ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+) # $5: URL.
            (\])                                                    # $6: "]" end delimiter.
            |                                                       # Alternative 3: URL delimited by {curly braces}.
            (\{)                                                    # $7: "{" start delimiter.
            ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+) # $8: URL.
            (\})                                                    # $9: "}" end delimiter.
            |                                                       # Alternative 4: URL delimited by <angle brackets>.
            (<|&(?:lt|\#60|\#x3c);)                                 # $10: "<" start delimiter (or HTML entity).
            ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+) # $11: URL.
            (>|&(?:gt|\#62|\#x3e);)                                 # $12: ">" end delimiter (or HTML entity).
            |                                                       # Alt. 5: URL not delimited by (), [], {} or <>.
            (                                                       # $13: Prefix proving URL not already linked.
            (?: ^                                                   # Can be a beginning of line or string, or
             | [^=\s\'"\]]                                          # a non-"=", non-quote, non-"]", followed by
            ) \s*[\'"]?                                             # optional whitespace and optional quote;
              | [^=\s]\s+                                           # or... a non-equals sign followed by whitespace.
            )                                                       # End $13. Non-prelinkified-proof prefix.
            (\b                                                     # $14: Other non-delimited URL.
            (?:ht|f)tps?:\/\/                                       # Required literal http, https, ftp or ftps prefix.
            [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]+                     # All URI chars except "&" (normal*).
            (?:                                                     # Either on a "&" or at the end of URI.
            (?!                                                     # Allow a "&" char only if not start of an...
            &(?:gt|\#0*62|\#x0*3e);                                 # HTML ">" entity, or
            | &(?:amp|apos|quot|\#0*3[49]|\#x0*2[27]);              # a [&\'"] entity if
            [.!&\',:?;]?                                            # followed by optional punctuation then
            (?:[^a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]|$)              # a non-URI char or EOS.
           ) &                                                      # If neg-assertion true, match "&" (special).
            [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]*                     # More non-& URI chars (normal*).
           )*                                                       # Unroll-the-loop (special normal*)*.
            [a-z0-9\-_~$()*+=\/#[\]@%]                              # Last char can\'t be [.!&\',;:?]
           )                                                        # End $14. Other non-delimited URL.
            /imx';

        $urlReplace = '$1$4$7$10$13<a href="$2$5$8$11$14">$2$5$8$11$14</a>$3$6$9$12';

        return preg_replace($urlPattern, $urlReplace, $text);
    }

    /**
     * Convert file path to relative URL
     *
     * @param $path
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function urlPathToRel($path)
    {
        $root = self::fsClean(self::varsGet($_SERVER['DOCUMENT_ROOT']));
        $path = self::fsClean($path);

        $normRoot = str_replace(DIRECTORY_SEPARATOR, '/', $root);
        $normPath = str_replace(DIRECTORY_SEPARATOR, '/', $path);

        $regExp   = '/^' . preg_quote($normRoot, '/') . '/i';
        $relative = preg_replace($regExp, '', $normPath);

        $relative = ltrim($relative, '/');

        return $relative;
    }

    /**
     * 将文件路径转换为绝对的网址
     *
     * @param $path
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function urlPathToUrl($path)
    {
        return self::urlRoot() . '/' . self::urlPathToRel($path);
    }

    /**
     * 是否是绝对的URL
     *
     * @param $path
     * @return bool
     */
    public static function urlIsAbsolute($path)
    {
        $result = strpos($path, '//') === 0
            || preg_match('#^[a-z-]{3,}:\/\/#i', $path);

        return $result;
    }

    /**
     * @param array $parts
     * @return string
     */
    public static function urlCreate(array $parts = array())
    {
        $parts = array_merge(array(
            'scheme' => 'http',
            'query'  => array(),
        ), $parts);

        if (is_array($parts['query'])) {
            $parts['query'] = self::urlBuild($parts['query']);
        }

        return self::urlBuildAll('', $parts, self::URL_REPLACE);
    }

}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
