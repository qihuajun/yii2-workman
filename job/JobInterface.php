<?php

namespace yii\workman;

/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 上午11:09
 */
interface JobInterface
{
    const STATUS_NEW = 'NEW';
    const STATUS_DELAYED = 'DELAYED';
    const STATUS_READY = 'READY';
    const STATUS_RESERVED = 'RESERVED';
    const STATUS_BURIED ='BURIED';

    public function getId();

    public function setId();

    public function getName();

    public function setName();

    public function getStatus();

    public function setStatus();

    public function run();
}