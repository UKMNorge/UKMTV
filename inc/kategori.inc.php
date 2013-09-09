<h2><?= $_GET['sok'] ?> - alle filmer</h2>
<div class="row">
	<?php
	$videos = new tv_files('set', $_GET['sok']);
	$videos->print_list();
	?>
</div>
<div class="clearfix"></div>