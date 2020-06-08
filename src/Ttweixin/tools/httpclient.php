<?php
include 'SignAndCheck.php';
function do_post_request($url, $data, $optional_headers = null) {
    $params = array (
        'http' => array (
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    if ($optional_headers !== null) {
        $params ['http'] ['header'] = $optional_headers;
    }
    $ctx = stream_context_create ( $params );
    $fp = @fopen ( $url, 'rb', false, $ctx );
    if (! $fp) {
        throw new Exception ( "Problem with $url, $php_errormsg" );
    }
    $response = @stream_get_contents ( $fp );
    if ($response === false) {
        throw new Exception ( "Problem reading data from $url, $php_errormsg" );
    }
    
    $obj = json_decode($response, TRUE);
    $signature = $obj['sign'];
    $obj['sign'] = '';
    $obj['sign_type'] = '';
    $data = getStr($obj);
    $re = verify($data, $signature, __DIR__ . '/../cer/TTFPublicKey.cer');
    if($re){
        return $obj;
    }
    return json_decode("{'resp_code':'888888','resp_msg':'验签失败'}", TRUE);
}
?>