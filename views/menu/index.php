<?php
Yii::app()->clientScript->registerCssFile('/css/uikit.docs.min.css');
Yii::app()->clientScript->registerScriptFile('/js/sortable.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/js/menu_update.js', CClientScript::POS_END);
/* @var $this MenuController */
echo "<h1>{$title}</h1>";
?>
<div id="change_content">
    <?php
    $class = CHtml::modelName($modelClass);
    echo '<div id="error_ajax">';
        if (Yii::app()->user->hasFlash($class . 'success')) { ?>
        <div class="uk-alert uk-alert-success" data-uk-alert="">
            <a href="" class="uk-alert-close uk-close"></a>
            <p><?= Yii::app()->user->getFlash($class . 'success'); ?></p>
        </div>
        <?php
        }
    echo '</div>';
    $this->renderPartial('_items', compact('menu_all')); ?>
</div>


