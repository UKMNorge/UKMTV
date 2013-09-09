<p class="lead">UKM filmer hvert år flere hundre deltakere. Her har vi samlet filmer fra de siste årene</p>

<h2>
	Populært den siste måneden
</h2>

<div class="row">
<?php
$popular = new tv_files('popular',date('Y-m'));
$popular->print_list(15);

if($popular->num_videos == 0) {
	$popular = new tv_files('popular',false);
	$popular->print_list(15);	
}
?>
</div>
<div class="clearfix"></div>