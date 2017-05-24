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


    public function putInTube(JobInterface $job, $tube = 'default', $priority = 100, $delay = 0, $ttr=10)
    {
        $data = $job->getData();
        $data = Json::encode($data);
        return $this->pheanstalk->putInTube($tube,$data,$priority,$delay,$ttr);
    }

    public function reserveFromTube($tube='default',$timeout=null)
    {
        $job = $this->pheanstalk->reserveFromTube($tube,$timeout);
        return $this->transformFromQueueJob($job);
    }


    public function delete(JobInterface $job)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->delete($queueJob);
    }

    public function release(JobInterface $job, $priority = 100, $delay = 0)
    {
        $queueJob = $this->transformToQueueJob($job);
        $this->pheanstalk->release($queueJob,$delay);
    }

    public function bury(JobInterface $job, $priority = 100)
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

    public function put(JobInterface $job, $priority = 100, $delay = 0, $ttr = 10)
    {
        $this->pheanstalk->put($job->getData(),$priority,$delay,$ttr);
    }

    public function reserve($timeout = null)
    {
        $job = $this->pheanstalk->reserve($timeout);
        return $this->transformFromQueueJob($job);
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
}