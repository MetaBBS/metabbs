<?php
if (!isset($id)) {
	print_notice('No user id', 'Please append the user id.');
}
$user = new User($id);
if (!$user->exists()) {
	print_notice('User not found', "User #$id is not exist.");
}
require_once 'lib/page.php';
?>