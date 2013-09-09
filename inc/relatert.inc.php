<?php
$tagged = new tv_files('tag', $_GET['sok']);

require_once('UKM/person.class.php');
//require_once('UKM/band.class.php');
if(strpos($_GET['sok'], 'b_')===0){
	$b = new innslag(str_replace('b_', '', $_GET['sok']));
	?>
	<h2>Filmer av "<?= $b->g('b_name') ?>"</h2>
	<h4>OBS: et søk kan finne flere filmer, <a href="/?q=<?= urlencode($b->get('b_name'))?>">klikk her for å vise søkeresultat</a></h4>
	<?php
} elseif(strpos($_GET['sok'], 'p_')===0) {
	$p = new person(str_replace('p_', '', $_GET['sok']));
	echo '<h2>Filmer hvor "'. $p->g('p_firstname') .'" deltar</h2>';
}

?>
<div class="row">
	<?php	$tagged->print_list(); ?>
</div>
<?php if($tagged->num_videos == 0) { ?>
	<div class="alert alert-warning">
		<strong>Beklager,</strong> fant ingen filmer hvor <?= $p->g('name') ?> deltar
	</div>
<?php
} ?>
<div class="clearfix"></div>