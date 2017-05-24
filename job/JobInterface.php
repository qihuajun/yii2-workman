<?php

namespace rossoneri\workman\job;

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

    public function setId($id);

    public function getName();

    public function setName($name);

    public function getStatus();

    public function setStatus($status);

    public function getData();

    public function run();

    public function getMaxTries();

    public function canBeBuried();

    public function failed();
}