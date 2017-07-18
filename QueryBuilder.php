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


    public $cssFile;
    private $jsFile;

    /** @inheritdoc */
    public function init()
    {
        $cssFileName=dirname(__FILE__).DIRECTORY_SEPARATOR.'query-builder'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'query-builder.default.css';
        $jsFileName=dirname(__FILE__).DIRECTORY_SEPARATOR.'query-builder'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'query-builder.standalone.js';
        $this->cssFile=Yii::app()->getAssetManager()->publish($cssFileName);
        $this->jsFile = Yii::app()->getAssetManager()->publish($jsFileName);
        $this->registerQueryBuilderAssets();

        if(!$this->id){
            $this->id = "query-builder";
        }
        $this->filters =array(array('id'=>1,'label'=>'test','type'=>'string'));

        parent::init();
    }

    /** @inheritdoc */
    public function run(){
        $params = array(
            'builderId' => $this->id,
            'filters' => json_encode($this->filters),
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
