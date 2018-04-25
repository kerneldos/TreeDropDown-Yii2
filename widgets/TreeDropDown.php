<?php
namespace app\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\base\Widget;
use yii\helpers\Html;

class TreeDropDown extends Widget
{
    public $field;
    /**
     * @var Model the data model that this widget is associated with.
     */
    public $model;
    /**
     * @var string the model attribute that this widget is associated with.
     */
    public $attribute;
    /**
     * @var string the input name. This must be set if [[model]] and [[attribute]] are not set.
     */
    public $name;
    /**
     * @var string the input value.
     */
    public $value;
    /**
     * @var array the HTML attributes for the input tag.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];

    public $items = [];


    /**
     * Initializes the widget.
     * If you override this method, make sure you call the parent implementation first.
     */
    public function init()
    {
        if ($this->name === null && !$this->hasModel()) {
            throw new InvalidConfigException("Either 'name', or 'model' and 'attribute' properties must be specified.");
        }
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }
        parent::init();
    }

    /**
     * @return bool whether this widget is associated with a data model.
     */
    protected function hasModel()
    {
        return $this->model instanceof Model && $this->attribute !== null;
    }

    //Функция построения дерева из массива от Tommy Lacroix
    protected function getTree($dataset) {
        $tree = [];

        foreach ($dataset as $id => &$node) {
            //Если нет вложений
            if (!$node['parent_id']) {
                $tree[$id] = &$node;
            } else {
                //Если есть потомки то перебераем массив
                $dataset[$node['parent_id']]['childs'][$id] = &$node;
            }
        }
        return $tree;
    }

    protected function tplMenu($category, $str) {

        static $res = [];

        foreach ($category as $cat) {

            $cat['id'] == $this->model->id ?: $res[$cat['id']] = $str . $cat['name'];

            if (isset($cat['childs'])) {
                $this->tplMenu($cat['childs'], $str . '   ');
            }
        }

        return $res;
    }

    protected function renderInputHtml()
    {
        $data = $this->model->find()
            ->select(['id', 'parent_id', 'name'])
            ->indexBy('id')
            ->asArray()
            ->all();

        $this->items = $this->tplMenu($this->getTree($data), '');

        return Html::activeDropDownList(
            $this->model,
            $this->attribute,
            $this->items,
            [
                'encodeSpaces' => true,
                'class' => 'form-control',
                'prompt' => [
                    'text' => 'Родительская страница',
                    'options' => [
                        'value' => '0',
                    ],
                ],
                'options' => [
                    $this->model->id => [
                        'disabled' => true,
                    ],
                ],
            ]
        );
    }

    public function run()
    {
        echo $this->renderInputHtml();
    }
}
