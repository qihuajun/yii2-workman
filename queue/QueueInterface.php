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
    public function useTube($tube);

    public function put(JobInterface $job,$priority = 100, $delay = 0, $ttr=10);

    public function putInTube(JobInterface $job,$tube='default',$priority = 100, $delay = 0, $ttr=10);

    public function reserve($timeout=null);

    public function reserveFromTube($tube='default',$timeout=null);

    public function delete(JobInterface $job);

    public function release(JobInterface $job,$priority = 100, $delay = 0);

    public function bury(JobInterface $job, $priority = 100);

    public function touch(JobInterface $job);

    public function watch($tube);

    public function watchOnly($tube);

    public function kick($bound);

    public function getAttempted(JobInterface $job);

}