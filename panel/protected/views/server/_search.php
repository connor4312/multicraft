<div class="wide form">

<?php $form=$this->beginWidget('CActiveForm', array(
    'action'=>Yii::app()->createUrl($this->route),
    'method'=>'get',
)); ?>

    <div class="row">
        <?php echo $form->label($model,'id'); ?>
        <?php echo $form->textField($model,'id'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'name'); ?>
        <?php echo $form->textField($model,'name'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'ip'); ?>
        <?php echo $form->textField($model,'ip'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'port'); ?>
        <?php echo $form->textField($model,'port'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'world'); ?>
        <?php echo $form->textField($model,'world'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'dir'); ?>
        <?php echo $form->textField($model,'dir'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'players'); ?>
        <?php echo $form->textField($model,'players'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'memory'); ?>
        <?php echo $form->textField($model,'memory'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'start_memory'); ?>
        <?php echo $form->textField($model,'start_memory'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'jarfile'); ?>
        <?php echo $form->textField($model,'jarfile'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'default_level'); ?>
        <?php echo $form->textField($model,'default_level'); ?>
    </div>

    <div class="row">
        <?php echo $form->label($model,'suspended'); ?>
        <?php echo $form->textField($model,'suspended'); ?>
    </div>

    <div class="row buttons">
        <?php echo CHtml::submitButton(Yii::t('mc', 'Search')); ?>
    </div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->

