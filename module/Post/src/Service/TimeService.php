<?php

namespace Post\Service;


use function jdate;

class TimeService
{
    private $time;

    public function __construct(string $time)
    {
        $this->time = $time;
    }

    public function dateToShamsi()
    {
        include_once __DIR__ . '/../../../../vendor/jdf/jdf.php';

        $timestamp = strtotime($this->time);

        $shamsiDate = jdate('Y-m-d H:i:s', $timestamp);

        return $shamsiDate;

    }



}