<?php
$filters = array();
$handlers = array();
$__plugins = array();
$__admin_menu = array();

/**
 * 플러그인 등록하기
 * @param $name 플러그인의 이름
 */
function register_plugin($name) {
	global $__plugins;
	$plugin = Plugin::find_by_name($name);
	if ($plugin->enabled) {
		$plugin->on_init();
	}
	$__plugins[$name] = $plugin;
	ksort($__plugins);
}

/**
 * 관리자 메뉴 추가하기
 * @param $url 추가할 url
 * @param $text 화면에 표시할 문자열
 */
function add_admin_menu($url, $text) {
	global $__admin_menu;
	$__admin_menu[] = link_text($url, $text);
}

// Filter API
/**
 * 필터 충돌시 겹쳐쓴다.
 */
define('META_FILTER_OVERWRITE', 1);

/**
 * 필터 충돌시 앞에 쓴다.
 */
define('META_FILTER_PREPEND', 2);

/**
 * 필터 충돌시 뒤에 쓴다.
 */
define('META_FILTER_APPEND', 3);

/**
 * 충돌시 콜백 함수를 호출한다.
 */
define('META_FILTER_CALLBACK', 4);

/**
 * 필터를 추가한다.
 * @param $event 추가할 이벤트
 * @param $callback 이벤트에 호출할 함수
 * @param $priority 우선 순위
 * @param $collision 충돌시 정책
 * @param $fallback 충돌시 폴백을 지정하여 폴백이 해결한다.
 * 충돌시 정책이 META_FILTER_CALLBACK 일 경우만 사용.
 * @see META_FILTER_OVERWRITE 충돌 정책들
 * @see META_FILTER_PREPEND
 * @see META_FILTER_APPEND
 * @see META_FILTER_CALLBACK
 */
function add_filter($event, $callback, $priority, $collision = META_FILTER_OVERWRITE, $fallback = null) {
	global $filters;
	if (@array_key_exists($priority, $filters[$event])) {
		switch ($collision) {
			case META_FILTER_OVERWRITE:
				$filters[$event][$priority] = $callback;
			break;
			case META_FILTER_PREPEND:
				$priority--;
				add_filter($event, $callback, $priority, $collision);
			break;
			case META_FILTER_APPEND:
				$priority++;
				add_filter($event, $callback, $priority, $collision);
			break;
			case META_FILTER_CALLBACK:
				if ($fallback) $fallback();
			break;
		}
	} else {
		$filters[$event][$priority] = $callback;
	}
}

/**
 * 필터를 제거한다.
 * @param $event 이벤트
 * @param $callback 등록한 함수
 */
function remove_filter($event, $callback) {
	global $filters;
	$key = array_search($callback, $filters[$event]);
	unset($filters[$event][$key]);
}

/**
 * 필터를 적용한다.
 * 해당 이벤트에 등록된 이벤트를 모두 실행
 * @param $event 해당 이벤트
 * @param $model 적용할 모델
 */
function apply_filters($event, &$model) {
	global $filters;
	if (isset($filters[$event])) {
		ksort($filters[$event]);
		foreach ($filters[$event] as $callback) {
			call_user_func_array($callback, array(&$model));
		}
	}
}

/**
 * 다수의 모델에 대해 필터를 적용한다.
 * @param $event 해당 이벤트
 * @param $array 적용 모델을 담고 있는 배열
 */
function apply_filters_array($event, &$array) {
	foreach (array_keys($array) as $key) {
		apply_filters($event, $array[$key]);
	}
}

// Handler API
// TODO: 한 액션을 여러 핸들러가 처리해야 할 때
/**
 * 디폴트를 후킹으로 핸들러를 추가한다.
 * @param $controller 컨트롤러
 * @param $action 액션
 * @param $callback 호출할 함수
 * @param type 핸들러 타입.
 * hook이 디폴트인데 before가 이용가능하다.
 */
function add_handler($controller, $action, $callback, $type = 'hook') {
	global $filters;
	$filters[$controller][$action][$type] = $callback;
}

/**
 * 등록된 후킹 핸들러를 실행한다.
 * @param $controller 컨트롤러
 * @param $action 액션
 * @return 후킹 핸들러가 등록된 경우 실행하고 true를 아닌경우 false.
 */
function run_hook_handler($controller, $action) {
	global $filters;
	run_before_handler($controller, $action);
	if (isset($filters[$controller][$action]['hook'])) {
		call_user_func($filters[$controller][$action]['hook']);
		return true;
	} else {
		return false;
	}
}

/**
 * 해당 액션보다 먼저 실행하도록 된 핸들러를 실행한다.
 * @param $controller 컨트롤러
 * @param $action 액션
 */
function run_before_handler($controller, $action) {
	global $filters;
	if (isset($filters[$controller][$action]['before'])) {
		call_user_func($filters[$controller][$action]['before']);
	}
}

function get_plugins() {
	$dir = METABBS_DIR . '/plugins/';
	$dp = opendir($dir);
	$plugins = array();
	while ($file = readdir($dp)) {
		list($name, ) = explode('.', $file);
		import_plugin($name);
	}
	closedir($dp);
	return $GLOBALS['__plugins'];
}

function import_plugin($plugin) {
	if (file_exists(METABBS_DIR."/plugins/$plugin.php")) {
		include_once("plugins/$plugin.php");
	} else if (file_exists(METABBS_DIR."/plugins/$plugin/plugin.php")) {
		include_once("plugins/$plugin/plugin.php");
	}
}

function import_enabled_plugins() {
	import_plugin('_base');
	foreach (get_enabled_plugins() as $plugin) {
		import_plugin($plugin->name);
	}
}
?>
