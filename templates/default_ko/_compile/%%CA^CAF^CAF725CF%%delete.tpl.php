<?php /* Smarty version 2.6.6, created on 2005-01-17 17:45:49
         compiled from delete.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div id="rubybbs">

<form method="post" action="delete.php?bid=<?php echo $this->_tpl_vars['bid']; ?>
&amp;id=<?php echo $this->_tpl_vars['id']; ?>
">
��ȣ: <input type="password" name="passwd" class="text" /> <input type="submit" value="�����" class="submit" />
</form>

<ul id="nav">
	<li><a href="index.php?bid=<?php echo $this->_tpl_vars['bid']; ?>
&amp;page=<?php echo $this->_tpl_vars['page']; ?>
">��Ϻ���</a></li>
	<li><a href="post.php?bid=<?php echo $this->_tpl_vars['bid']; ?>
&amp;page=<?php echo $this->_tpl_vars['page']; ?>
">�۾���</a></li>
	<li><a href="index.php?bid=<?php echo $this->_tpl_vars['bid']; ?>
&amp;page=<?php echo $this->_tpl_vars['page']; ?>
&amp;id=<?php echo $this->_tpl_vars['id']; ?>
">���ư���</a></li>
	<li><a href="admin.php">����</a></li>
</ul>
</div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>