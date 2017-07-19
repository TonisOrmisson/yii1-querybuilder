## Yii1 QueryBuilder widget
This is an implementation of jQuery Querybuilder for Yii1 

### DEMO
https://demo.andmemasin.eu/yii1-querybuilder/

### Dependencies
this widget depends on jQuery and Bootstrap3. Due to Yii1 widgets logic the widget itself
 does not manage the dependencies. You need to install & load the jQuery and Bootstrap3 
 in your app yourself prior using this widget!

### Basic usage example
#### In view:
 
```
echo CHtml::beginForm();
$this->widget('ext.yii1-querybuilder.QueryBuilder',
    [
        'model'=>$model,
        'attribute'=>'myquerystring',
        'filters'=>[
            ['id'=>'name','label'=>'Name (string)','type'=>'string'],
            ['id'=>'age','label'=>'Age (integer)','type'=>'integer'],
            ['id'=>'height','label'=>'Height (double)','type'=>'double'],
            ['id'=>'birthday','label'=>'Birthday (date)','type'=>'date'],
            ['id'=>'time','label'=>'Time (time)','type'=>'time'],
            ['id'=>'changed_at','label'=>'Last changed (datetime)','type'=>'datetime'],
            ['id'=>'is_active','label'=>'Is Active? (boolean)','type'=>'boolean'],
        ],
        'rules'=>$rules,

    ]);

echo CHtml::submitButton('go');
echo CHtml::endForm();

```
#### In Controller:
```
public function actionIndex()
{
    $model = new SomeModel();
        if(isset($_POST['SomeModel'])){
            $model->attributes =$_POST['SomeModel'];
            $rules = json_decode($someModel->myquerystring);
            if($rules){
                Yii::import('application.extensions.yii1-querybuilder.Translator');
                // translate rules to SQL (with params)
                $translator = new Translator($rules);
                // feed the translated sql & params to Yii find() method
                $result = SomeModel::model()->find($translator->where, $translator->params);
            }
        }
        $this->render('index',[
            'rules'=>$rules,
        ]);
}
```