<?php
namespace yiifloruit\wechat;

use Yii;
use yii\base\Component;

/**
 * URl加密
 * @author  AllenSun  [sunyujiebj@gmail.com]
 */
class UrlEncrypt extends Component
{
    /**
     * 生成URl
     * @param string $url
     * @param string|array $key
     * @return string
     */
    public static function encrypt_url($url, $key = 'floruit') {
        return rawurlencode(base64_encode(self::encrypt($url,$key)));
    }
    
    /**
     * 获取URL
     * @param string $str
     * @param string $key
     * @return array
     */
    public static function geturl($str, $key = 'floruit') {
        $str = self::decrypt_url($str,$key);
        $url_array = explode('&',$str);
        if (is_array($url_array)) {
            foreach ($url_array as $var) {
                $var_array = explode("=",$var);
                $vars[$var_array[0]]=$var_array[1];
            }
        }
        
        return $vars;
    }
    
    /**
     * 加密
     * @param string $txt
     * @param string $key
     */
    protected static function encrypt($txt, $key) {
        $encrypt_key = md5(mt_rand(0,100));
        $ctr=0;
        $tmp = "";
        for ($i=0;$i<strlen($txt);$i++) {
            if ($ctr==strlen($encrypt_key))
                $ctr=0;
            $tmp.=substr($encrypt_key,$ctr,1) . (substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1));
            $ctr++;
        }
        
        return self::keyED($tmp,$key);
    }
    
    /**
     * 解密
     * @param string $txt
     * @param string $key
     * @return Ambigous <string, boolean>
     */
    protected static function decrypt($txt, $key) {
        $txt = self::keyED($txt,$key);
        $tmp = "";
        for($i=0;$i<strlen($txt);$i++) {
            $md5 = substr($txt,$i,1);
            $i++;
            $tmp.= (substr($txt,$i,1) ^ $md5);
        }
        
        return $tmp;
    }

    /**
     * 解密URL
     * @param string $url
     * @param string $key
     * @return \yiifloruit\wechat\Ambigous
     */
    protected static function decrypt_url($url, $key) {
        return self::decrypt(base64_decode(rawurldecode($url)),$key);
    }
    
    /**
     * @param string $txt
     * @param string $encrypt_key
     */
    protected static function keyED($txt, $encrypt_key) {
        $encrypt_key = md5($encrypt_key);
        $ctr=0;
        $tmp = "";
        for($i=0;$i<strlen($txt);$i++) {
            if ($ctr==strlen($encrypt_key))
                $ctr=0;
            $tmp.= substr($txt,$i,1) ^ substr($encrypt_key,$ctr,1);
            $ctr++;
        }
        
        return $tmp;
    }   
}
