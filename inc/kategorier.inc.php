<?php
####################################################################
## CATEGORY FOLDERS / CATEGORIES
if(!isset($_GET['folder'])) {
	$qry = new SQL("SELECT `folders`.*,
						(SELECT COUNT(`categories`.`c_id`)
							 FROM `ukm_tv_categories` AS `categories`
							 WHERE `categories`.`f_id` = `folders`.`f_id`) AS `count`
					FROM `ukm_tv_category_folders` AS `folders`
					ORDER BY `folders`.`f_name` ASC");
	$res = $qry->run(); ?>
	<h2>Alle kategorier</h2>
	<?php
	if($res) {
		while( $r = mysql_fetch_assoc( $res ) ) { 
			if($r['count'] == 0)
				continue;
		?>
		<div class="well well-small span2">
			<a href="/kategorier/<?= utf8_encode($r['f_name']) ?>"><?= utf8_encode($r['f_name']) ?></a>
			<br /><small><strong>Inneholder:</strong> 
				<?= $r['count'] ?> samling<?= sizeof($r['count']) == 1 ? 'er' : '' ?></small>
		</div>
		<?php
		}
	}
### UKATEGORISERTE SAMLINGER
	$qry = new SQL("SELECT `t_group`.`tv_category`, 
							(SELECT COUNT(`t_count`.`tv_id`)
							 FROM `ukm_tv_files` AS `t_count`
							 WHERE `t_count`.`tv_category` = `t_group`.`tv_category` 
							 AND `tv_deleted`='false') AS `videos`
					FROM `ukm_tv_files` AS `t_group`
					JOIN `ukm_tv_categories` AS `cat_rel` ON (`cat_rel`.`c_name` = `t_group`.`tv_category`)
					WHERE `cat_rel`.`f_id` = '0'
					GROUP BY `t_group`.`tv_category`
					ORDER BY `t_group`.`tv_category`"
					);
	$res = $qry->run(); ?>
	<?php
	if($res){
		while( $r = mysql_fetch_assoc( $res ) ) { ?>
		<div class="well well-small span2">
			<a href="/samling/<?= $r['tv_category'] ?>"><?= $r['tv_category'] ?></a>
			<br /><small><strong>Inneholder:</strong> <?= $r['videos'] ?> film<?= $r['videos'] > 1 ? 'er' : '' ?></small>
		</div>
		<?php
		}
	}
####################################################################
## CATEGORY COLLECTIONS (REAL CATEGORIES)
} else {
	$qry = new SQL("SELECT `t_group`.`tv_category`, 
							(SELECT COUNT(`t_count`.`tv_id`)
							 FROM `ukm_tv_files` AS `t_count`
							 WHERE `t_count`.`tv_category` = `t_group`.`tv_category`
							 AND `tv_deleted`='false') AS `videos`
					FROM `ukm_tv_files` AS `t_group`
					JOIN `ukm_tv_categories` AS `cat_rel` ON (`cat_rel`.`c_name` = `t_group`.`tv_category`)
					JOIN `ukm_tv_category_folders` AS `folders` ON (`folders`.`f_id` = `cat_rel`.`f_id`)
					WHERE `f_name` = '#fname'
					GROUP BY `t_group`.`tv_category`
					ORDER BY `t_group`.`tv_category`",
					array('fname' => utf8_decode($_GET['folder'])));
	$res = $qry->run(); ?>
	<h2>Alle samlinger i <?= $_GET['folder']?></h2>
	<?php
	if($res)
		while( $r = mysql_fetch_assoc( $res ) ) { ?>
		<div class="well well-small span2">
			<a href="/samling/<?= $r['tv_category'] ?>"><?= str_replace('Fylkesmønstringen i ','',$r['tv_category']) ?></a>
			<br /><small><strong>Inneholder:</strong> <?= $r['videos'] ?> film<?= $r['videos'] > 1 ? 'er' : '' ?></small>
		</div>
		<?php
		}
} ?>
<div class="clearfix"></div>