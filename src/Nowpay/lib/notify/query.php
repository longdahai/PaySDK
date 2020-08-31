<?php
    require_once '../Config.php';
    require_once '../signaTure.php';
    require_once './postRequest.php';
    /**
     * 
     * @author Jupiter
     * 
     * 查询接口类
     * 通过订单号查询支付结果情况
     * 说明:以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己的需要，按照技术文档编写，并非一定要使用该代码。该代码仅供参考
     */
    class QueryOrder {
        public function main() {
            $req=array();
            $req["funcode"] = 'MQ002';
            $req['version'] = '1.0.0';
            $req['deviceType'] = '0601';
            $req["appId"]= '147868777472129'; //订单对应的APPID
            $req["mhtOrderNo"] = '2017121411360623411';//商户欲查询交易订单号
            $req["mhtCharset"]=Config::TRADE_CHARSET;
            $req["mhtSignType"]=Config::TRADE_SIGN_TYPE;
                       
            $resp=array();
            //请求查询接口
            $info = new SignaTure;
            $req_str = $info -> getToStr($req, '1FZMAlAplOTamX6OARDVV8hrswhbGEVg');

            $post = new PostRequest;
            $res = $post -> post(Config::TRADE_URL, $req_str);
            return $res;
        }
    }
    $p=new QueryOrder();
    echo $p->main();