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

    public function dateToShams(): string
    {
        include_once __DIR__ . '/../../../../vendor/jdf/jdf.php';

        $timestamp = $this->time->getTimestamp();

        date_default_timezone_set("Asia/Tehran");

        $shamsiDate = jdate('Y-m-d H:i:s', $timestamp);

        return $shamsiDate;

    }




}