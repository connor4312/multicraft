<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
?>
<?php $this->beginContent('//layouts/main'); ?>
<menu>
        <?php
            $this->widget('application.components.Menu', array(
                'items'=>$this->menu,
                'htmlOptions'=>array('class'=>'operations'),
            ));
        ?>
</menu>
<content>
    <?php echo $content; ?>
</content>
<?php $this->endContent(); ?>
