<?php

namespace Yurun\PaySDK\Ttalipay;

use think\Log;
use \Yurun\PaySDK\Base;
use \Yurun\PaySDK\Lib\ObjectToArray;

/**
 * 支付宝即时到账SDK类
 */
class SDK extends Base
{
    /**
     * 公共参数
     * @var \Yurun\PaySDK\Ttalipay\Params\PublicParams
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

        $notify_url = $data ["notify_url"];
        $sub_mer_no = '';
        $mer_no = $data['appID'];
        $user_id = $data ["user_id"];
        $order_no = $data ["order_no"];
        $order_time = date('YmdHis');
        $order_amount = $data ["order_amount"];
        $currency = 'CNY';
        $terminal_type = 'wap';
        $goods_id = '';
        $goods_name = $data ["goods_name"];
        $goods_num = 1;
        $goods_type = 2;
        $attach = $data ["attach"];
        $url = $this->_url;
        $domain = $this->_domain;

        $share_benefit_flag = null;//$data ["share_benefit_flag"];
        $share_benefit_exp = null;//$data ["share_benefit_exp"];

        $service = 'fosun.sumpay.api.pay.qrcode.trade.apply';

        $parameters = [
            'app_id' => $mer_no,
            'terminal_type' => $terminal_type,
            'version' => '1.0',
            'service' => $service,
            'timestamp' => $order_time,
            'mer_no' => $mer_no,
            'user_id' => $user_id,
            'order_no' => $order_no,
            'order_time' => $order_time,
            'order_amount' => $order_amount,
            'goods_name' => $goods_name,
            'goods_num' => $goods_num,
            'goods_type' => $goods_type,
            'notify_url' => $notify_url,
            'business_code' => 10,
        ];

        if ($goods_id && "" != $goods_id) {
            $parameters ['goods_id'] = $goods_id;
        }
        if ($currency && "" != $currency) {
            $parameters ['currency'] = $currency;
        }
        if ($sub_mer_no && "" != $sub_mer_no) {
            $parameters ['sub_mer_no'] = $sub_mer_no;
        }
        if ($attach && "" != $attach) {
            $parameters ['attach'] = $attach;
        }
        if ($share_benefit_flag && "" != $share_benefit_flag) {
            $parameters ['share_benefit_flag'] = $share_benefit_flag;
        }
        if ($share_benefit_exp && "" != $share_benefit_exp && "1" == $share_benefit_flag) {
            $parameters ['share_benefit_exp'] = $share_benefit_exp;
        }

        $encrypted_fields = array();
        $charset_change_fields = array(
            "terminal_info",
            "attach",
            "goods_name"
        );
        $special_fields = array(
            "terminal_info",
            "attach",
            "notify_url",
            "goods_name",
            "share_benefit_exp"
        );
        $defaultCharset = 'UTF-8';

        require __DIR__ . '/tools/SumpayService.php';

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
        require __DIR__ . '/tools/SignAndCheck.php';
        $signature = $data['sign'];
        $data['sign'] = '';
        $data['sign_type'] = '';
        $strData = getStr($data);
        if (verify($strData, $signature, $this->publicParams->appPublicKeyFile)) {
            Log::info("验证成功\n");
            if ($data['status'] == '1') {
                Log::info("交易成功");
                return true;
            } else if ($data['status'] == '2') {
                Log::info("交易处理中");
            } else if ($data['status'] == '3') {
                Log::info("已支付等待确认收货");
            } else {
                Log::info("交易失败");
            }
            $arr = array('resp_code' => '000000');
            echo json_encode($arr);
        } else {
            Log::info("验证失败");
            $arr = array('resp_code' => '999999');
            $this->_error = $arr;
            return false;
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