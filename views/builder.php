<?php
/** @var string $builderId */
/** @var string $filters */
/** @var string $rules */
/** @var CModel $model */
/** @var string $attribute */

?>
<?php Yii::app()->clientScript->registerCoreScript('jquery');

Yii::app()->clientScript->registerScript('build-query',
<<<JS
    $('#{$builderId}').queryBuilder({
        plugins: ['bt-tooltip-errors'],
        filters: {$filters},
    });

    $('#{$builderId}').on('change', function() {
      var result = $('#{$builderId}').queryBuilder('getRules');
      if(result!=null){
          $('#rules').html(JSON.stringify(result, null, 2));
      }
      
    });
JS
, CClientScript::POS_READY);

if($rules){
    Yii::app()->clientScript->registerScript('',"$('#{$builderId}').queryBuilder('setRules', {$rules});");
}

?>
<div id="<?=$builderId?>"></div>
<?= CHtml::activeTextArea($model,$attribute,['id'=>'rules']) ?>
