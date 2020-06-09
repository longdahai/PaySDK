<?php
namespace Yurun\PaySDK\Weiyou\Notify;

use \Yurun\PaySDK\Weiyou\Notify\Base;
use \Yurun\PaySDK\Weiyou\Reply\Pay as ReplyPay;

/**
 * 微信支付-支付通知处理基类
 */
abstract class Pay extends Base
{
	/**
	 * 返回数据
	 * @var \Yurun\PaySDK\Weiyou\Reply\Pay
	 */
	public $replyData;

	public function __construct()
	{
		parent::__construct();
		$this->replyData = new ReplyPay;
	}
}