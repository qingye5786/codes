<?php
/**
 * 工具类
 */
class Utils {
	/**
	 * 获取post数据
	 * @param string $var_name
	 * @return string false
	 */
	public static function postVar($var_name = NULL) {
		if(!$var_name) {
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
	public static function daddslashes($data, $filter = 0) {
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
	public static function dump($var, $echo=true, $label=null, $strict=true) {
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
}
