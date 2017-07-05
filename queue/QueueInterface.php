<?php
namespace rossoneri\workman\queue;
use Pheanstalk\Job;
use rossoneri\workman\job\JobInterface;

/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 上午11:47
 */
interface QueueInterface
{
    /**
     *
     */
    const DEFAULT_PRIORITY = 100;

    /**
     *
     */
    const DEFAULT_DELAY = 0;

    /**
     *
     */
    const DEFAULT_TUBE = 'default';

    /**
     *
     */
    const DEFAULT_TTR = 60;

    /**
     * @param $tube
     * @return mixed
     */
    public function useTube($tube);

    /**
     * put job in queue
     *
     * @param JobInterface $job
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     * @return mixed
     */
    public function put(JobInterface $job, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR);

    /**
     * put raw data in queue
     *
     * @param $data
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     * @return mixed
     */
    public function putRaw($data, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR);

    /**
     * put job into a tube
     *
     * @param JobInterface $job
     * @param string $tube
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     * @return mixed
     */
    public function putInTube(JobInterface $job, $tube=self::DEFAULT_TUBE, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR);

    /**
     * put raw data in a tube
     *
     * @param $data
     * @param string $tube
     * @param int $priority
     * @param int $delay
     * @param int $ttr
     * @return mixed
     */
    public function putRawInTube($data, $tube=self::DEFAULT_TUBE, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR);

    /**
     * reserve job
     *
     * @param null $timeout
     * @return mixed
     */
    public function reserve($timeout=null);

    /**
     * reserve raw data
     *
     * @param null $timeout
     * @return mixed
     */
    public function reserveRaw($timeout=null);

    /**
     * reserve a job from tube
     *
     * @param string $tube
     * @param null $timeout
     * @return mixed
     */
    public function reserveFromTube($tube=self::DEFAULT_TUBE, $timeout=null);

    /**
     * reserve raw data from a tube
     *
     * @param string $tube
     * @param null $timeout
     * @return mixed
     */
    public function reserveRawFromTube($tube=self::DEFAULT_TUBE, $timeout=null);

    /**
     * delete a job
     *
     * @param JobInterface $job
     * @return mixed
     */
    public function delete(JobInterface $job);

    /**
     * release a job
     *
     * @param JobInterface $job
     * @param int $priority
     * @param int $delay
     * @return mixed
     */
    public function release(JobInterface $job, $priority = 100, $delay = 0);

    /**
     * bury a job
     *
     * @param JobInterface $job
     * @param int $priority
     * @return mixed
     */
    public function bury(JobInterface $job, $priority = 100);

    /**
     * touch a job
     *
     * @param JobInterface $job
     * @return mixed
     */
    public function touch(JobInterface $job);

    /**
     * watch a tube
     *
     * @param $tube
     * @return mixed
     */
    public function watch($tube);

    /**
     * watch a tube only
     *
     * @param $tube
     * @return mixed
     */
    public function watchOnly($tube);

    /**
     * kick
     *
     * @param $bound
     * @return mixed
     */
    public function kick($bound);

    /**
     * get attempted count of a job
     *
     * @param JobInterface $job
     * @return mixed
     */
    public function getAttempted(JobInterface $job);

    /**
     * return queue's stats
     *
     * @return mixed
     */
    public function stats();

    /**
     * return tube stats
     *
     * @return mixed
     */
    public function statsTube($tube);

    /**
     * return job stats
     *
     * @param Job $job
     * @return mixed
     */
    public function statsJob(Job $job);

    /**
     * list tubes
     *
     * @return mixed
     */
    public function listTubes();

}