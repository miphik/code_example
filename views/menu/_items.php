<?php
Yii::app()->clientScript->registerScript(
    'delete_alert',
    '
    $(document).ready(function () {
        var alert = $(".uk-alert");
        setTimeout(function(){alert.hide(1000, function() {$(this).remove()})}, 2500);
    });
    $(document).on("click", ".toggle_show", function(el) {
        var elem = $(el.currentTarget);
        $.ajax({
            type: "POST",
            url: "/control/menu/toggle/pk/" + elem.data("id") + "/attribute/is_show",
            data: "",
            dataType: "html",
            success: function(data) {
                if (elem.children("i").hasClass("uk-icon-check-circle-o")) {
                    elem.children("i").removeClass("uk-icon-check-circle-o").addClass("uk-icon-times-circle");
                } else {
                    elem.children("i").removeClass("uk-icon-times-circle").addClass("uk-icon-check-circle-o");
                }
            },
            error: function (data){
            }
        });
    });
    '
);
echo '<a class="uk-button uk-button-primary" href="' .
    Yii::app()->createUrl('/control/menu/update') . '">' .
    Yii::t('control', 'Добавить пункт меню') . '</a>';
?>
<br /><br /><br />
<?php
echo '<br /><br />';
//$modelClass->unsetAttributes();
if (Yii::app()->user->hasFlash('success')) { ?>
    <div id="error_ajax">
        <div class="uk-alert uk-alert-success" data-uk-alert="">
            <a href="" class="uk-alert-close uk-close"></a>
            <p><?= Yii::app()->user->getFlash('success'); ?></p>
        </div>
    </div>
<?php
} elseif (Yii::app()->user->hasFlash('error')) { ?>
<div id="error_ajax">
    <div class="uk-alert uk-alert-warning" data-uk-alert="">
        <a href="" class="uk-alert-close uk-close"></a>
        <p><?= Yii::app()->user->getFlash('error'); ?></p>
    </div>
</div>
<?php
} ?>
<div id="content_remove" class="uk-width-medium-1-2 height">
    <ul class="uk-tab" data-uk-tab="{connect:'#tab-content'}">
        <?php
        // Create tabs for each menu
        $roots = Menu::getCountRoots();
        foreach ($roots as $root) {
            ?>
            <li><a href="javascript:void(0);"><?php echo Yii::t('control', $root['title']); ?></a></li>
        <?php
        }
        ?>
    </ul>
    <ul id="tab-content" class="uk-switcher uk-margin">
        <?php
        // Render menu
        foreach ($menu_all as $key => $menu) { ?>
            <div id="menu_root_<?php echo $key; ?>">
            <?php
            if (!empty($menu)) {
                Menu::renderMenu($menu);
            }
            ?>
            </div>
        <?php
        } ?>
    </ul>
</div>
<br /><br /><br />