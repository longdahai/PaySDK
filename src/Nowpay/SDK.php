<?php

namespace Yurun\PaySDK\Nowpay;

use \Yurun\PaySDK\Base;
use \Yurun\PaySDK\Lib\ObjectToArray;

/**
 * 支付宝即时到账SDK类
 */
class SDK extends Base
{
    /**
     * 公共参数
     * @var \Yurun\PaySDK\Nowpay\Params\PublicParams
     */
    public $publicParams;

    /**
     * 处理执行接口的数据
     * @param $params
     * @param &$data 数据数组
     * @param &$requestData 请求用的数据，格式化后的
     * @param &$url 请求地址
     * @return array
     */
    public function __parseExecuteData($params, &$data, &$requestData, &$url)
    {
        $data = \array_merge(ObjectToArray::parse($this->publicParams), ObjectToArray::parse($params));

        require_once __DIR__ . '/lib/config.php';
        require_once __DIR__ . '/lib/signaTure.php';
        require_once __DIR__ . '/lib/notify/postRequest.php';

        $payType = 'zhifubao';
        $outputType = 1;
        switch ($payType) {
            case 'weixin':
                $appid = Config::$wxAppId;
                $key = Config::$wxKey;
                $payChannelType = 13;
                break;
            case 'zhifubaoisv':
                $appid = Config::$zfbisvAppId;
                $key = Config::$zfbisvKey;
                $payChannelType = 12;
                break;
            case 'zhifubao':
                $appid = Config::$zfbAppId;
                $key = Config::$zfbKey;
                $payChannelType = 12;
                break;
        }
        //$appid = Config::$zfbAppId;
        //$key = Config::$zfbKey;
        $outputType = '0';
        $consumerCreateIp = '0.0.0.1';
        if ($payType == 'weixin' && $outputType == '0') {
            $outputType = '0';
            $consumerCreateIp = get_client_ip(); //需上传用户真实ip
        } else if ($payType == 'weixin' && $outputType == '1') {
            $outputType = '2';
        } else if ($payType == 'zhifubao' && $outputType == '0') {
            $outputType = '0';
        } else if ($payType == 'zhifubao' && $outputType == '1') {
            $outputType = '1';
        }

        $req = array();
        $req["appId"] = $appid;
        $req["deviceType"] = Config::TRADE_DEVICE_TYPE;
        $req["frontNotifyUrl"] = $this->return_url;//前台通知，三方支付宝0模式有效，官方支付宝0、1模式有效，微信0模式有效
        $req["funcode"] = Config::TRADE_FUNCODE;
        $req["mhtCharset"] = Config::TRADE_CHARSET;
        $req["mhtCurrencyType"] = Config::TRADE_CURRENCYTYPE;
        $req["mhtOrderAmt"] = $param['amount'] * 100;
        $req["mhtOrderDetail"] = $param['rate_money'] . $param['subject'];
        $req["mhtOrderName"] = $param['rate_money'] . $param['subject'];
        $req["mhtOrderNo"] = $param['order_id'];
        //$req["mhtOrderNo"]        ="11111152222899997799";
        $req["mhtOrderStartTime"] = date("YmdHis");
        $req["mhtOrderTimeOut"] = Config::$trade_time_out;
        $req["mhtOrderType"] = Config::TRADE_TYPE;
        $req["mhtReserved"] = "";
        $req["mhtSignType"] = Config::TRADE_SIGN_TYPE;
        $req["notifyUrl"] = $this->notify_url;
        $req["outputType"] = $outputType;//   0 默认值
        $req["payChannelType"] = $payChannelType; //12 支付宝  //13 微信 //20 银联  //25  手Q
        $req["version"] = "1.0.0";
        $req["consumerCreateIp"] = $consumerCreateIp; //微信必填// outputType=2时 无须上送该值

        $info = new SignaTure;
        $req_str = $info->getToStr($req, $key);


        if ($payType == 'weixin' || $outputType == '0') {
            header("location:" . Config::TRADE_URL . "?" . $req_str);
            die();
        }
        $post = new PostRequest;
        $res = $post->post(Config::TRADE_URL, $req_str);

        $code = (bool)stripos($res, '&tn=');
        if ($code) {
            $arr = explode('&', $res);
            $gettn = '';
            foreach ($arr as $v) {
                $tn = explode('=', $v);
                if ($tn[0] == 'tn') {
                    $gettn = $tn[1];
                }
            }
            //注意
            //支付宝ISV返回的tn urldecode函数会自动转义，导致tn解码不正确
            // 1、&not被转义，显示成﹁。2、&times会被转义成 X
//		if(strpos($gettn,'notify_url')!== false){
//            $gettn=str_replace("notify_url","ampnotify_url",$gettn);
//            $gettn=str_replace("timestamp","amptimestamp",$gettn);
//      }
            echo urldecode($gettn);
            $tn = urldecode($gettn);
            //echo "请点击链接进行支付：<a href='". urldecode($gettn) ."'>点我支付</a>";
            //echo "请点击链接进行支付：<a href='$tn'>点我支付</a>";
            return array('status' => 0, 'html' => $tn);

        } else {
            return array('status' => 1, 'html' => '');
        }

        $parameters = $this->sign([
            'url' => $url,
            'charset' => 'UTF-8',
            'data' => $parameters,
            'privateKeyName' => $data['appPrivateKeyFile'],
            'password' => "1heron.com",
            'publicKeyName' => $data['appPublicKeyFile'],
            'domain' => $domain,
            'charset_change_fields' => $charset_change_fields,
            'encrypted_fields' => $encrypted_fields,
            'special_fields' => $special_fields,
            'defaultCharset' => $defaultCharset
        ]);
        $requestData = $parameters;
        $url = $this->publicParams->apiDomain;
    }

    public function sign($params)
    {
        $data = charsetChange($params['charset_change_fields'], $params['data'], $params['charset'], $params['defaultCharset']);
        $aes = new \CryptAES();
        $aesKey = $aes->getAesKey();
        $aes->set_key($aesKey);
        $aes->require_pkcs5();
        $data = encryptByAesKey($params['encrypted_fields'], $aesKey, $data, $aes);
        $aesKey = encrpyt(base64_encode($aesKey), $params['publicKeyName']);
        $data ['aes_key'] = $aesKey;
        $data = specialChange($params['special_fields'], $data);
        $signStr = sign(getStr($data), $params['privateKeyName'], $params['password']);
        $data ['sign'] = $signStr;
        $data ['sign_type'] = 'CERT';
        $optional_headers = "Referer: " . $params['domain'] . "\r\n";

        return $data;
    }

    /**
     * 验证回调通知是否合法
     * @param $data
     * @return bool
     */
    public function verifyCallback($data)
    {
        if (!isset($data['sign'], $data['sign_type'])) {
            return false;
        }
        $content = $this->parseSignData($data);
        if (empty($this->publicParams->appPublicKeyFile)) {
            $key = $this->publicParams->appPublicKey;
            $method = 'verifyPublic';
        } else {
            $key = $this->publicParams->appPublicKeyFile;
            $method = 'verifyPublicFromFile';
        }
        switch ($this->publicParams->sign_type) {
            case 'DSA':
                return \Yurun\PaySDK\Lib\Encrypt\DSA::$method($content, $key, \base64_decode($data['sign']));
            case 'RSA':
                return \Yurun\PaySDK\Lib\Encrypt\RSA::$method($content, $key, \base64_decode($data['sign']));
            case 'MD5':
                return $data['sign'] === md5($content . $this->publicParams->md5Key);
            default:
                throw new \Exception('未知的加密方式：' . $this->publicParams->sign_type);
        }
    }

    /**
     * 验证同步返回内容
     * @param AlipayRequestBase $params
     * @param array $data
     * @return bool
     */
    public function verifySync($params, $data)
    {
        return true;
    }

    public function parseSignData($data)
    {
        unset($data['sign_type'], $data['sign']);
        \ksort($data);
        $content = '';
        foreach ($data as $k => $v) {
            if ($v !== '' && $v !== null && !is_array($v)) {
                $content .= $k . '=' . $v . '&';
            }
        }
        return trim($content, '&');
    }

    /**
     * 调用执行接口
     * @param mixed $params
     * @param string $method
     * @return mixed
     */
    public function execute($params, $format = 'JSON')
    {
        $result = parent::execute($params, $format);
        return $result;
    }

    /**
     * 检查是否执行成功
     * @param array $result
     * @return boolean
     */
    protected function __checkResult($result)
    {
        return isset($result['resp_code']) && '000000' === $result['resp_code'];
    }

    /**
     * 获取错误信息
     * @param array $result
     * @return string
     */
    protected function __getError($result)
    {
        return $result['resp_msg'];
    }

    /**
     * 获取错误代码
     * @param array $result
     * @return string
     */
    protected function __getErrorCode($result)
    {
        return $result['resp_code'];
    }
}