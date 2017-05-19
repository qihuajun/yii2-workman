<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/19
 * Time: 下午5:30
 */

namespace rossoneri\workman\job;


use yii\base\Model;

abstract class BaseJob extends Model implements JobInterface
{
    public $id;

    public $name;

    public $status = self::STATUS_NEW;

    public $canBeReleased = false;

    public $canBeBuried = false;

    public function init()
    {
        parent::init();

        if(!$this->id){
            $this->id = uniqid();
        }

        if(!$this->name){
            $this->name = self::className();
        }
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }


    public function run()
    {
        // TODO: Implement run() method.
    }

    public function getData()
    {
        $data = $this->attributes;
        $data['class'] = self::className();

        return $data;
    }

    public function canBeReleased()
    {
        return $this->canBeReleased;
    }

    public function canBeBuried()
    {
        return $this->canBeBuried;
    }
}