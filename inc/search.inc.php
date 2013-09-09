<?php
if(!isset($_GET['q']) || empty($_GET['q'])) { ?>
<form class="span12 form-search" action="<?= BASEURL ?>" method="get">
		<div class="input-append">
			<input type="text" name="q" class="span9 search-query" placeholder="Søk i UKM-tv">
			<button type="submit" class="btn"><i class="icon-search"></i></button>
		</div>
	</form>
<?php
} else {
	$result = new tv_files('search', $_GET['q']);
?>	<h2>Søkeresultat for "<?= $_GET['q'] ?>"</h2>
	<div class="row">
	<?php
	if($result->num_videos > 0)
		$result->print_list();
	else
		echo '<div class="alert alert-info span9"><strong>Beklager</strong>, ingen treff!</div>';
	?>
	</div>
<?php } ?>
<div class="clearfix"></div>