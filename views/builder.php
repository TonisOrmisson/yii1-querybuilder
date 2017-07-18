<?php
/** @var string $builderId */
/** @var string $filters */
/** @var string $rules */

?>
<?php Yii::app()->clientScript->registerCoreScript('jquery');

Yii::app()->clientScript->registerScript('build-query',
<<<JS
    $('#$builderId').queryBuilder({
        plugins: ['bt-tooltip-errors'],
        filters: $filters,
    });

JS
, CClientScript::POS_READY);

if($rules){
    Yii::app()->clientScript->registerScript('',"$('#{$builderId}').queryBuilder('setRules', {$rules});");
}

?>
<div id="<?=$builderId?>"></div>
