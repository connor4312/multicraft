<?php if ($cpu !== null || $memory !== null): ?>

<?php
function intervals($value, $warning, $danger) {
	if ($value >= $danger) {
		return 'progress-bar-danger';
	} elseif ($value >= $warning) {
		return 'progress-bar-warning';
	}

	return '';
}
?>
<h3><?php echo Yii::t('mc', 'Resource usage') ?></h3>
<div class="row">
	<div class="col-md-6">
		<h4><?php echo Yii::t('mc', 'CPU') ?></h4>
		<div class="progress progress-striped active">
			<div class="progress-bar <?php echo intervals($cpu, 85, 95) ?>" role="progressbar" aria-valuenow="<?php echo $cpu ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $cpu ?>%">
				<?php echo $cpu ?>% Usage
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<h4><?php echo Yii::t('mc', 'Memory') ?></h4>
		<div class="progress progress-striped active">
			<div class="progress-bar <?php echo intervals($memory, 85, 95) ?>" role="progressbar" aria-valuenow="<?php echo $memory ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $memory ?>%">
				<?php echo $memory ?>% Usage
			</div>
		</div>
	</div>
</div>
<?php endif ?>
