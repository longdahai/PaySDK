<?php
//异步通知验签逻辑
class Notify
{
	public function verification($str, $appkey)
	{
		if($str != '') {
			$arr = explode('&', $str);
			$nowArr = array();
			foreach($arr as $v) {
				$kv = explode('=', $v);
				$nowArr[$kv[0]] = $kv[1];
			}
			ksort($nowArr);

			$newstr = '';
			foreach($nowArr as $key => $value) {
				if($value == '' || $key == 'signature') {
					continue;
				}
				$newstr .= $key.'='.urldecode($value).'&';
			}
			$newstr .= md5($appkey);
            \Think\Log::record("原串:" . $newstr);

			if( $nowArr['signature'] == md5($newstr) ) {
				return true;
			} else {
				return false;
			}
		}
	}	
}	
?>