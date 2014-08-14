<?php
$related = new tv_files('related', $TV);
require_once('UKM/person.class.php');
require_once('UKMconfig.inc.php');
?>
<script language="javascript" src="http://embed.<?= UKM_HOSTNAME ?>/info/<?= $TV->id ?>"></script>
<div id="my-video" width="100%"></div>
<div class="fb-like pull-right" data-send="true" data-layout="box_count" data-width="100" data-show-faces="false" data-font="arial"></div>
<div class="wrapper">
	<h2><?= $TV->title ?></h2>
	<div class="description"></div>
	<strong><a class="kat" href="<?= $TV->category_url ?>"><?= $TV->category ?></a> &raquo; </strong>
	<a class="kat" href="<?= $TV->set_url ?>"><?= $TV->set ?></a>
	<?php
	$inn = new innslag($TV->b_id);
	if(sizeof($inn->personer())!=0) { ?>
		<div class="description">
			<strong>Personer i innslaget:</strong>
			<?php
			$i = 0;
			foreach($inn->personer() as $pers) {
				$i++;
				$p = new person($pers['p_id']); ?>
				<a href="<?= BASEURL ?>relatert/p_<?= $p->g('p_id') ?>"><?= $p->g('name') ?></a><?= $i < sizeof($inn->personer()) ? ', ' : ''?>
			<?php
			} ?>
		</div>
	<?php } ?>
	<div class="description">
		<?= $video['tv_description'] ?>
	</div>
	<div class="clearfix"></div>
</div>
<h3>Anbefalte videoer fra samme album</h3>
<div class="relaterte_videoer">
	<div class="row">
	<?php
	echo $related->print_list(10);
	?>
	</div>
</div>