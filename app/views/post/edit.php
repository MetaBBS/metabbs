<?php
$template = get_template($board, 'write');
$template->set('board', $board);
$template->set('post', $post);
$template->set('extra_attributes', $extra_attributes);
$template->set('link_list', url_for($board));
$template->set('link_cancel', url_for($post));
$template->set('preview', isset($preview) ? $preview : null);
$template->render();
