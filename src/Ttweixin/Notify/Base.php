<?php
namespace Yurun\PaySDK\Ttweixin\Notify;

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
	}

	/**
	 * 返回数据
	 * @param boolean $success
	 * @param string $message
	 * @return void
	 */
	public function reply($success, $message = '')
	{
		echo $success ? 'success' : 'fail';
	}

	/**
	 * 获取通知数据
	 * @return void
	 */
	public function getNotifyData()
	{
		return json_decode(\file_get_contents('php://input'), true);
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