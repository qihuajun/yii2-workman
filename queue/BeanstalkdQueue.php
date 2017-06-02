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
use yii\base\Component;
use rossoneri\workman\exception\InvalidJobException;
use rossoneri\workman\job\JobInterface;
use yii\helpers\Json;

class BeanstalkdQueue extends Component implements QueueInterface
{
    public $host;
    public $port = PheanstalkInterface::DEFAULT_PORT;
    public $connectTimeout = 30;
    public $connectPersistent = true;

    /**
     * @var Pheanstalk
     */
    private $pheanstalk;

    public function init()
    {
        parent::init();

        $this->pheanstalk = new Pheanstalk($this->host,$this->port,$this->connectTimeout,$this->connectPersistent);
    }

    private function transformToQueueJob(JobInterface $job){
        return new Job($job->getId(),Json::encode($job->getData()));
    }

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


    public function putInTube(JobInterface $job, $tube=self::DEFAULT_TUBE, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR)
    {
        $data = $job->getData();
        $data = Json::encode($data);
        return $this->pheanstalk->putInTube($tube,$data,$priority,$delay,$ttr);
    }

    public function reserveFromTube($tube=self::DEFAULT_TUBE,$timeout=null)
    {
        $job = $this->pheanstalk->reserveFromTube($tube,$timeout);
        return $this->transformFromQueueJob($job);
    }


    public function delete(JobInterface $job)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->delete($queueJob);
    }

    public function release(JobInterface $job, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->release($queueJob,$delay);
    }

    public function bury(JobInterface $job, $priority = self::DEFAULT_PRIORITY)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->bury($queueJob,$priority);
    }

    public function touch(JobInterface $job)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->touch($queueJob);
    }

    public function watch($tube)
    {
        $this->pheanstalk->watch($tube);
    }

    public function kick($bound)
    {
        $this->pheanstalk->kick($bound);
    }

    public function useTube($tube)
    {
        $this->pheanstalk->useTube($tube);
    }

    public function put(JobInterface $job, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR)
    {
        return $this->pheanstalk->put($job->getData(),$priority,$delay,$ttr);
    }

    public function reserve($timeout = null)
    {
        $job = $this->pheanstalk->reserve($timeout);
        if($job){
            return $this->transformFromQueueJob($job);
        }
        return null;
    }

    public function watchOnly($tube)
    {
        $this->pheanstalk->watchOnly($tube);
    }

    public function getAttempted(JobInterface $job)
    {
        $queueJob = $this->transformToQueueJob($job);
        $stats = $this->pheanstalk->statsJob($queueJob);
        return (int) $stats->reserves;
    }

    public function putRaw($data, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr = self::DEFAULT_TTR)
    {
        return $this->pheanstalk->put($data,$priority,$delay,$ttr);
    }

    public function putRawInTube($data, $tube = self::DEFAULT_TUBE, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr = self::DEFAULT_TTR)
    {
        return $this->pheanstalk->putInTube($tube,$data,$priority,$delay,$ttr);
    }

    public function reserveRaw($timeout = null)
    {
        $job = $this->pheanstalk->reserve($timeout);
        return $job;
    }

    public function reserveRawFromTube($tube = self::DEFAULT_TUBE, $timeout = null)
    {
        $job = $this->pheanstalk->reserveFromTube($tube,$timeout);
        return $job;
    }
}