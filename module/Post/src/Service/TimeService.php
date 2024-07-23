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

    public function gregorianToJalali($g_y, $g_m, $g_d)
    {
        $g_days_in_month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $j_days_in_month = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

        // Check for leap year
        if (($g_y % 4 == 0 && $g_y % 100 != 0) || ($g_y % 400 == 0)) {
            $g_days_in_month[1] = 29;
        }

        // Determine the days passed since 1/1/475AD
        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $gd = $g_d - 1;

        $g_day_no = 365 * $gy + (int)(($gy + 3) / 4) - (int)(($gy + 99) / 100) + (int)(($gy + 399) / 400);

        for ($i = 0; $i < $gm; ++$i) {
            $g_day_no += $g_days_in_month[$i];
        }

        $g_day_no += $gd;

        // Jalali date from 475AD
        $j_day_no = $g_day_no - 79;

        $j_np = (int)($j_day_no / 12053); // 12053 days in a 33 years period
        $j_day_no %= 12053;

        $jy = 979 + 33 * $j_np + 4 * (int)($j_day_no / 1461); // 1461 days in a 4 years period
        $j_day_no %= 1461;

        if ($j_day_no >= 366) {
            $jy += (int)(($j_day_no - 1) / 365);
            $j_day_no = ($j_day_no - 1) % 365;
        }

        for ($i = 0; ($i < 11 && $j_day_no >= $j_days_in_month[$i]); ++$i) {
            $j_day_no -= $j_days_in_month[$i];
        }

        $jm = $i + 1;
        $jd = $j_day_no + 1;

        return [$jy, $jm, $jd];
    }

    public function dateToShamsi()
    {
        include_once __DIR__ . '/../../../../vendor/jdf/jdf.php';

        $timestamp = strtotime($this->time);

        $shamsiDate = jdate('Y-m-d H:i:s', $timestamp);

        return $shamsiDate;

    }



}