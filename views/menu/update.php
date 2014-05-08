<?php
/**
 * @var MenuController $this
 * @var Menu           $modelClass
 * @var string         $returnUrl
 */

Yii::app()->clientScript->registerScriptFile('/js/save_item.js', CClientScript::POS_END);

$title = ($modelClass->isNewRecord ? 'Добавление' : 'Редактирование') . ' пункта меню';
echo CHtml::tag(
    'h3',
    [],
    Yii::t('control', $title)
);

// Кнопки сохранения и отмены.
if ($modelClass->isNewRecord) {
    $buttons = CHtml::button(
        Yii::t('control', 'Сохранить'),
        ['class'=> 'uk-button uk-button-primary save_without_redirect']
    );
} else {
    $buttons = CHtml::button(
        Yii::t('control', 'Сохранить'),
        ['class'=> 'uk-button uk-button-primary save_item']
    );
}
$buttons .= '&nbsp;';
$buttons .= CHtml::button(
    Yii::t('control', 'Сохранить и выйти'),
    ['class'=> 'uk-button uk-button-primary', 'type' => 'submit']
);
$buttons .= '&nbsp;';
$buttons .= CHtml::link(Yii::t('control', 'Отмена'), $this->createUrl($returnUrl), ['class'=> 'uk-button']);

/**
 * @var TbActiveForm $form
 */
$form = $this->beginWidget(
    'bootstrap.widgets.TbActiveForm',
    [
        'id' => 'form-model',
        'type' => 'horizontal',
    ]
);

// Выводим ошибки
if ($modelClass->hasErrors()) {
    echo $form->errorSummary($modelClass);
}
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

// Выводим кнопки управления
echo $buttons . "<br/><br/>";

// --- Начало полей формы ---

echo $form->textFieldRow($modelClass, 'title', ['class' => 'uk-width-7-10']);
echo $form->textFieldRow($modelClass, 'url', ['class' => 'uk-width-7-10']);
echo $form->dropDownListRow(
    $modelClass,
    'root',
    [
        Menu::MENU_RIGHT => Yii::t('control', 'Правое меню'),
        Menu::MENU_LEFT => Yii::t('control', 'Левое меню'),
    ],
    [
        'empty' => '--- Выберите ---',
        'class' => 'uk-width-7-10'
    ]
);
echo $form->dropDownListRow(
    $modelClass,
    'is_show',
    [
        '1' => Yii::t('control', 'Да'),
        '0' => Yii::t('control', 'Нет'),
    ],
    [
        'empty' => '--- Выберите ---',
        'class' => 'uk-width-7-10'
    ]
);

echo $form->dropDownListRow(
    $modelClass,
    'page_id',
    Pages::getAllPages(),
    [
        'empty' => '--- Выберите ---',
    ]
);

// Выводим кнопки управления
echo $buttons;

$this->endWidget();
