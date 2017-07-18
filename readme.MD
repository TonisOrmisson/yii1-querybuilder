# NB! work in progress here ....

# Yii1 QueryBuilder widget
This is an implementation of jQuery Querybuilder for Yii1 

## Dependencies
this widget depends on jQuery and Bootstrap3. Due to Yii1 widgets logic the widget itself
 does not manage the dependencies. You need to install & load the jQuery and Bootstrap3 
 in your app yourself prior using this widget!

## Basic usage example
In view:
 
```
echo CHtml::beginForm();
$this->widget('ext.yii1-querybuilder.QueryBuilder',
    [
        'model'=>$model,
        'attribute'=>'myquerystring',
        'filters'=>[
            ['id' => 'id', 'label' => 'Id', 'type' => 'integer'],
            ['id' => 'name', 'label' => 'Name', 'type' => 'string'],
            ['id' => 'lastName', 'label' => 'Last Name', 'type' => 'string']
        ],
        'rules'=>$rules,

    ]);

echo CHtml::submitButton('go');
echo CHtml::endForm();

```
In Controller:
```
public function actionIndex()
{
    $model = new SomeModel();
        if(isset($_POST['SomeModel'])){
            $model->attributes =$_POST['SomeModel'];
            $rules = json_decode($someModel->myquerystring);
        }
        $this->render('index',['rules'=>$rules]);

}
```