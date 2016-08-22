<?php
/**
 * 工具类
 */
class Utils 
{
	public static $encoding = 'UTF-8'; // 默认字符串
	
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
	//---------------------------------- 字符串 ------------------------------------
	
	
	
	
	
	
	//----------------------------------- 数组 -------------------------------------
	//----------------------------------- 数组 -------------------------------------
	//----------------------------------- 数组 -------------------------------------
	//----------------------------------- 数组 -------------------------------------
	//----------------------------------- 数组 -------------------------------------

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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
