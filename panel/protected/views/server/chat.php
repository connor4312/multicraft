<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
$this->pageTitle = Yii::app()->name . ' - '.Yii::t('mc', 'Chat');

$this->breadcrumbs=array(
    Yii::t('mc', 'Servers')=>array('index'),
    $model->name=>array('view', 'id'=>$model->id),
    Yii::t('mc', 'Chat'),
);

$this->menu = array(
    array(
        'label'=>Yii::t('mc', 'Back'),
        'url'=>array('server/view', 'id'=>$model->id),
        'icon'=>'arrow-left',
    )
);

?>
<div class="row">
    <h3>
        <div class="pull-left" id="statusicon-ajax"><?php echo $data['statusicon'] ?></div>
        <?php echo Yii::t('mc', 'Chat') ?>
    </h3>
</div>
<br>
<div class="row">
    <div class="col-md-3">
        <?php if ($getPlayers): ?>
        <!-- PLAYERS -->
        <div id="players-ajax">
            <?php echo $data['players'] ?>
        </div>
        <?php endif ?>
    </div>
    <div class="col-md-9">
        <?php if ($getChat): ?>
        <!-- CHAT -->
        <?php if ($chat): ?>
        <?php echo CHtml::beginForm() ?>

        <div class="input-group">
            <input type="text" id="message" name="message" value="" class="form-control" data-focus>
            <span class="input-group-btn">
                <?php echo CHtml::ajaxSubmitButton(Yii::t('mc', 'Send'), '', array('type'=>'POST',
                        'data'=>array('ajax'=>'chat', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,
                        'message'=>"js:$('#message').val()"), 'success'=>'js:chat_response'
                    ), array('class' => 'btn btn-primary')) ?>
            </span>
        </div>
        <div class="alert alert-error" id="chat-error" style="display: none"></div>


        <?php echo CHtml::endForm() ?>
        <?php endif ?>
        <div id="console" data-type="chat"></div>
        <?php echo CHtml::ajaxLink(Yii::t('mc', 'Clear chat'), '', array('type'=>'POST',
                'data'=>array('ajax'=>'clearChat', Yii::app()->request->csrfTokenName=>Yii::app()->request->csrfToken,),
                'success'=>'js:chat_response')) ?>
        <?php endif ?>
    </div>
</div>

<?php $this->printRefreshScript(); ?>
<?php echo CHtml::script('
    scheduleRefresh(function(d){multicraft.console(d);});
    function chat_response(data)
    {
        $("#message").focus();
        if (data)
        {
            $("#chat-error").html(data)
            $("#chat-error").show()
        }
        else
        {
            $("#chat-error").hide()
            $("#message").val("")
        }
        setTimeout(function() { refresh("chat", function(d){multicraft.console(d);});}, 500);
    }
    '); ?>
