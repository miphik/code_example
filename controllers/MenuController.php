<?php

class MenuController extends Controller
{
    public $currentMenu = 'menu';
    private $_modelClass = 'Menu';

    /**
     * @return array
     */
    public function actions()
    {
        return [
            'update' => [
                'class' => 'control.controllers.actions.UpdateMenuAction',
                'modelClass' => $this->_modelClass,
                'returnUrl' => '/control/menu',
                'view' => '/menu/update',
                'pageTitle' => Yii::t('admin', 'Добавление/редактирование пункта меню'),
            ],

            'delete' => [
                'class' => 'control.controllers.actions.DeleteMenuAction',
                'modelClass' => $this->_modelClass,
                'returnUrl' => '/control/menu',
            ],

            'toggle' => [
                'class' => 'control.components.MToggleAction',
                'modelName' => $this->_modelClass,
            ],
        ];
    }

    public function actionIndex()
    {
        $menu_all = Menu::getMenu();
        $title = Yii::t('control', 'Меню');
        $this->render('index', ['menu_all' => $menu_all, 'title' => $title, 'modelClass' => $this->_modelClass]);
    }

    /**
     * Save menu items after drag and drop
     *
     * @throws CHttpException
     */
    public function actionSave()
    {
        $root_menu_id = (int)Yii::app()->request->getParam('menu_id');

        if (!$root_menu_id) {
            throw new CHttpException(400, Yii::t('control', 'Ошибка запроса'));
        }

        $aChengeMenuEl = Menu::getPostForSaveMenu();

        $root = Menu::model()->findByPk($aChengeMenuEl['current']->root);
        if (isset($aChengeMenuEl['prev']) and $aChengeMenuEl['prev']) {
            $aChengeMenuEl['current']->moveAfter($aChengeMenuEl['prev']);
        } elseif (isset($aChengeMenuEl['parent']) and $aChengeMenuEl['parent']) {
            $aChengeMenuEl['current']->moveAsFirst($aChengeMenuEl['parent']);
        } else {
            $aChengeMenuEl['current']->moveAsFirst($root);
        }

        Menu::getFullUrl($aChengeMenuEl['current']->root, true);

        Menu::renderMenu(Menu::getMenu($root_menu_id));
    }
}
