<?php # vim: set filetype=php fdm=marker sw=4 ts=4 et : 
/**
 * Copyright (c) 2013 Cyril Feraudet
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category  Monitoring
 * @author    Cyril Feraudet <cyril@feraudet.com>
 * @copyright 2013 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 

$post_request = file_get_contents('php://input');
$collectd_source = get_arg('cdsrc', 0, 0, "", __FILE__, __LINE__);
if(isset($collectd_source) && $collectd_source) {
    if(isset($collectd_sources[$collectd_source])) {
        $url_jsonrpc = $collectd_sources[$collectd_source]['jsonrpc'];
        $proxy_jsonrpc = isset($collectd_sources[$collectd_source]['proxy'])?$collectd_sources[$collectd_source]['proxy']:null;
    } else {
        pw_error_log("Some node in your tree as Collectd Source set as '$collectd_source' but there is no such Source in your configuration. "
                ."Using default '$collectd_source_default' source instead. "
                ."Check your database (try [SELECT * FROM tree WHERE datas LIKE '%$collectd_source%';]) and your configuration file",  __FILE__, __LINE__);
        pw_error_log("\$post_request='$post_request'",  __FILE__, __LINE__);
        pw_error_log("\$_GET='".print_r($_GET, 1)."'",  __FILE__, __LINE__);
        pw_error_log("\$_POST='".print_r($_GET, 1)."'",  __FILE__, __LINE__);
        $url_jsonrpc = $collectd_sources[$collectd_source_default]['jsonrpc'];
        $proxy_jsonrpc = isset($collectd_sources[$collectd_source_default]['proxy'])?$collectd_sources[$collectd_source_default]['proxy']:null;
    }
} else {
    pw_error_log("This was called with no/empty \$collectd_source. More information is following.",  __FILE__, __LINE__);
    pw_error_log("\$collectd_source='".(isset($collectd_source)?$collectd_source:"unset")."'",  __FILE__, __LINE__);
    pw_error_log("\$post_request='$post_request'",  __FILE__, __LINE__);
    pw_error_log("\$_GET='".print_r($_GET, 1)."'",  __FILE__, __LINE__);
    pw_error_log("\$_POST='".print_r($_GET, 1)."'",  __FILE__, __LINE__);
    pw_error_log("Please tell us about this problem.",  __FILE__, __LINE__);
    $url_jsonrpc = $collectd_sources[$collectd_source_default]['jsonrpc'];
    $proxy_jsonrpc = isset($collectd_sources[$collectd_source_default]['proxy'])?$collectd_sources[$collectd_source_default]['proxy']:null;
}
putenv('http_proxy');
putenv('https_proxy');
$ch = curl_init($url_jsonrpc);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, $proxy_jsonrpc == null ? FALSE : TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_request);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($post_request))
        );
if($result = curl_exec($ch)) {
	echo $result;
} else {
    exit_error(curl_error($ch));
}

function exit_error($error) {
    echo json_encode(array("error" => $error, "data" => array())); exit;
}
