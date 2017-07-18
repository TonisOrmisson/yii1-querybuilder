<?php

/**
 * @author Tõnis Ormisson <tonis@andmemasin.eu>
 */
class QueryBuilder extends CWidget
{
    /** @var  string QueryBuilder container ID */
    public $id;

    /** @var array $filters */
    public $filters;

    /** @var array $rules */
    public $rules;

    /** @var CModel $model*/
    public $model;

    /** @var string $attribute*/
    public $attribute;

    public $cssFile;
    private $jsFile;

    /** @inheritdoc */
    public function init()
    {
        if(!$this->filters){
            throw new ErrorException('Filters must be set for QueryBuilder');
        }
        if(!$this->model){
            throw new ErrorException('Model must be set for QueryBuilder');
        }
        if(!$this->attribute){
            throw new ErrorException('Attribute must be set for QueryBuilder');
        }

        $cssFileName=dirname(__FILE__).DIRECTORY_SEPARATOR.'query-builder'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'query-builder.default.css';
        $jsFileName=dirname(__FILE__).DIRECTORY_SEPARATOR.'query-builder'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'query-builder.standalone.js';
        $this->cssFile=Yii::app()->getAssetManager()->publish($cssFileName);
        $this->jsFile = Yii::app()->getAssetManager()->publish($jsFileName);
        $this->registerQueryBuilderAssets();

        // set default id
        if(!$this->id){
            $this->id = "query-builder";
        }
        parent::init();
    }

    /** @inheritdoc */
    public function run(){
        $reflect = new ReflectionClass($this->model);

        $params = array(
            'builderId' => $this->id,
            'filters' => json_encode($this->filters),
            'rules' => $this->rules ? json_encode($this->rules) : null,
            'model' => $this->model,
            'modelClassName' =>$reflect->getShortName(),
            'attribute' => $this->attribute,
        );
        $this->render('builder',$params);
    }


    protected function registerQueryBuilderAssets()
    {
        // register assets
        $cs=\Yii::app()->clientScript;
        $cs->registerCssFile($this->cssFile);
        $cs->registerScriptFile($this->jsFile, CClientScript::POS_HEAD);
    }
}
