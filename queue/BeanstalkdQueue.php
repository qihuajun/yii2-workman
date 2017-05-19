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
use yii\base\Component;
use rossoneri\workman\exception\InvalidJobException;
use rossoneri\workman\job\JobInterface;

class BeanstalkdQueue extends Component implements QueueInterface
{
    public $server;

    /**
     * @var Pheanstalk
     */
    private $pheanstalk;

    public function init()
    {
        parent::init();

        $this->pheanstalk = new Pheanstalk($this->server);
    }

    private function transformToQueueJob(JobInterface $job){
        return new Job($job->getId(),$job->getData());
    }

    private function transformFromQueueJob(Job $job){
        $data = $job->getData();

        $object = \Yii::createObject($data);

        if(! $object instanceof JobInterface){
            throw new InvalidJobException();
        }
    }


    public function putInTube(JobInterface $job, $tube = 'default', $priority = 100, $delay = 0, $ttr=10)
    {
        return $this->pheanstalk->putInTube($tube,$job->getData(),$priority,$delay,$ttr);
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
}