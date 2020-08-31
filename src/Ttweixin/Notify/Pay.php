<?php
namespace Yurun\PaySDK\Ttweixin\Notify;

use \Yurun\PaySDK\Ttweixin\Notify\Base;
use \Yurun\PaySDK\Weixin\Reply\Pay as ReplyPay;

/**
 * 微信支付-支付通知处理基类
 */
abstract class Pay extends Base
{
	/**
	 * 返回数据
	 * @var \Yurun\PaySDK\Ttweixin\Reply\Pay
	 */
	public $replyData;

	public function __construct()
	{
		parent::__construct();
		$this->replyData = new ReplyPay;
	}
}