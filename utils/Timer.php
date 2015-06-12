<?php

namespace Utils;

class Timer {

    private $start_time;
    private $end_time;

    public function start($float = true){
        $this->start_time = microtime($float);
    }

    public function stop()
    {
        $this->end_time = microtime(true);
    }

    public function getTime()
    {
        return $this->end_time - $this->start_time;
    }
}