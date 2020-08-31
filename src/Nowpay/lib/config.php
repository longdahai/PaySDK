<?php
/**
* 配置参数
*/
	class Config{
        static $zfbAppId="155349875988424";
        static $zfbKey="FgPHGHDqMfyk3ukyvPStIzNEFdyvBrVS";//支付宝测试参数
       // static $zfbAppId="147868777472129";
        //static $zfbKey="1FZMAlAplOTamX6OARDVV8hrswhbGEVg";//支付宝测试参数

		
		static $timezone="Asia/Shanghai";
        static $trade_time_out="3600";
        static $front_notify_url="https://www.baidu.com/";
        static $back_notify_url="http://qxu1649340405.my3w.com/wap_php_demo/notify/bnotify.php";

        const TRADE_URL="https://pay.ipaynow.cn";//正式交易接口地址
        const QUERY_URL="https://pay.ipaynow.cn";//正式查询接口地址

        // const TRADE_URL="https://p.ipaynow.cn";//测试交易接口地址
        // const QUERY_URL="https://p.ipaynow.cn";//测试查询接口地址

        const TRADE_FUNCODE="WP001";
        const QUERY_FUNCODE="MQ002";
        const NOTIFY_FUNCODE="N001";
        const FRONT_NOTIFY_FUNCODE="N002";
        const TRADE_TYPE="01";
        const TRADE_CURRENCYTYPE="156";
        const TRADE_CHARSET="UTF-8";
        const TRADE_DEVICE_TYPE="0601";
        const TRADE_SIGN_TYPE="MD5";
        const TRADE_QSTRING_EQUAL="=";
        const TRADE_QSTRING_SPLIT="&";
        const TRADE_FUNCODE_KEY="funcode";
        const TRADE_DEVICETYPE_KEY="deviceType";
        const TRADE_SIGNTYPE_KEY="mhtSignType";
        const TRADE_SIGNATURE_KEY="mhtSignature";
        const SIGNATURE_KEY="signature";
        const SIGNTYPE_KEY="signType";
        const VERIFY_HTTPS_CERT=false;
    }