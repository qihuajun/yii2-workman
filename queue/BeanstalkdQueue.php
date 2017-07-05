<?php
/**
 * Created by PhpStorm.
 * User: qihuajun
 * Date: 2017/5/18
 * Time: 下午2:52
 */

namespace rossoneri\workman\queue;


use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;
use rossoneri\workman\exception\InvalidJobException;
use rossoneri\workman\job\JobInterface;
use yii\base\Component;
use yii\helpers\Json;

/**
 * Class BeanstalkdQueue
 * @package rossoneri\workman\queue
 */
class BeanstalkdQueue extends Component implements QueueInterface
{
    /**
     * @var string
     */
    public $host;

    /**
     * @var int
     */
    public $port = PheanstalkInterface::DEFAULT_PORT;
    /**
     * @var int
     */
    public $connectTimeout = 30;
    /**
     * @var bool
     */
    public $connectPersistent = true;

    /**
     * @var Pheanstalk
     */
    private $pheanstalk;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->pheanstalk = new Pheanstalk($this->host,$this->port,$this->connectTimeout,$this->connectPersistent);
    }

    /**
     * convert a BaseJob instance to Job instance
     *
     * @param JobInterface $job
     * @return Job
     */
    private function transformToQueueJob(JobInterface $job){
        return new Job($job->getId(),Json::encode($job->getData()));
    }

    /**
     * convert a BaseJob instance from a Job instance
     *
     * @param Job $job
     * @return object
     * @throws InvalidJobException
     */
    private function transformFromQueueJob(Job $job){
        $data = $job->getData();
        $data = Json::decode($data);

        $object = \Yii::createObject($data);

        if(! $object instanceof JobInterface){
            throw new InvalidJobException();
        }

        $object->setId($job->getId());

        return $object;
    }


    /**
     * @inheritdoc
     */
    public function putInTube(JobInterface $job, $tube=self::DEFAULT_TUBE, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR)
    {
        $data = $job->getData();
        $data = Json::encode($data);
        return $this->pheanstalk->putInTube($tube,$data,$priority,$delay,$ttr);
    }

    /**
     * @inheritdoc
     */
    public function reserveFromTube($tube=self::DEFAULT_TUBE, $timeout=null)
    {
        $job = $this->pheanstalk->reserveFromTube($tube,$timeout);
        return $this->transformFromQueueJob($job);
    }


    /**
     * @inheritdoc
     */
    public function delete(JobInterface $job)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->delete($queueJob);
    }

    /**
     * @inheritdoc
     */
    public function release(JobInterface $job, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->release($queueJob,$delay);
    }

    /**
     * @inheritdoc
     */
    public function bury(JobInterface $job, $priority = self::DEFAULT_PRIORITY)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->bury($queueJob,$priority);
    }

    /**
     * @inheritdoc
     */
    public function touch(JobInterface $job)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->touch($queueJob);
    }

    /**
     * @inheritdoc
     */
    public function watch($tube)
    {
        $this->pheanstalk->watch($tube);
    }

    /**
     * @inheritdoc
     */
    public function kick($bound)
    {
        $this->pheanstalk->kick($bound);
    }

    /**
     * @inheritdoc
     */
    public function useTube($tube)
    {
        $this->pheanstalk->useTube($tube);
    }

    /**
     * @inheritdoc
     */
    public function put(JobInterface $job, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR)
    {
        return $this->pheanstalk->put($job->getData(),$priority,$delay,$ttr);
    }

    /**
     * @inheritdoc
     */
    public function reserve($timeout = null)
    {
        $job = $this->pheanstalk->reserve($timeout);
        if($job){
            return $this->transformFromQueueJob($job);
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function watchOnly($tube)
    {
        $this->pheanstalk->watchOnly($tube);
    }

    /**
     * @inheritdoc
     */
    public function getAttempted(JobInterface $job)
    {
        $queueJob = $this->transformToQueueJob($job);
        $stats = $this->pheanstalk->statsJob($queueJob);
        return (int) $stats->reserves;
    }

    /**
     * @inheritdoc
     */
    public function putRaw($data, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr = self::DEFAULT_TTR)
    {
        return $this->pheanstalk->put($data,$priority,$delay,$ttr);
    }

    /**
     * @inheritdoc
     */
    public function putRawInTube($data, $tube = self::DEFAULT_TUBE, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr = self::DEFAULT_TTR)
    {
        return $this->pheanstalk->putInTube($tube,$data,$priority,$delay,$ttr);
    }

    /**
     * @inheritdoc
     */
    public function reserveRaw($timeout = null)
    {
        $job = $this->pheanstalk->reserve($timeout);
        return $job;
    }

    /**
     * @inheritdoc
     */
    public function reserveRawFromTube($tube = self::DEFAULT_TUBE, $timeout = null)
    {
        $job = $this->pheanstalk->reserveFromTube($tube,$timeout);
        return $job;
    }

    /**
     * return queue's stats
     *
     * @return mixed
     */
    public function stats()
    {
        $stats = $this->pheanstalk->stats();
        $data = [];
        foreach ($stats as $key => $stat) {
            $data[$key] = $stat;
        }

        return $data;
    }

    /**
     * return tube stats
     *
     * @return mixed
     */
    public function statsTube($tube)
    {
        $stats = $this->pheanstalk->statsTube($tube);
        $data = [];
        foreach ($stats as $key => $stat) {
            $data[$key] = $stat;
        }

        return $data;
    }

    /**
     * return job stats
     *
     * @param Job $job
     * @return mixed
     */
    public function statsJob(Job $job)
    {
        $stats = $this->pheanstalk->statsJob($job);
        $data = [];
        foreach ($stats as $key => $stat) {
            $data[$key] = $stat;
        }

        return $data;
    }

    /**
     * list tubes
     *
     * @return mixed
     */
    public function listTubes()
    {
        return $this->pheanstalk->listTubes();
    }
}