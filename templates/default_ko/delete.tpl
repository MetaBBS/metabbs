{include file="header.tpl"}

<div id="rubybbs">

<form method="post" action="delete.php?bid={$bid}&amp;id={$id}">
��ȣ: <input type="password" name="passwd" class="text" /> <input type="submit" value="�����" class="submit" />
</form>

<ul id="nav">
	<li><a href="index.php?bid={$bid}&amp;page={$page}">��Ϻ���</a></li>
	<li><a href="post.php?bid={$bid}&amp;page={$page}">�۾���</a></li>
	<li><a href="index.php?bid={$bid}&amp;page={$page}&amp;id={$id}">���ư���</a></li>
	<li><a href="admin.php">����</a></li>
</ul>
</div>

{include file="footer.tpl"}
