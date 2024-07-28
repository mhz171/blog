<?php

namespace Post\Service;


use function jdate;

class TimeService
{
    private $time;

    public function __construct(\DateTime $time)
    {
        $this->time = $time;
    }

    public function dateToShamsi()
    {
        include_once __DIR__ . '/../../../../vendor/jdf/jdf.php';

        $timestamp = $this->time->getTimestamp();

        $shamsiDate = jdate('Y-m-d H:i:s', $timestamp);

        return $shamsiDate;

    }

    public function dateToMiladi()
    {
        include_once __DIR__ . '/../../../../vendor/jdf/jdf.php';

        $timestamp = jdate('Y-m-d H:i:s', strtotime($this->time));

        return $timestamp;
    }



}