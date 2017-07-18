<?php
/** @var string $builderId */
/** @var string $filters */
?>
<?php Yii::app()->clientScript->registerCoreScript('jquery');
$builderId = "sadsdf";
Yii::app()->clientScript->registerScript('build-query',
<<<JS
    $('#$builderId').queryBuilder({
        plugins: ['bt-tooltip-errors'],
        filters: $filters
    });

JS
, CClientScript::POS_READY);

?>
<div id="<?=$builderId?>"></div>
