<?php $this->renderPartial('//layouts/components/head'); ?>
<div class="container">

    <?php $this->renderPartial('//layouts/components/banner'); ?>
    <?php $this->renderPartial('//layouts/components/navigation'); ?>

    <div class="row" id="content"><?php echo $content; ?></div>
    <div class="shadow"></div>
</div>
<?php $this->renderPartial('//layouts/components/foot'); ?>
