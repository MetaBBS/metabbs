<?php
if ($board->perm_write > $account->level) {
	access_denied();
}
if (is_post()) {
	$post = new Post($_POST['post']);
	$post->board_id = $board->id;
	define('SECURITY', 1);
	include 'actions/post/save.php';
} else {
	$post = new Post;
	$post->name = $name;
	$nav[] = link_to(i("List"), $board);
	render('write');
}
?>