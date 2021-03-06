<?php

namespace admin\ngrest;

use Exception;
use luya\helpers\ArrayHelper;

/**
 * Create NgRest config from Array.
 * 
 * Example config array to `setConfig($array)`:.
 * 
 * ```php
 * $array = [
 *     'list' => [
 *         'firstname' => [
 *             'name' => 'firstname',
 *             'alias' => 'Vorname',
 *             'i18n' => false,
 *             'extraField' => false,
 *             'type' => [
 *                 'class' => '\\admin\\ngrest\\plugins\\Text',
 *                 'args' => ['arg1' => 'arg1_value', 'arg2' => 'arg2_value']
 *             ]
 *         ]
 *     ],
 *     'create' => [
 *         //...
 *     ]
 * ];
 * ```
 *
 * @author nadar
 */
class Config extends \yii\base\Object implements \admin\ngrest\interfaces\Config
{
    private $_config = [];

    private $_plugins = null;

    private $_extraFields = null;

    private $_hash = null;

    public $apiEndpoint = null;

    public $primaryKey = null; /* not sure yet if right place to impelment about config */

    public function setConfig(array $config)
    {
        if (count($this->_config) > 0) {
            throw new Exception('Cant set config if config is not empty');
        }

        $this->_config = $config;
    }

    public function getConfig()
    {
        return $this->_config;
    }

    public function getHash()
    {
        if ($this->_hash === null) {
            $this->_hash = md5($this->apiEndpoint);
        }

        return $this->_hash;
    }

    public function hasPointer($pointer)
    {
        return array_key_exists($pointer, $this->_config);
    }

    public function getPointer($pointer)
    {
        return ($this->hasPointer($pointer)) ? $this->_config[$pointer] : false;
    }

    public function hasField($pointer, $field)
    {
        return ($this->getPointer($pointer)) ? array_key_exists($field, $this->_config[$pointer]) : false;
    }

    public function getField($pointer, $field)
    {
        return ($this->hasField($pointer, $field)) ? $this->_config[$pointer][$field] : false;
    }

    public function addField($pointer, $field, array $options = [])
    {
        if ($this->hasField($pointer, $field)) {
            return false;
        }

        $options = ArrayHelper::merge([
            'name' => null,
            'i18n' => false,
            'alias' => null,
            'type' => null,
            'extraField' => false,
        ], $options);

        // can not unshift non array value, create array for this pointer.
        if (empty($this->_config[$pointer])) {
            $this->_config[$pointer] = [];
        }

        $this->_config[$pointer] = ArrayHelper::arrayUnshiftAssoc($this->_config[$pointer], $field, $options);

        return true;
    }

    public function appendFieldOption($fieldName, $optionKey, $optionValue)
    {
        foreach ($this->getConfig() as $pointer => $fields) {
            if (is_array($fields)) {
                foreach ($fields as $field) {
                    if (isset($field['name'])) {
                        if ($field['name'] == $fieldName) {
                            $this->_config[$pointer][$field['name']][$optionKey] = $optionValue;
                        }
                    }
                }
            }
        }
    }

    public function isDeletable()
    {
        return ($this->getPointer('delete') === true) ? true : false;
    }

    /**
     * @todo: combine getPlugins and getExtraFields()
     */
    public function getPlugins()
    {
        if ($this->_plugins === null) {
            $plugins = [];
            foreach ($this->getConfig() as $pointer => $fields) {
                if (is_array($fields)) {
                    foreach ($fields as $field) {
                        if (isset($field['type'])) {
                            $fieldName = $field['name'];
                            if (!array_key_exists($fieldName, $plugins)) {
                                $plugins[$fieldName] = $field;
                            }
                        }
                    }
                }
            }
            $this->_plugins = $plugins;
        }

        return $this->_plugins;
    }

    /**
     * @todo: combine getPlugins and getExtraFields()
     */
    public function getExtraFields()
    {
        if ($this->_extraFields === null) {
            $extraFields = [];
            foreach ($this->getConfig() as $pointer => $fields) {
                if (is_array($fields)) {
                    foreach ($fields as $field) {
                        if (isset($field['extraField']) && $field['extraField']) {
                            if (!array_key_exists($field['name'], $extraFields)) {
                                $extraFields[] = $field['name'];
                            }
                        }
                    }
                }
            }
            $this->_extraFields = $extraFields;
        }

        return $this->_extraFields;
    }

    public function onFinish()
    {
        if (!$this->hasField('list', $this->primaryKey)) {
            $this->addField('list', $this->primaryKey, [
                'name' => $this->primaryKey,
                'alias' => 'ID',
                'type' => [
                    'class' => '\admin\ngrest\plugins\Text',
                    'args' => [],
                ],
            ]);
        }
    }
}
