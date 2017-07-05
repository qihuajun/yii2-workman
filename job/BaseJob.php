<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/19
 * Time: 下午5:30
 */

namespace rossoneri\workman\job;


use yii\base\Model;

/**
 * Class BaseJob
 * @package rossoneri\workman\job
 */
abstract class BaseJob extends Model implements JobInterface
{
    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $name;

    /**
     * @var string
     */
    public $status = self::STATUS_NEW;

    /**
     * @var int
     */
    public $maxTries = 1;

    /**
     * @var bool
     */
    public $canBeBuried = false;

    /**
     *
     */
    public function init()
    {
        parent::init();

        if(!$this->name){
            $this->name = self::className();
        }
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }


    /**
     * @inheritdoc
     */
    public function run(){}


    /**
     * @inheritdoc
     */
    public function getData()
    {
        $data = $this->attributes;
        $data['class'] = self::className();

        return $data;
    }

    /**
     * @inheritdoc
     */
    public function getMaxTries()
    {
        return $this->maxTries;
    }

    /**
     * @inheritdoc
     */
    public function canBeBuried()
    {
        return $this->canBeBuried;
    }

    /**
     * @inheritdoc
     */
    public function failed()
    {

    }
}