<?php
header("Content-type: text/html; charset=utf-8");

    require_once '../controller/notify.php';
    /**
    * @author Jupiter
    *
    * 异步通知接口
    *
    * 用于被动接收中小开发者支付系统发过来的通知信息，并对通知进行验证签名，
    * 签名验证通过后，商户可对数据进行处理。
    *
    * 说明:以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己的需要，按照技术文档编写，并非一定要使用该代码。该代码仅供参考
    */
    $request=file_get_contents('php://input');
    $notice = new Notify;
    $code = $notice -> verification($request, '1FZMAlAplOTamX6OARDVV8hrswhbGEVg');
    if($code) {
        //处理验签成功后的逻辑
    } else {
        //处理验签失败后的逻辑
    }
    echo 'success=Y';
    
