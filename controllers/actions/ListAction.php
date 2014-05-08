<?php

/**
 * Class ListAction
 */
class ListAction extends CAction
{
    /**
     * @var BActiveRecord | null $modelClass
     */
    public $modelClass = null;

    /**
     * @var string
     */
    public $pageTitle = '';

    /**
     * @var string | null
     */
    public $view = null;

    /**
     * @var string
     */
    public $title = '';

    /**
     * @var string
     */
    public $updateUrl = '';

    /**
     * @var string
     */
    public $deleteUrl = '';

    public function run()
    {
        if (is_null($this->view)) {
            throw new CException(
                Yii::t('yii', 'View is not defined.')
            );
        }

        // Проверяем обязательный параметр
        if (empty($this->modelClass)) {
            throw new CException(
                Yii::t(
                    'yii', 'Property "{class}.{property}" is not defined.',
                    [
                        '{class}' => get_class($this),
                        '{property}' => 'modelClass'
                    ]
                )
            );
        }

        /**
         * @var BActiveRecord $modelClass
         */
        $modelClass = new $this->modelClass('search');
        $modelName = CHtml::modelName($modelClass);
        if (!($modelClass instanceof BActiveRecord)) {
            throw new CException(
                Yii::t(
                    'yii', 'Class "{class} not instanceof BActiveRecord.',
                    [
                        '{class}' => $this->modelClass,
                    ]
                )
            );
        }

        /**
         * @var Controller $controller
         */
        $controller = $this->controller;

        /**
         * Заголовок страницы
         */
        $controller->pageTitle = Yii::app()->name;
        if (is_string($this->pageTitle) && !empty($this->pageTitle)) {
            $controller->pageTitle .= ' | ' . $this->pageTitle;
        }

        $ajax = Yii::app()->request->getQuery('ajax');
        if ($ajax && $ajax === $modelName . '-grid') {
            $query = Yii::app()->request->getQuery($modelName);
            $modelClass->setScenario('search');
            $modelClass->setAttributes($query);
        }

        $controller->render(
            $this->view,
            [
                'title' => $this->title,
                'modelClass' => $modelClass,
                'modelName' => $modelName,
                'updateUrl' => $this->updateUrl,
                'deleteUrl' => $this->deleteUrl,
            ]
        );
    }
}