<?php
$comment = new Comment($_POST['comment']);
if ($account->level < $board->perm_comment) {
	redirect_to(url_for($post));
}
$comment->user_id = $account->id;
if (!$account->is_guest()) {
	$comment->name = $account->name;
}

apply_filters('PostComment', $comment);

$post->add_comment($comment);
if ($_POST['ajax']) {
	$comment->name = stripslashes($comment->name);
	$comment->body = stripslashes($comment->body);
	include("skins/$board->skin/_comment.php");
	exit;
} else {
	redirect_to(url_for($post));
}
?>
