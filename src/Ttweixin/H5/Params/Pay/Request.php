<?php
namespace Yurun\PaySDK\Ttweixin\H5\Params\Pay;

use Yurun\PaySDK\RequestBase;

/**
 * 微信支付-H5支付请求类
 */
class Request extends RequestBase
{
    public $app_id;
    public $terminal_type;
    public $version;
    public $service;
    public $timestamp;
    public $mer_no;
    public $user_id;
    public $order_no;
    public $order_time;
    public $order_amount;
    public $goods_name;
    public $goods_num;
    public $goods_type;
    public $notify_url;
    public $business_code;

    public function __construct()
    {
        $this->terminal_type = 'wap';
    }


}