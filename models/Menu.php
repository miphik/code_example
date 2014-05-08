<?php
/**
 * This is the model class for table "menu".
 *
 * The followings are the available columns in table 'menu':
 * @property string      $id
 * @property string      $title
 * @property integer     $is_show
 * @property string      $lft
 * @property string      $rgt
 * @property integer     $level
 * @property string      $root
 * @property string      $url
 * @property string      $page_id
 *
 * @property Publication $publication
 */
class Menu extends CActiveRecord implements BActiveRecord
{
    const MENU_ROOT_LEVEL = 1;
    const MENU_TOP_LEVEL = 2;

    const MENU_LEFT = 1;
    const MENU_RIGHT = 2;

    const CACHE_MENU_FULL_URL = 'cache_menu_full_url';

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'menu';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return [
            ['title, url, root, is_show', 'required'],
            ['is_show, level, page_id', 'numerical', 'integerOnly' => true],
            ['title', 'length', 'max' => 100],
            ['lft, rgt, page_id', 'length', 'max' => 10],
            ['root', 'length', 'max' => 45],
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            ['id, title, is_show, lft, page_id, rgt, level, root, url', 'safe', 'on' => 'search'],
        ];
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return [
            'page' => [self::BELONGS_TO, 'Pages', 'page_id'],
        ];
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => Yii::t('control', 'Название'),
            'is_show' => Yii::t('control', 'Отображать'),
            'lft' => 'Lft',
            'rgt' => 'Rgt',
            'level' => 'Level',
            'root' => Yii::t('control', 'Меню'),
            'url' => Yii::t('control', 'Адрес'),
            'page_id' => Yii::t('control', 'Страница'),
        ];
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id, true);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('is_show', $this->is_show);
        $criteria->compare('lft', $this->lft, true);
        $criteria->compare('rgt', $this->rgt, true);
        $criteria->compare('level', $this->level);
        $criteria->compare('root', $this->menucol, true);
        $criteria->compare('url', $this->menucol, true);
        $criteria->compare('page_id', $this->menucol, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Menu the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function behaviors()
    {
        return [
            'nestedSetBehavior' => [
                'class' => 'ext.NestedSetBehavior',
                'leftAttribute' => 'lft',
                'rightAttribute' => 'rgt',
                'levelAttribute' => 'level',
                'hasManyRoots' => true,
                //'rootAttribute' => 'root',
            ],
        ];
    }

    public static function getAllItemsByRootID($root_id)
    {
        $children = Yii::app()->db->createCommand(
            'SELECT
                descendant.id,
                descendant.title,
                descendant.is_show,
                descendant.`level`,
                descendant.root,
                descendant.url,
                descendant.full_url
            FROM
                menu AS parent
            JOIN menu AS descendant ON descendant.lft BETWEEN parent.lft
            AND parent.rgt
            WHERE
                parent.id = ' . $root_id . '
                AND descendant.is_show = 1
            AND descendant.root = ' . $root_id . '
            ORDER BY descendant.lft'
        )->queryAll();

        return $children;
    }

    /**
     * @param int  $root_id
     * @param bool $force_cache
     * @return array
     */
    public static function getFullUrl($root_id, $force_cache = false)
    {
        if ($result = Yii::app()->cache->get(self::CACHE_MENU_FULL_URL . $root_id) and !$force_cache) {
            return $result;
        }

        $children = self::getAllItemsByRootID($root_id);
        $full_url = '';
        $prev_url = [];
        $lev_prev = 0;

        $result = [];
        foreach ($children as $item) {
            if ($item['url'] && ($item['level'] > self::MENU_ROOT_LEVEL)) {

                if ($full_url and $lev_prev < $item['level']) {
                    $prev_url[$item['level']] = $full_url;
                    $full_url = $full_url . '/' . $item['url'];
                } elseif (isset($prev_url[$item['level']])) {
                    $full_url = $prev_url[$item['level']] . '/' . $item['url'];
                } else {
                    $full_url = $item['url'];
                }
                $lev_prev = $item['level'];

                $result[$item['id']] = $full_url;
            }
        }

        Yii::app()->cache->set(self::CACHE_MENU_FULL_URL . $root_id, $result);

        return $result;
    }

    /**
     * @param int $rootId
     * @return array
     */
    public static function getTopMenu($rootId)
    {
        $data = Yii::app()->db->createCommand(
            'SELECT
                id,
                title,
                url,
                full_url
            FROM menu AS m
            WHERE is_show=1 AND root=:root AND `level`=:level
            ORDER BY m.lft'
        )->queryAll(
            true,
            [
                'root' => $rootId,
                'level' => self::MENU_TOP_LEVEL,
            ]
        );

        $data = self::_buildFullUrl($rootId, $data);

        return $data;
    }

    /**
     * Render one menu
     *
     * @param Menu $menu One root menu with all items
     */
    public static function renderMenu($menu)
    {
        $level = 0;
        $flag = true;

        $menu_root_id = current($menu);
        $menu_root_id = ($menu_root_id) ? $menu_root_id->root : false;

        $full_url = Menu::getFullUrl($menu_root_id);

        foreach ($menu as $category) {
            if ($category->level == $level) {
                echo CHtml::closeTag('li', ['class' => 'uk-sortable-list-item']) . "\n";
            } elseif ($category->level > $level) {
                if ($flag) {
                    echo CHtml::openTag('ul', ['class' => 'uk-sortable', 'data-uk-sortable' => '']) . "\n";
                } else {
                    echo CHtml::openTag('ul', ['class' => 'uk-sortable-list']) . "\n";
                }
            } else {
                echo CHtml::closeTag('li') . "\n";

                for ($i = $level - $category->level; $i; $i--) {
                    echo CHtml::closeTag('ul') . "\n";
                    echo CHtml::closeTag('li') . "\n";
                }
            }
            echo CHtml::openTag(
                'li',
                [
                    'class' => 'uk-sortable-list-item',
                    'data-id' => $category->id,
                    'data-menu' => $category->root
                ]
            );

            self::renderOneMenuItem($category, $full_url);

            $level = $category->level;
            $flag = false;
        }
        self::getCloseTagsMenu($level);
    }

    private static function renderOneMenuItem($category, $full_url)
    {
        echo CHtml::openTag('div', ['class' => 'uk-sortable-item']);
        echo CHtml::openTag('div', ['class' => 'uk-sortable-handle']);
        echo CHtml::closeTag('div');
        echo CHtml::openTag('div', ['data-sortable-action' => 'toggle']);
        echo CHtml::closeTag('div');
        echo " " . Yii::t('menu', 'Заголовок:') . " " .
            CHtml::link(CHtml::encode($category->title), '/control/menu/update/id/' . $category->id);
        if (isset($full_url[$category->id])) {
            echo " " . Yii::t('menu', 'Адрес:') . " " .
                CHtml::link(
                    $full_url[$category->id],
                    Yii::app()->createUrl('/' . $full_url[$category->id])
                );
        }
        echo " " . Yii::t('menu', 'Отображать:') . " " .
            ($category->is_show ?
                '<a href="javascript:void(0);" data-id="' . $category->id . '" class="toggle_show"><i class="uk-icon-check-circle-o"></i></a>' :
                '<a href="javascript:void(0);" data-id="' . $category->id . '" class="toggle_show"><i class="uk-icon-times-circle"></i></a>'
            );
        echo CHtml::link(
            '<i class="uk-icon-times"></i>',
            'javascript:void(0);',
            [
                'data-id' => $category->id,
                'data-menu' => $category->root,
                'class' => 'delete_item',
                'style' => 'float: right;'
            ]
        );
        echo CHtml::closeTag('div');
    }

    public static function getFrontendUrl()
    {
        $str = $_SERVER['HTTP_HOST'];
        $re = '/([A-Za-z.]+\d?)/';
        preg_match($re, $str, $matches);
        return $matches[1];
    }

    /**
     * Render close tags for menu
     *
     * @param int $level Count levels menu rate
     */
    private static function getCloseTagsMenu($level)
    {
        for ($i = $level - 1; $i; $i--) {
            echo CHtml::closeTag('li') . "\n";
            echo CHtml::closeTag('ul') . "\n";
        }
    }

    /**
     * Get all items for all menu or all item for particularly menu
     *
     * @param null|int $root id for root menu element
     * @return CActiveRecord[]|CActiveRecord
     */
    public static function getMenu($root = null)
    {
        $criteria = new CDbCriteria;
        $criteria->condition = "root=:root AND level <> 1";
        $criteria->order = 't.root, t.lft'; // or 't.root, t.lft' for multiple trees
        if ($root) {
            $criteria->params = [':root' => $root];
            $menu_all = Menu::model()->findAll($criteria);
        } else {
            $roots = Menu::getCountRoots();
            foreach ($roots as $root) {
                $criteria->params = [':root' => $root['id']];
                $menu_all[$root['id']] = Menu::model()->findAll($criteria);
            }
        }
        return $menu_all;
    }

    /**
     * Get id's all menu root elements
     *
     * @return array
     */
    public static function getCountRoots()
    {
        $roots = Yii::app()->db->createCommand(
            'SELECT
                id,
                title
            FROM
                menu
            WHERE
                menu.level = 1'
        )->queryAll();
        return $roots;
    }

    /**
     * Get objects menu from post request after drag and drop
     *
     * @return array of object Menu
     * @throws CHttpException
     */
    public static function getPostForSaveMenu()
    {
        $post = Yii::app()->request->getParam('menu');
        $post = json_decode($post, true);

        foreach ($post as $key => $item) {
            if (!empty($item)) {
                $result[$key] = Menu::model()->findByPk((int)$item);
            } else {
                $result[$key] = false;
            }
        }

        if (!isset($result['current']) or empty($post['current'])) {
            throw new CHttpException(400, Yii::t('control', 'Ошибка запроса'));
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFiltersColumn()
    {
        // TODO: Implement getFiltersColumn() method.
    }
}
