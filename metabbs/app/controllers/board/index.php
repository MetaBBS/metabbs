<?php
permission_required('list', $board);

$style = $board->get_style();
$template = $style->get_template('list');
$template->set('board', $board);

if (isset($_GET['search'])) {
	// backward compatibility
	$_GET['search']['keyword'] = $_GET['search']['text'];
	unset($_GET['search']['text']);
	redirect_to(query_string_for($_GET['search']));
}

$finder = new PostFinder($board);
$board->finder = $finder;
$finder->set_page(get_requested_page());
$finder->get_post_body = $style->skin->get_option('get_body_in_the_list', true);

if (isset($_GET['keyword']) && trim($_GET['keyword'])) {
	$keyword = $_GET['keyword'];
	$finder->set_keyword($keyword);
	$template->set('keyword', $keyword);
	foreach (array('title', 'body', 'comment') as $key) {
		if (isset($_GET[$key]) && $_GET[$key]) {
			$finder->add_condition($key);
			$template->set($key.'_checked', 'checked="checked"');
		} else {
			$template->set($key.'_checked', '');
		}
	}
} else {
	$template->set('keyword', '');
	$template->set('title_checked', 'checked="checked"');
	$template->set('body_checked', '');
	$template->set('comment_checked', '');
}

if ($board->use_category) {
	$categories = $board->get_categories();
	$un = new UncategorizedPosts($board);
	if ($un->exists())
		array_unshift($categories, $un);
	$template->set('categories', $categories);
	if (isset($_GET['category']) && $_GET['category'] !== '') {
		if ($_GET['category'] == 0)
			$category = new UncategorizedPosts($board);
		else
			$category = Category::find($_GET['category']);
		$finder->set_category($category);
		$template->set('category', $category);
	}
}
$posts = $finder->get_posts();
apply_filters_array('PostList', $posts);

$template->set('posts', $posts);
$template->set('post_count', $count = $board->get_post_count());
$template->set('posts_count', $count); // backward compatibility
$template->set('link_rss', url_for($board, 'rss'));
$template->set('link_new_post', $account->has_perm('write', $board) ? url_for($board, 'post', get_search_params()) : null);
$template->set('admin', $account->has_perm('admin', $board));

$count = $finder->get_post_count();
$page_count = $count ? ceil($count / $board->posts_per_page) : 1;
$page = get_requested_page();
$prev_page = $page - 1;
$next_page = $page + 1;
$page_group_start = $page - 5;
if ($page_group_start < 1) $page_group_start = 1;
$page_group_end = $page + 5;
if ($page_group_end > $page_count) $page_group_end = $page_count;

$template->set('link_prev_page', $prev_page > 0 ? url_for_page($prev_page) : null);
$template->set('link_next_page', $next_page <= $page_count ? url_for_page($next_page) : null);
$pages = array();	
if ($page_group_start > 1) {
	$pages[] = link_to_page(1);
	if ($page_group_start > 2) $pages[] = '...';
}
for ($p = $page_group_start; $p <= $page_group_end; $p++) {
	if ($p == $page) $pages[] = '<span class="here">'.link_to_page($p).'</span>';
	else $pages[] = link_to_page($p);
}
if ($page_group_end != $page_count) {
	if ($page_group_end < ($page_count - 1)) $pages[] = '...';
	$pages[] = link_to_page($page_count);
}
$template->set('pages', $pages);
?>