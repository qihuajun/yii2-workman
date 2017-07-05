<?php

namespace rossoneri\workman\job;

/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 上午11:09
 */
/**
 * Interface JobInterface
 * @package rossoneri\workman\job
 */
interface JobInterface
{

    const STATUS_NEW = 'NEW';
    const STATUS_DELAYED = 'DELAYED';
    const STATUS_READY = 'READY';
    const STATUS_RESERVED = 'RESERVED';
    const STATUS_BURIED ='BURIED';

    /**
     * get job id
     *
     * @return mixed
     */
    public function getId();

    /**
     * set job id
     *
     * @param $id
     * @return mixed
     */
    public function setId($id);

    /**
     * get job name
     *
     * @return mixed
     */
    public function getName();

    /**
     * set job name
     *
     * @param $name
     * @return mixed
     */
    public function setName($name);

    /**
     *
     * get job status
     *
     * @return mixed
     */
    public function getStatus();

    /**
     *
     * set job status
     *
     * @param $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * get job data
     *
     * @return mixed
     */
    public function getData();

    /**
     * run the job
     *
     * @return mixed
     */
    public function run();

    /**
     * get job's max tries
     *
     * @return mixed
     */
    public function getMaxTries();

    /**
     * whether the job can be buried
     *
     * @return mixed
     */
    public function canBeBuried();

    /**
     * handle job's failing
     *
     * @return mixed
     */
    public function failed();
}