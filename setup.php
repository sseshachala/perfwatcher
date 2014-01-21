<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<?php
/**
 * Copyright (c) 2011 Cyril Feraudet
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
 * @copyright 2011 Cyril Feraudet
 * @license   http://opensource.org/licenses/mit-license.php
 * @link      http://www.perfwatcher.org/
 **/ 
?>

<html lang="en-US">
    <head profile="http://www.w3.org/2005/10/profile">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>PerfWatcher installation checking</title>
		<link rel="icon" type="image/ico" href="img/perfwatcher.ico">
	</head>
    <body>
		<ul>
<?php
require 'etc/config.default.php';
require 'lib/common.php';
function printok($txt) { return "<font color='green'>$txt</font>"; }
function printko($txt) { return "<font color='red'>$txt</font>"; }
function printoo($txt) { return "<font color='orange'>$txt</font>"; }

$ok = "Your version of php is >= 5.3.0 (".PHP_VERSION.")";
$oo = "Your version of php is < 5.3.0 (".PHP_VERSION."), so no PHP RRD module, Perfwatcher will use rrdtool in command-line";
echo "<li>".(version_compare(PHP_VERSION, '5.3.0', '>=') ? printok ($ok) : printoo ($oo))."</li>";

$ok = "PHP RRD module is present (".phpversion("rrd").")";
$oo = "PHP RRD module is not present, Perfwatcher will use rrdtool in command-line";
echo "<li>".(version_compare(phpversion("rrd"), '0.0.0', '>=') ? printok ($ok) : printoo ($oo))."</li>";

$ok = "rrdtool is present at $rrdtool";
$ko = "No rrdtool found at $rrdtool please install or modify \$rrdtool in etc/config.php";
echo "<li>".(isset($rrdtool) && file_exists($rrdtool) ? printok ($ok) : printko ($ko))."</li>";

if (isset($rrdtool) && file_exists($rrdtool)) {
	$cmd = "$rrdtool | awk '{print $2; exit(0)}'";
	$rrdtool_version = trim(`$cmd`);
	$ok = "rrdtool version is great than 1.4.0 ($rrdtool_version)";
	$oo = "rrdtool version is less than 1.4.0 ($rrdtool_version). No rrdcached available and some feature will not work.";
	$ko = "Can't retrieve rrdtool version. Check your \$rrdtool in etc/config.php";
	if (!ereg("^[0-9]+(\.[0-9+])*$", $rrdtool_version)) {
		echo  "<li>".printko($ko)."</li>";
	} else if (version_compare($rrdtool_version, '1.4.0', '<')) {
		echo  "<li>".printoo($oo)."</li>";
	} else if (version_compare($rrdtool_version, '1.4.0', '>=')) {
		echo  "<li>".printok($ok)."</li>";
		$rrdcached_ok = true;
	} else {
		echo  "<li>".printko($ko)."</li>";
	}
	
}

if ($rrdcached_ok && isset($rrdcached)) {
		$ok = "rrdcached socket is present at $rrdcached but make sure it writable with group of your webserver";
		$ko = "No rrdcached socket found at $rrdcached please install or modify \$rrdcached in etc/config.php and make sure it writable with group of your webserver";
		echo "<li>".(file_exists($rrdcached) ? printok ($ok) : printko ($ko))."</li>";
}

if (isset($rrdtool) && file_exists($rrdtool) && in_array('--border', $rrdtool_options)) {
	$cmd = "$rrdtool graph --border 1 2>&1";
	$res = trim(`$cmd`);
	if (substr($res, 0, 21) == 'ERROR: unknown option') {
		$options = $rrdtool_options;
		foreach ($options as $key => $val) {
			if ($val == '--border') {
				unset($options[$key]);
				unset($options[$key + 1]);
			}
		}
		$line = implode("', '", $options);
		echo "<li>".printko("Your rrdtool does not support --border option. Please add <i>\$rrdtool_options = array('$line');</i> in etc/config.php")."</li>";
	} else {
		echo "<li>".printok("Your rrdtool support --border option.")."</li>";
	}
}

# Check the database
$ok = "Can connect to '".$db_config{'database'}."' on '".$db_config{'servername'}."' with user '".$db_config{'username'}."'.";
$ko = "Could not connect to database '".$db_config{'database'}."' on '".$db_config{'servername'}."' with user '".$db_config{'username'}."'. You may check the install/* scripts.";
$result_connect = 0;
$result_schema_version = 0;
$result_tree = 0;
$out="";
$dbtest = new _database($db_config);
if ($dbtest->connect()) {
		$result_connect = 1;
		$dbtest->prepare("SELECT value FROM config WHERE confkey='schema_version'");
		$dbtest->execute();
		if($dbtest->nextr()) {
				$result_connect = 1;
				$out = $dbtest->get_row('assoc');
				$dbtest->free();
		}
		if($out && (isset($out['value'])) && ($out['value'] == $database_schema_version)) {
				$result_schema_version = 1;
				$dbtest->prepare("SELECT id,title,parent_id FROM tree WHERE pwtype='container'");
				$dbtest->execute();
				if($dbtest->nextr()) {
						$result_connect = 1;
						$out = $dbtest->get_row('assoc');
						$dbtest->destroy();
				}
				if($out && (isset($out['title'])) && (isset($out['parent_id'])) && ($out['parent_id'] == 1)) {
						$result_tree = 1;
				}
		}
}
echo "<li>Database connection : ".($result_connect ? printok($ok) : printko ($ko))."</li>";

$ok = "Database schema version is correct";
$ko = "Database schema version is incorrect or missing in '".$db_config{'database'}.".config'. You may check the install/* scripts.";
echo "<li>Database schema version : ".($result_schema_version ? printok($ok) : printko ($ko))."</li>";
$ok = "Found a root drive in the tree";
$ko = "Could not find the root of the tree (pwtype container) in '".$db_config{'database'}.".tree'. You may check the install/* scripts.";
echo "<li>Database contents : ".($result_tree ? printok($ok) : printko ($ko))."</li>";

?>
		</ul>
    </body>
</html>
