<?php

namespace Yurun\PaySDK\Weiyou;

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

        $post = [
            'merchantId' => $data['merchantId'],
            'appId' => $data['appID'],
            'cpOrderId' => $data['cpOrderId'],
            'cpOrderName' => $data['cpOrderName'],
            'cpOrderDesc' => $data['cpOrderDesc'],
            'reqFee' => $data['reqFee'],
            'notifyUrl' => $data['notifyUrl'],
            'redirectUrl' => $data['redirectUrl'],
            'clientIp' => $data['clientIp'],
        ];

        $post['sign'] = $this->sign($post);
        $requestData = $post;
        $url = $this->publicParams->apiDomain;
    }

    public function sign($params)
    {
        ksort($params);

        $origin = [];
        foreach ($params as $key => $value) {
            $origin[] = "$key=$value";
        }
        $origin = join('&', $origin);

        return md5($origin . '&key=' . $this->publicParams->md5Key);
    }

    /**
     * 验证回调通知是否合法
     * @param $data
     * @return bool
     */
    public function verifyCallback($data)
    {
        if (!isset($data['sign'])) {
            return false;
        }

        $tmp = $data;
        unset($tmp['sign']);

        $sign = $this->sign($tmp);

        return $data['sign'] === $sign;
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
        return isset($result['resultCode']) && 1 === $result['resultCode'];
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