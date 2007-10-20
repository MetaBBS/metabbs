<?php
permission_required('write', $board);

if (is_post()) {
	if (!$account->has_perm('admin', $board)) {
		unset($_POST['post']['notice']);
	}
	$post = new Post(@$_POST['post']);
	if (!$account->is_guest()) {
		$post->user_id = $account->id;
		$post->name = $account->name;
	}
	define('SECURITY', 1);
	include 'app/controllers/post/save.php';
} else {
	$post = new Post;
	$post->name = cookie_get('name');
	if (isset($_GET['search'])) {
		$post->category_id = $_GET['search']['category'];
	}

	$template = get_template($board, 'write');
	$template->set('board', $board);
	$template->set('post', $post);
	$template->set('extra_attributes', $extra_attributes);
	$template->set('link_list', url_for($board));
	$template->set('link_cancel', '');
}
?>