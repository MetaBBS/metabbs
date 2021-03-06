<?php
global $layout, $routes, $params;

if (isset($board)) {
	$admin = $account->has_perm('admin', $board);
	$board->title = $board->get_title();
}
$guest = $account->is_guest();
if ($guest) {
	$link_login = url_with_referer_for('account', 'login');
	$link_signup = url_with_referer_for('account', 'signup');
} else {
	$link_logout = url_with_referer_for('account', 'logout');
	$link_account = url_with_referer_for('account', 'edit');
	if ($account->is_admin()) {
		$link_admin = url_for('admin');
	} else {
		$link_admin = null;
	}
}

if ($this->view == 'write') {
	if ($board->use_category)
		$categories = $board->get_categories();
	else
		$categories = null;
	$un = new UncategorizedPosts($board);
	if ($board->have_empty_item())
		array_unshift($categories, $un);
	
	$notice_checked = $post->notice ? 'checked="checked"' : '';
	$secret_checked = $post->secret ? 'checked="checked"' : '';
	$editing = $routes['action'] == 'edit';
	$post->author = htmlspecialchars($post->name);
	$post->title = htmlspecialchars($post->title);
	$post->body = htmlspecialchars($post->body);
	$additional_fields = array();
	foreach ($extra_attributes as $attr) {
		$attr->name = htmlspecialchars($attr->name);
		$attr->output = $attr->render($post->get_attribute($attr->key));
		$additional_fields[] = $attr;
	}
	if ($post->exists()) {
		$attachments = $post->get_attachments();
	} else {
		$attachments = array();
	}
	$uploadable = $board->use_attachment;
	$upload_limit = get_upload_size_limit();
}

// for list.php
if (isset($categories)) {
	foreach ($categories as $k => $c) {
		if (isset($_GET['category']) && $_GET['category'] == $c->id ||
			isset($post) && $post->category_id == $c->id) {
			$categories[$k]->selected = 'selected="selected"';
		} else {
			$categories[$k]->selected = '';
		}
		$categories[$k]->name = htmlspecialchars($c->name);
		$categories[$k]->url = url_for($board, null, array('category' => $c->id));
		$categories[$k]->post_count = $c->get_post_count();
	}
} else {
	$categories = null;
}

$taggable = $board->use_tag();
$form_id = '';
if ($this->view == 'view') {
	$layout->add_meta('Author', htmlspecialchars(isset($post->name_orig) ? $post->name_orig : $post->name));
	$form_id = 'comment-form';
	if ($taggable) {
		$tags = $post->get_tags();
		if ($tags) {
			foreach ($tags as $k => $v) {
				$tags[$k]->name = htmlspecialchars($v->name);
				$tags[$k]->url = url_for($board, null, array('tag' => 1, 'keyword' => urlencode($v->name)));
				$tags[$k]->last = false;
			}
			$tags[$k]->last = true;
		}
	}
}
if (isset($post) && !$board->use_trackback) {
	$post->trackback_url = null;
	$trackbacks = array();
}
if (isset($trackbacks)) {
	foreach ($trackbacks as $k => $v) {
		$trackbacks[$k]->title = htmlspecialchars($v->title);
		$trackbacks[$k]->blog_name = htmlspecialchars($v->blog_name);
		if ($admin)
			$trackbacks[$k]->delete_url = url_for($v, 'delete');
		else
			$trackbacks[$k]->delete_url = null;
	}
}
if (isset($newer_post) && !$newer_post->exists()) 
	$newer_post = null;
if (isset($older_post) && !$older_post->exists()) 
	$older_post = null;

if (isset($attachments)) {
	foreach ($attachments as $k => $v) {
		modern_attachment_filter($attachments[$k]);
	}
}
if (isset($comments)) {
	function flatten_comments($comments, $parent = 0, $depth = 0) {
		$_comments = array();
		foreach ($comments as $comment) {
			if ($comment->parent == $parent) {
				$comment->depth = $depth;
				$_comments[] = $comment;
				$children = flatten_comments($comments, $comment->id, $depth + 1);
				$_comments = array_merge($_comments, $children);
			}
		}
		return $_comments;
	}
	$comments = flatten_comments($comments);
}
/*$comment_url = null;
if (isset($post) && $post->exists() && $account->has_perm('comment', $post)) {
	$comment_url = url_for($post, 'comment');
}

if ($routes['controller'] == 'comment') {
	$comment_url = url_for($GLOBALS['comment'], $routes['action']);
	if ($routes['action'] == 'edit') {
		$comment_author = htmlspecialchars($comment->name);
		$comment_body = htmlspecialchars($comment->body);
	}
}
if (isset($comment_url) && !isset($comment_author)) {
	$comment_author = htmlspecialchars(cookie_get('name'));
	$comment_body = "";
}*/
if (!isset($signature)) $signature = '';
if (!isset($link_cancel)) $link_cancel = '';
if (isset($keyword)) $keyword = htmlspecialchars($keyword);

if ($this->view == 'reply_comment')
	$form_id = 'reply-form' . $comment->id . rand(0, 10);
else if ($this->view == 'edit_comment')
	$form_id = 'edit-form';

if (isset($board))
	$link_rss = url_for($board, 'rss');

$css = 'styles/'.$style->name.'/style.css';
if (file_exists($css))
	$layout->add_stylesheet($style_dir.'/style.css?'.filemtime($css));
$layout->wrap("<div id=\"meta\">\n", "</div>\n");
?>
