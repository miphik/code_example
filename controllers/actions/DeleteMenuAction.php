<?php

/**
 * Экшен удаления записи
 */
class DeleteMenuAction extends DeleteAction
{
    /**
     * @param Menu     $modelClass
     * @param null|int $id
     * @throws CHttpException
     */
    protected function deleteItem($modelClass, $id = null)
    {
        $root = (int)Yii::app()->request->getParam('menu_id');
        if ($id and $root) {
            $node = $modelClass->findByPk($id);
            if (!$node->deleteNode()) {
                throw new CHttpException(500, Yii::t('backend', 'Не удалось удалить запись'));
            } else {
                Menu::renderMenu(Menu::getMenu($root));
            }
        }
    }
}
