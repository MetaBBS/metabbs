<?php

// $Header: /cvsroot/rubybbs/rubybbs/libs/Core.php,v 1.11 2005/02/03 12:31:16 ditto Exp $

define("RUBYBBS_VERSION", "0.3");

class Core {
	/**
	* ���� �޽��� ��� (static)
	* @param $msg ���� �޽���
	*/
	function Error($msg) {
		print "<p>$msg</p>";
		exit;
	}
	/**
	* ������ �̵� (static)
	* @param $url �̵��� URL
	*/
	function Redirect($url) {
		if (!headers_sent()) {
			header("Location: $url");
		} else {
			$url = htmlspecialchars($url);
			print "<meta http-equiv=\"refresh\" content=\"0;url=$url\" />";
		}
		exit;
	}
}

@import_request_variables('gpc');

@include 'config.php';

?>
