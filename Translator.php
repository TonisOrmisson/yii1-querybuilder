<?php

/**
 * Class Translator
 * @see based on https://github.com/leandrogehlen/yii2-querybuilder/blob/master/Translator.php
 * @property  array $params
 * @property  array $where
 */
class Translator extends CModel
{
    private $_where;
    private $_params = [];
    private $_operators;

    /** @var  CDbCriteria $criteria */
    public $criteria;

    /** @var array The params from that are already set so we don't overwrite them */
    private $currentParams = [];
    private $paramsCount = 0;

    /**
     * Constructors.
     * @param array $data Rules configuration
     * @param array $config the configuration array to be applied to this object.
     */
    public function __construct($data, $config = [])
    {
        // Yii1 does not init (its based on yii2 version)
        $this->init();
        $this->_where = $this->buildWhere($data);
    }

    public function init()
    {
        $this->_operators = [
            'equal' =>            '= ?',
            'not_equal' =>        '<> ?',
            'in' =>               ['op' => 'IN (?)',     'list' => true, 'sep' => ', ' ],
            'not_in' =>           ['op' => 'NOT IN (?)', 'list' => true, 'sep' => ', '],
            'less' =>             '< ?',
            'less_or_equal' =>    '<= ?',
            'greater' =>          '> ?',
            'greater_or_equal' => '>= ?',
            'between' =>          ['op' => 'BETWEEN ?',   'list' => true, 'sep' => ' AND '],
            'begins_with' =>      ['op' => 'LIKE ?',     'fn' => function($value){ return "$value%"; } ],
            'not_begins_with' =>  ['op' => 'NOT LIKE ?', 'fn' => function($value){ return "$value%"; } ],
            'contains' =>         ['op' => 'LIKE ?',     'fn' => function($value){ return "%$value%"; } ],
            'not_contains' =>     ['op' => 'NOT LIKE ?', 'fn' => function($value){ return "%$value%"; } ],
            'ends_with' =>        ['op' => 'LIKE ?',     'fn' => function($value){ return "%$value"; } ],
            'not_ends_with' =>    ['op' => 'NOT LIKE ?', 'fn' => function($value){ return "%$value"; } ],
            'is_empty' =>         '= ""',
            'is_not_empty' =>     '<> ""',
            'is_null' =>          'IS NULL',
            'is_not_null' =>      'IS NOT NULL'
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeNames()
    {
    }

    /**
     * Encodes filter rule into SQL condition
     * @param string $field field name
     * @param string|array $type operator type
     * @param string|array $params query parameters
     * @return string encoded rule
     */
    protected function encodeRule($field, $type, $params)
    {
        $pattern = $this->_operators[$type];
        $keys = array_keys($params);

        if (is_string($pattern)) {
            $replacement = !empty($keys) ? $keys[0] : null;
        } else {
            $op = self::getValue($pattern, 'op');
            $list = self::getValue($pattern, 'list');
            if ($list){
                $sep = self::getValue($pattern, 'sep');
                $replacement = implode($sep, $keys);
            } else {
                $fn = self::getValue($pattern, 'fn');
                $replacement = key($params);
                $params[$replacement] = call_user_func($fn, $params[$replacement]);
            }
            $pattern = $op;
        }
        // Set params
        $this->_params = array_merge($this->_params, $params);
        return $field . " " . ($replacement ? str_replace("?", $replacement, $pattern) : $pattern);
    }

    /**
     * @param array $data rules configuration
     * @return string the SQL WHERE statement
     */
    protected function buildWhere($data)
    {
        if (!isset($data['rules']) || !$data['rules']) {
            return null;
        }

        $criteria =  new CDbCriteria();

        $condition = " " . $data['condition'] . " ";

        foreach ($data['rules'] as $rule) {
            if (isset($rule['condition'])) {
                $criteria->addCondition($this->buildWhere($rule),$condition);
            } else {
                $params = [];
                $operator = $rule['operator'];
                $field = $rule['field'];
                $value = self::getValue($rule, 'value');

                if ($value !== null) {
                    $i = count($this->_params);

                    if (!is_array($value)) {
                        $value = [$value];
                    }

                    foreach ($value as $v) {
                        $params[":".$this->getNewParamName()] = $v;
                        $i++;
                    }
                }
                $mCondition = $this->encodeRule($field, $operator, $params);
                $criteria->addCondition($mCondition,$condition);
            }
        }

        return $criteria->condition;

    }

    /**
     * Returns query WHERE condition.
     * @return string
     */
    public function getWhere()
    {
        return $this->_where;
    }

    /**
     * Returns the parameters to be bound to the query.
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * Get a param name that should not conflict with any params already set
     * @return string
     */
    private function getNewParamName(){
        $paramPrefix = 'p';
        if(!empty($this->currentParams) && $this->paramsCount < count($this->currentParams) ){
            $this->paramsCount = count($this->currentParams) +1;
        }else{
            $this->paramsCount = $this->paramsCount + 1;
        }
        return $paramPrefix.$this->paramsCount;
    }
    /**
     * @param array $currentParams
     */
    public function setCurrentParams($currentParams) {
        $this->currentParams = $currentParams;
    }

    /**
     * NB! This is a copy-paste from Yii2 ArrayHelper
     */
    public static function getValue($array, $key, $default = null)
    {
        if ($key instanceof \Closure) {
            return $key($array, $default);
        }

        if (is_array($key)) {
            $lastKey = array_pop($key);
            foreach ($key as $keyPart) {
                $array = static::getValue($array, $keyPart);
            }
            $key = $lastKey;
        }

        if (is_array($array) && (isset($array[$key]) || array_key_exists($key, $array))) {
            return $array[$key];
        }

        if (($pos = strrpos($key, '.')) !== false) {
            $array = static::getValue($array, substr($key, 0, $pos), $default);
            $key = substr($key, $pos + 1);
        }

        if (is_object($array)) {
            // this is expected to fail if the property does not exist, or __get() is not implemented
            // it is not reliably possible to check whether a property is accessible beforehand
            return $array->$key;
        } elseif (is_array($array)) {
            return (isset($array[$key]) || array_key_exists($key, $array)) ? $array[$key] : $default;
        }

        return $default;
    }


}