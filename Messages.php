<?php
namespace yiifloruit\wechat;

use Yii;
use yii\base\Exception;

/**
 * 微信消息类
 * @author  AllenSun  [sunyujiebj@gmail.com]
 */
class Messages extends BaseWechat
{
    /**
     * 企业应用的id
     * @var int
     */
    protected $agentId = 0;

    /**
     * 初始化
     * @see \yii\base\Object::init()
     */
    public function init() {
        if (empty(Yii::$app->params[$this->config]['agentid'])) {
            throw new Exception('发送失败:参数agentid不能为空');
        } else {
            $this->agentId = Yii::$app->params[$this->config]['agentid'];
        }
        
        parent::init();  
    }
    
    /**
     * 发送单条消息
     * @param string $msgType news
     * @param array $params ['userid' => '', 'title' => '', 'description' => '', 'picurl' => '']
     * @see open_id array | string
     * @param string $url       访问绝对路径
     * @param string $urlPar    需要穿点的参数
     */
    public function SendOne($msgType, $params, $url, $urlPar = []) {   
        if (empty($params['userid'])) {
            throw new Exception('发送失败:userid不能为空');
        }
       
        //对url参数加密处理
        if (!empty($urlPar)) {
            $url = $url .'?param='.UrlEncrypt::encrypt_url(http_build_query($urlPar));
        }

        switch ($msgType) {
            case 'news':
                $messageData = $this->generateNews($url, $params);
                break;
            default:
                throw new Exception('发送失败：消息类型错误');
        }

        $postUrl = "https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token={$this->accessToken}";        
        return $this->https_post($postUrl, $messageData);
    }
    
    /**
     * 发送消息
     * @param string $url
     * @param array $message
     * @param int $agentId
     * @return string
     */
    private function generateNews($url, $params) {           
        return json_encode([
            'touser' => is_array($params['userid']) ? implode('|', $params['userid']) : $params['userid'],
            'msgtype' => 'news',
            'agentid' => $this->agentId,
            'news' => [
                'articles' => [
                    ['title' => empty($params['title']) ? '' : $params['title'],
                        'description' => empty($params['description']) ? '' : $params['description'],
                        'url' => $url,
                        'picurl' => empty($params['picurl']) ? '' : $params['picurl']
                    ],
                ]
            ]
        ], JSON_UNESCAPED_UNICODE);
    }    
}
