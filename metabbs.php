<?php
require 'core/common.php';

$dispatcher = new Dispatcher($_SERVER['PATH_INFO']);
$params = $dispatcher->params;
$routes = $dispatcher->routes;
if (!$dispatcher) {
	print_notice('Requested URL is not valid.', 'Valid URL format is '.METABBS_HOST_URL.'/<em>controller</em>/<em>action</em><br />If you are administrator, go to <a href="/admin">administration page</a>');
}

$layout = new Layout;
$layout->add_javascript(METABBS_BASE_PATH . 'media/prototype.js');
$title = &$layout->title;
$view = DEFAULT_VIEW;

import_enabled_plugins();

if ($account->is_guest() && using_openid() && cookie_is_registered('openid')
		&& $routes['controller'] != 'openid') {
	redirect_to(url_with_referer_for('openid', 'login').'&openid_identifier='.urlencode(cookie_get('openid')));
}

@include $dispatcher->get_container_path();
@include $dispatcher->get_controller_path();
//$action_dir = 'app/controllers/' . $routes['controller'];
if (!run_custom_handler($routes['controller'], $routes['action'])) {
	$found = @include $dispatcher->get_action_path();
	if (!$found) {
		header('HTTP/1.1 404 Not Found');
		print_notice(i('Page not found'), i('The requested URL was not found on this server.'));
	}
}
if (isset($board)) {
	$style = $board->get_style();
	$style_dir = $style->get_path();
	$_skin_dir = $style->skin->get_path();
	$skin_dir = METABBS_BASE_PATH.$_skin_dir;
} else {
	$_skin_dir = 'skins/default';
	$style_dir = METABBS_BASE_PATH.'styles/default';
}

if ($view == ADMIN_VIEW) {
	$layout->add_stylesheet(METABBS_BASE_PATH . 'media/style.css');
	$layout->add_javascript(METABBS_BASE_PATH . 'media/admin.js');
	$layout->header = $layout->footer = '';
} else {
	if (isset($style)) {
		$css = 'styles/'.$style->name.'/style.css';
		if (file_exists($css))
			$layout->add_stylesheet($style_dir.'/style.css?'.filemtime($css));
		$layout->wrap("<div id=\"meta\">\n", "</div>\n");
	} else {
		$layout->wrap("<div id=\"meta\" class=\"theme-only\">\n", "</div>\n");
	}
	$css = 'themes/'.get_current_theme().'/style.css';
	if (file_exists($css))
		$layout->add_stylesheet(METABBS_BASE_PATH . $css);
	$layout->add_javascript(METABBS_BASE_PATH . 'media/script.js');
}

ob_start();
if (isset($template)) {
	$template->set('title', $title); // XXX
	$template->render();
} else include $dispatcher->get_view_path();
$content = ob_get_contents();
ob_end_clean();

if (!is_xhr()) {
	include get_header_path();
	echo $layout->header;
	if ($view == DEFAULT_VIEW && isset($board) && $board->header)
		include $board->header;
	echo $content;
	if ($view == DEFAULT_VIEW && isset($board) && $board->footer)
		include $board->footer;
	echo $layout->footer;
	include get_footer_path();
} else {
	echo $content;
}
?>
