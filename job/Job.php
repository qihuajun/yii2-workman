<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 上午11:25
 */

namespace rossoneri\workman\job;


use yii\base\Component;

abstract class Job extends Component implements JobInterface
{
    public $id;

    public $name;

    public $status = self::STATUS_NEW;

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
}