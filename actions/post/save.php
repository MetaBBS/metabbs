<?php
if (!defined('SECURITY')) {
	return;
}
if (!$account->is_guest()) {
	$post->user_id = $account->id;
	$post->name = $account->name;
}

apply_filters('PostSave', $post);

if ($post->exists()) {
	$post->update();
} else {
	$board->add_post($post);
}
if (isset($_FILES['upload'])) {
	$upload = $_FILES['upload'];
	if (!file_exists('data/uploads')) {
		@mkdir('data/uploads', 0777);
	}
	foreach ($upload['name'] as $key => $filename) {
		if (!$filename || $upload['size'][$key] == 0) {
			continue;
		}
		$attachment = new Attachment;
		$attachment->filename = $filename;
		$post->add_attachment($attachment);
		move_uploaded_file($upload['tmp_name'][$key], 'data/uploads/' . $attachment->id);
	}
}
if ($post->id) {
	redirect_to(url_for($post));
} else {
	redirect_to(url_for($board));
}
?>
