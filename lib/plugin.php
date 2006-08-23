<?php
$filters = array();
$handlers = array();

// Filter API
// TODO: ���� �켱������ ���� �� ���Ͱ� ��ϵǾ��� ��
function add_filter($event, $callback, $priority) {
	global $filters;
	$filters[$event][$priority] = $callback;
}
function remove_filter($event, $callback) {
	global $filters;
	$key = array_search($callback, $filters[$event]);
	unset($filters[$event][$key]);
}
function apply_filters($event, &$model) {
	global $filters;
	if (isset($filters[$event])) {
		ksort($filters[$event]);
		foreach ($filters[$event] as $callback) {
			call_user_func_array($callback, array(&$model));
		}
	}
}
function apply_filters_array($event, &$array) {
	foreach (array_keys($array[$event]) as $key) {
		apply_filters($array[$event][$key]);
	}
}

// Handler API
// TODO: �� �׼��� ���� �ڵ鷯�� ó���ؾ� �� ��
function add_handler($controller, $action, $callback, $type = 'hook') {
	global $filters;
	$filters[$controller][$action][$type] = $callback;
}
function run_hook_handler($controller, $action) {
	global $filters;
	if (isset($filters[$controller][$action]['hook'])) {
		call_user_func($filters[$controller][$action]['hook']);
		return true;
	} else {
		return false;
	}
}

function say_hello_handler() {
	echo "<h1>Hello, world!</h1>";
	echo "<p>This is MetaBBS Plugin API test.</p>";
}
add_handler('say', 'hello', 'say_hello_handler');
?>
