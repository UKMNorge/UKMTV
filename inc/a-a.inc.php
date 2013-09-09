<?php
if(!isset($_GET['alpha'])) {
	$_GET['alpha'] = 'a';
}
?>
<h2>Filmer som starter med "<?= $_GET['alpha'] ?>"</h2>
<select class="visible-phone visible-tablet" id="a-z-chooser">
	<?php
	foreach( range('a','z') as $i ) { ?>
		<option value="<?= $i ?>" <?= $i==$_GET['alpha'] ? 'selected="selected"':'' ?>><?= $i ?></option>
	<?php
	} ?>
		<option value="æ" <?= 'æ'==$_GET['alpha'] ? 'selected="selected"':'' ?>>æ</option>
		<option value="ø" <?= 'ø'==$_GET['alpha'] ? 'selected="selected"':'' ?>>ø</option>
		<option value="å" <?= 'å'==$_GET['alpha'] ? 'selected="selected"':'' ?>>å</option>
</select>

<div class="btn-group hidden-phone hidden-tablet">
<?php
foreach(range('a','z') as $i) { ?>
	<a class="btn <?= $_GET['alpha'] == $i ?>" href="/a-a/<?= $i ?>"><?= $i ?></a>
<?php
} ?>
	<a class="btn <?= $_GET['alpha'] == 'æ' ?>" href="/a-a/æ">æ</a>
	<a class="btn <?= $_GET['alpha'] == 'ø' ?>" href="/a-a/ø">ø</a>
	<a class="btn <?= $_GET['alpha'] == 'å' ?>" href="/a-a/å">å</a>
</div>
<div class="cleanfix"></div>
<div class="vertical-spacer"></div>
<div class="row">
<?php
$i = 0;
$letter = new tv_files('alphabet', $_GET['alpha']);
$letter->print_list();
?>
</div>
<div class="cleanfix"></div>
<?php
if( sizeof( $videos ) == 0 ) { ?>
	<div class="alert alert-warning">
		<strong>Beklager,</strong> fant ingen filmer som starter med <?= $_GET['alpha'] ?>
	</div>
<?php
} ?>
<script language="javascript">
jQuery('#a-z-chooser').change(function(){
	window.location.href = '<?= BASEURL ?>a-a/' + jQuery('#a-z-chooser option:selected').val();
});
</script>