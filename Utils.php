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
	
	/**
     * @param $url 内容url地址
     * @param $url $before 提示信息前面内容
     * @return mixed $ch 资源
     */
    public static function getContent($url, $before='') {
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
     */
    public static function getImage($url, $dir='./', $type=0, $filename=''){
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
}
