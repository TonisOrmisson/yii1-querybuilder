<?php

/**
 * Class Translator
 * @param array $params
 * @param array $where
 */
class Translator extends CModel
{
    private $_where;
    private $_params = [];
    private $_operators;

    /**
     * Constructors.
     * @param array $data Rules configuration
     * @param array $config the configuration array to be applied to this object.
     */
    public function __construct($data, $config = [])
    {
        $this->_where = $this->buildWhere($data);
    }

    /**
     * @inheritdoc
     */
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
     * Returns the list of attribute names of the model.
     * @return array list of attribute names.
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

        $this->_params = array_merge($this->_params, $params);
        return $field . " " . ($replacement ? str_replace("?", $replacement, $pattern) : $pattern);
    }

    /**
     * @param array $data rules configuration
     * @return string the WHERE clause
     */
    protected function buildWhere($data)
    {
        if (!isset($data['rules']) || !$data['rules']) {
            return '';
        }

        $where = [];
        $condition = " " . $data['condition'] . " ";

        foreach ($data['rules'] as $rule) {
            if (isset($rule['condition'])) {
                $where[] = $this->buildWhere($rule);
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
                        $params[":p$i"] = $v;
                        $i++;
                    }
                }
                $where[] = $this->encodeRule($field, $operator, $params);
            }
        }
        return "(" . implode($condition, $where) . ")";
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
     * Retrieves the value of an array element or object property with the given key or property name.
     * If the key does not exist in the array or object, the default value will be returned instead.
     *
     * The key may be specified in a dot format to retrieve the value of a sub-array or the property
     * of an embedded object. In particular, if the key is `x.y.z`, then the returned value would
     * be `$array['x']['y']['z']` or `$array->x->y->z` (if `$array` is an object). If `$array['x']`
     * or `$array->x` is neither an array nor an object, the default value will be returned.
     * Note that if the array already has an element `x.y.z`, then its value will be returned
     * instead of going through the sub-arrays. So it is better to be done specifying an array of key names
     * like `['x', 'y', 'z']`.
     *
     * Below are some usage examples,
     *
     * ```php
     * // working with array
     * $username = \yii\helpers\ArrayHelper::getValue($_POST, 'username');
     * // working with object
     * $username = \yii\helpers\ArrayHelper::getValue($user, 'username');
     * // working with anonymous function
     * $fullName = \yii\helpers\ArrayHelper::getValue($user, function ($user, $defaultValue) {
     *     return $user->firstName . ' ' . $user->lastName;
     * });
     * // using dot format to retrieve the property of embedded object
     * $street = \yii\helpers\ArrayHelper::getValue($users, 'address.street');
     * // using an array of keys to retrieve the value
     * $value = \yii\helpers\ArrayHelper::getValue($versions, ['1.0', 'date']);
     * ```
     *
     * @param array|object $array array or object to extract value from
     * @param string|\Closure|array $key key name of the array element, an array of keys or property name of the object,
     * or an anonymous function returning the value. The anonymous function signature should be:
     * `function($array, $defaultValue)`.
     * The possibility to pass an array of keys is available since version 2.0.4.
     * @param mixed $default the default value to be returned if the specified array key does not exist. Not used when
     * getting value from an object.
     * @return mixed the value of the element if found, default value otherwise
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