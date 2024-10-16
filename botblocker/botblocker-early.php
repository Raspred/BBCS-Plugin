<?php 
// If this file is called directly, abort.
if (!defined('ABSPATH') || !defined('WPINC') || !defined('BOTBLOCKER')) {
    exit;
}

require_once __DIR__ . '/helpers.php';

class BotBlockerEarly
{
    private $startTime;
    private $finishTime;

    public function __construct()
    {

    }

    private function setStartTime()
    {
        $this->startTime = time();
    }

    private function setFinishTime()
    {
        $this->finishTime = time();
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getFinishTime()
    {
        return $this->finishTime;
    }

    public function getExecutionTime()
    {
        if (isset($this->startTime) && isset($this->finishTime)) {
            return $this->finishTime - $this->startTime;
        }
        return null;
    }

    public function run()
    {
        /* */
    }
}