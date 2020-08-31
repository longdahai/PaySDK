<?php

namespace Yurun\PaySDK\Xinsheng;

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
     * @var \Yurun\PaySDK\Xinsheng\Params\PublicParams
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

        $parameters = $this->sign([
            'total_fee' => $data['total_fee'],
            'merchant_no' => $data['appID'],
            'out_trade_no' => $data['out_trade_no'],
            'subject' => $data['subject'],
            'order_desc' => $data['subject'],
            'pay_type' => 'UNION_WECHAT',
            'front_url' => $data['front_url'],
            'notify_url' => $data['notify_url'],
            'spbill_create_ip' => $data['spbill_create_ip'],
            'version' => '1.0',
            'sign_type' => 'MD5',
            'appid' => 'wxaca49ad3fecf54b2',
            'openId' => '123456789',
        ]);
        dump($parameters);
        $requestData = $parameters;
        $url = $this->publicParams->apiDomain;
    }

    public function sign($params)
    {
        $data = $params;
        ksort($data);

        $sign = [];
        foreach ($data as $key => $value) {
            $sign[] = "$key=$value";
        }
        $sign[] = 'key=' . $this->publicParams->key;
        $data['sign'] = join('&', $sign);
        $data ['sign'] = strtoupper(md5($data['sign']));
        unset($data['key']);

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
        dump($result);
        return $result;
    }

    /**
     * 检查是否执行成功
     * @param array $result
     * @return boolean
     */
    protected function __checkResult($result)
    {
        return isset($result['return_code']) && '000000' === $result['return_code'];
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