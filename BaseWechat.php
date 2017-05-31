<?php
namespace yiifloruit\wechat;

use Yii;
use yii\base\Component;
use yii\base\Exception;

/**
 * 微信基础类基础类
 * @author  AllenSun [sunyujiebj@gmail.com]
 */
class BaseWechat extends Component
{
    /**
     * 配置参数(支持多组配置)
     * @var string
     */
    protected $config = 'wechatDef';
    
    /**
     * 访问令牌
     * @var string
     */
    protected $accessToken = null;
    
    /**
     * 缓存键令牌
     * @var string
     */
    protected $_cacheKeyAT = 'WeChatAccessToken';
    
    /**
     * 初始化
     * @see \yii\base\Object::init()
     */
    public function init() {
        $this->accessToken = Yii::$app->cache->get('WeChatAccessToken');
        if (empty($this->accessToken)) {
            $this->accessToken = $this->getAccessToken();
        }
    }

    /**
     * 获取Token
     * @param number $duration
     * @return string
     */
    public function getAccessToken() {
        if (empty(Yii::$app->params[$this->config]['corpid']) || empty(Yii::$app->params[$this->config]['corpsecret'])) {
            throw new Exception('发送失败:'.$this->config.'配置参数');
        }
        
        $tokenUrl = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid={corpid}&corpsecret={corpsecret}";
        $result = $this->https_post(str_replace(['{corpid}', '{corpsecret}'], 
            [
                Yii::$app->params[$this->config]['corpid'],     //企业Id
                Yii::$app->params[$this->config]['corpsecret']  //管理组的凭证密钥               
            ], $tokenUrl), true);
        
        if (!isset($result['access_token'])) {
            throw new Exception('发送失败:令牌获取');
        }

        Yii::$app->cache->set($this->_cacheKeyAT, $result['access_token'], $result['expires_in']);        
        return $result['access_token'];
    }
    
    
    /**
     * Http请求
     * @param string $url
     * @param array $data
     * @param string $isArray
     * @return string|mixed
     */
    protected function https_post($url, $data = [], $isArray = true) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($curl, CURLOPT_SSLVERSION, 3);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        
        //返回数组
        if ($isArray == true) {
            $result = json_decode($result, true);
        }
        
        return $result;
    }

}
