<?php
require_once 'config.php';
$config = new Config('metabbs.conf.php');

$backend = $config->get('backend');
if (!$backend) $backend = 'mysql';

$model_dir = 'model';
require_once 'model.php';
require_once 'backends/' . $backend . '/backend.php';
require_once 'model/board.php';

require_once 'template.php';
require_once 'uri_manager.php';
require_once 'page.php';
require_once 'account.php';

function _addslashes(&$str) {
	$str = addslashes($str);
}
function addslashes_deep(&$array) {
	if (is_array($array)) {
		@array_walk($array, '_addslashes');
	}
}
?>
