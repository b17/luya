<?php
namespace cmsadmin\models;

class Block extends \admin\ngrest\base\Model
{
    public $ngRestEndpoint = 'api-cms-block';

    public function ngRestConfig($config)
    {
        $config->list->field("name", "Name")->text()->required();

        $config->create->field("name", "Name")->text()->required();
        $config->create->field("json_config", "JSON Config")->ace(['mode' => 'json']);
        $config->create->field("twig_frontend", "Twig Frontend")->ace(['mode' => 'twig']);
        $config->create->field("twig_admin", "Twig Admin")->ace(['mode' => 'twig']);

        $config->update->copyFrom('create');

        return $config;
    }

    public static function tableName()
    {
        return 'cms_block';
    }

    public function rules()
    {
        return [
            [['name', 'json_config', 'twig_frontend', 'twig_admin'], 'required']
        ];
    }

    public function scenarios()
    {
        return [
            'restcreate' => ['name', 'json_config', 'twig_frontend', 'twig_admin'],
            'restupdate' => ['name', 'json_config', 'twig_frontend', 'twig_admin'],
        ];
    }
}
