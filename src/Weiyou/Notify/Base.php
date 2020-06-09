<?php
namespace Yurun\PaySDK\Weiyou\Notify;

use Yurun\PaySDK\NotifyBase;
use \Yurun\PaySDK\Weixin\Reply\Base as ReplyBase;
use \Yurun\PaySDK\Lib\XML;
use \Yurun\PaySDK\Lib\ObjectToArray;

/**
 * 微信支付-通知处理基类
 */
abstract class Base extends NotifyBase
{
	public function __construct()
	{
		parent::__construct();
		$this->replyData = new ReplyBase;
	}

	/**
	 * 返回数据
	 * @param boolean $success
	 * @param string $message
	 * @return void
	 */
	public function reply($success, $message = '')
	{
		$this->replyData->return_code = $success ? 'SUCCESS' : 'FAIL';
		$this->replyData->return_msg = $message;
		echo $this->replyData;
	}

	/**
	 * 获取通知数据
	 * @return void
	 */
	public function getNotifyData()
	{
		return $_POST;
	}
	
	/**
	 * 对通知进行验证，是否是正确的通知
	 * @return bool
	 */
	public function notifyVerify()
	{
		return $this->sdk->verifyCallback($this->data);
	}
}