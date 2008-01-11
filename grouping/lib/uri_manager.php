<?php
if (!defined('METABBS_BASE_URI')) {
	if (isset($_SERVER['REDIRECT_URL'])) {
		define('METABBS_BASE_URI', METABBS_BASE_PATH);
	} else {
		define('METABBS_BASE_URI', $_SERVER['SCRIPT_NAME'] . '/');
	}
}

/**
 * 해당 컨트롤러와 액션에 맞는 url의 내용을 생성한다. 이 함수가 구체적인 url을 만든다.
 * @param $controller 컨트롤러
 * @param $action 액션
 * @param $params 전달할 패러미터
 * @return 생성된 URL 주소의 문자열
 */
function _url_for($controller, $action = null, $params = null) {
	$url = METABBS_BASE_URI;
	if (is_a($controller, 'Model')) {
		$url .= $controller->model . '/';
		if ($action) $url .= $action . '/';
		$url .= $controller->get_id();
	} else {
		$url .= $controller . '/';
		if ($action) $url .= $action . '/';
	}

	if ($params) $url .= query_string_for($params);

	return $url;
}

function query_string_for($params) {
	$_params = array();
	foreach ($params as $key => $value)
		$_params[] = "$key=$value";
	return '?' . implode('&', $_params);
}

/**
 * 전체 url 주소를 생성한다.
 * @param $controller 컨트롤러 명
 * @param $action 액션 명
 * @return http 프로토콜에 맞춘 전체 url
 */
function full_url_for($controller, $action = '') {
	return 'http://'.$_SERVER['HTTP_HOST']._url_for($controller, $action);
}

/**
 * _url_for를 호출하여 일반적인 주소를 만든다.
 * @param $controller 컨트롤러 명
 * @param $action 액션 명
 * @params 패러미터
 * @return 생성된 url 주소
 */
function url_for($controller, $action = null, $params = array()) {
	set_default_params($params);
	return _url_for($controller, $action, $params);
}

function set_default_params(&$params) {
	if (isset($_GET['search']) && $_GET['search']) {
		foreach ($_GET['search'] as $k => $v) {
			if ($v) $params["search[$k]"] = urlencode($v);
		}
	}
}

/**
 * 리퍼러 정보를 포함한 url을 생성한다.
 * @param $controller 컨트롤러 명
 * @param $action 액션 명칭
 * @param $params 패러미터
 * @return 리퍼러 정보를 포함한 url의 스트링
 */
function url_with_referer_for($controller, $action = null, $params = array()) {
	if (!isset($_GET['url'])) {
		$params['url'] = urlencode($_SERVER['REQUEST_URI']);
	} else {
		$params['url'] = urlencode($_GET['url']);
	}
	return url_for($controller, $action, $params);
}

/**
 * 지정한 주소로 리다이렉트한다.
 * @param $url 이동할 주소
 */
function redirect_to($url) {
	header('Location: ' . $url);
	exit;
}

/**
 * 리퍼러나 지정된 주소를 기초로 뒤로 돌아간다.
 */
function redirect_back() {
	if (isset($_GET['url'])) {
		redirect_to($_GET['url']);
	} else {
		redirect_to($_SERVER['HTTP_REFERER']);
	}
}
?>