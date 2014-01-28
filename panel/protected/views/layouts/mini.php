<?php
/**
 *
 *   Copyright © 2010-2012 by xhost.ch GmbH
 *
 *   All rights reserved.
 *
 **/
?>
<?php $this->renderPartial('//layouts/components/head'); ?>
    <div id="watermark-logo"></div>
    <div id="mini">
        
        <?php $this->renderPartial('//layouts/components/banner'); ?>
        <?php $this->renderPartial('//layouts/components/navigation'); ?>

        <div class="row" id="content"><?php echo $content; ?></div>
    </div>
<?php $this->renderPartial('//layouts/components/foot'); ?>
