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
    const DEFAULT_PRIORITY = 100;

    const DEFAULT_DELAY = 0;

    const DEFAULT_TUBE = 'default';

    const DEFAULT_TTR = 60;

    public function useTube($tube);

    public function put(JobInterface $job,$priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR);

    public function putRaw($data, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR);

    public function putInTube(JobInterface $job, $tube=self::DEFAULT_TUBE, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR);

    public function putRawInTube($data, $tube=self::DEFAULT_TUBE, $priority = self::DEFAULT_PRIORITY, $delay = self::DEFAULT_DELAY, $ttr=self::DEFAULT_TTR);

    public function reserve($timeout=null);

    public function reserveRaw($timeout=null);

    public function reserveFromTube($tube=self::DEFAULT_TUBE,$timeout=null);

    public function reserveRawFromTube($tube=self::DEFAULT_TUBE,$timeout=null);

    public function delete(JobInterface $job);

    public function release(JobInterface $job,$priority = 100, $delay = 0);

    public function bury(JobInterface $job, $priority = 100);

    public function touch(JobInterface $job);

    public function watch($tube);

    public function watchOnly($tube);

    public function kick($bound);

    public function getAttempted(JobInterface $job);

}