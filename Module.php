<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/31
 * Time: 下午5:27
 */

namespace rossoneri\workman;


use yii\filters\AccessControl;

class Module extends \yii\base\Module
{
    /**
     * Specify the roles which have permission to access this module
     *
     * @var array
     */
    public $roles = ['@'];

    /**
     * array list of user IP addresses that can access this module.
     *
     * @var array
     */
    public $ips;


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => $this->roles,
                        'ips' => $this->ips
                    ]
                ],
            ]
        ];
    }

}