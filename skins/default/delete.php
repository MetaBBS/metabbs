<h2><?=i('Delete')?></h2>
<form method="post">
<p>
	<? if ($ask_password) { ?>
	<?=i("Password")?>: <input type="password" name="password" />
	<? } ?>
	<?=submit_tag(i("Delete"))?>
</p>
</form>
