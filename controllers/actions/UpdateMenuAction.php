<?php

/**
 * Экшен добавления/редактирования модели
 *
 * Class UpdateMenuAction
 */
class UpdateMenuAction extends UpdateAction
{
    /**
     * @param $modelClass BActiveRecord
     */
    protected function saveNewRecord($modelClass)
    {
        $class = CHtml::modelName($this->modelClass);
        $post = Yii::app()->request->getPost($class);
        $saveWithAjax = Yii::app()->request->getParam('saveNotRedirect');
        $notRedirect = Yii::app()->request->getParam('notRedirect');
        $saveResult = false;

        if ($post and isset($post['root'])) {
            $modelClass->setAttributes($post);
            $modelClass->validate();
            $oRoot = Menu::model()->findByPk($post['root']);
            if (!$modelClass->isNewRecord) {
                if ($oRoot and $modelClass->saveNode()) {
                    Menu::getFullUrl($modelClass->root, true);
                    $saveResult = true;
                }
            } else {
                if ($oRoot and $modelClass->appendTo($oRoot)) {
                    Menu::getFullUrl($modelClass->root, true);
                    $saveResult = true;
                }
            }
        }

        if ($saveResult && $notRedirect) {
            $this->controller->refresh();
        }

        if ($saveResult && $saveWithAjax) {
            echo json_encode(['success' => Yii::t('control', 'Запись успешно сохранена')]);
            Yii::app()->end();
        }

        if ($saveWithAjax) {
            echo json_encode(['error' => $modelClass->getErrors(), 'name' => get_class($modelClass)]);
            Yii::app()->end();
        }

        if ($saveResult) {
            Yii::app()->user->setFlash($class . 'success', Yii::t('control', 'Запись успешно сохранена'));
            $this->controller->redirect(
                $this->controller->createUrl($this->returnUrl)
            );
        }
    }
}
