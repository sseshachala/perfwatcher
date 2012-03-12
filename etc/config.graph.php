<?php // vim:fenc=utf-8:filetype=php:ts=4
/**
 * Configuration file for Collectd graph browser
 */
require 'etc/config.default.php';
// Array of paths when collectd's rrdtool plugin writes RRDs
$config['datadirs']   = array($rrds_path);
// Width of graph to be generated by rrdgraph
$config['rrd_width']  = 600;
// Height of graph to be generated by rrdgraph
$config['rrd_height'] = 120;
// List of supported timespans (used for period drop-down list)
$config['timespan']   = array(
	array('name'=>'hour',  'label'=>'past hour',  'seconds'=>3600),
	array('name'=>'day',   'label'=>'past day',   'seconds'=>86400),
	array('name'=>'week',  'label'=>'past week',  'seconds'=>604800),
	array('name'=>'month', 'label'=>'past month', 'seconds'=>2678400),
	array('name'=>'year',  'label'=>'past year',  'seconds'=>31622400));
// Interval at which values are collectd (currently ignored)
$config['rrd_interval']  = 10;
// Average rows/rra (currently ignored)
$config['rrd_rows']      = 2400;
// Additional options to pass to rrdgraph
$config['rrd_opts']      = array();
// Predefined set of colors for use by collectd_draw_rrd()
$config['rrd_colors']    = array(
		 'h_1'=>'F7B7B7',  'f_1'=>'FF0000', // Red
		 'h_2'=>'B7EFB7',  'f_2'=>'00E000', // Green
		 'h_3'=>'B7B7F7',  'f_3'=>'0000FF', // Blue
		 'h_4'=>'F3DFB7',  'f_4'=>'F0A000', // Yellow
		 'h_5'=>'B7DFF7',  'f_5'=>'00A0FF', // Cyan
		 'h_6'=>'DFB7F7',  'f_6'=>'A000FF', // Magenta
		 'h_7'=>'FFC782',  'f_7'=>'FF8C00', // Orange
		 'h_8'=>'DCFF96',  'f_8'=>'AAFF00', // Lime
		 'h_9'=>'83FFCD',  'f_9'=>'00FF99',
		'h_10'=>'81D9FF', 'f_10'=>'00B2FF',
		'h_11'=>'FF89F5', 'f_11'=>'FF00EA',
		'h_12'=>'FF89AE', 'f_12'=>'FF0051',
		'h_13'=>'BBBBBB', 'f_13'=>'555555',
	);
/*
 * URL to collectd's unix socket (unixsock plugin)
 *  enabled:  'unix:///var/run/collectd/collectd-unixsock'
 *  disabled: null
 */
$config['collectd_sock'] = null;
/*
 * Path to TTF font file to use in error images
 * (fallback when file does not exist is GD fixed font)
 */
//$config['error_font']    = '/usr/share/fonts/corefonts/arial.ttf';
$config['error_font']    = '/usr/share/fonts/liberation/LiberationSans-Regular.ttf';

/*
 * Constant defining full path to rrdtool
 */
define('RRDTOOL', '/usr/bin/rrdtool');

?>
