<?php
/**
 * Create Calendar Array with AstroTime
 *  Copyright (c) r28 (https://redmagic.cc)
 *
 * @require r28/AstroTime
 * @require settings/leap.ini   : Results of implementation 'Leap second', and defference between UTC and TAI
 * 
 *  Licensed under The MIT License
 *  Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) r28 (https://redmagic.cc)
 * @link          https://redmagic.cc Redmagic
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License 
 */
namespace r28\AstroTime;

use \Cake\Chronos;
use \RuntimeException;
use \InvalidArgumentException;

class AstroCalendar
{

    public $calendar;

    public $start_of_week;

    /**
     * Constructor
     * 
     * @param   AstroTime   $time
     * @param   string      $type   Type of calendar
     *                      - month     : Monthly
     *                      - year      : Yearly
     * @param   boolean     $is_start_sunday    (default: false)
     */
    public function __construct(AstroTime $time, $type='month', $is_start_sunday=false) {
        $year = $time->year;
        $month = $time->month;
        if ($type == 'month') {
            $cal = static::createFromMonth($year, $month, $is_start_sunday);
        } elseif ($type == 'year') {
            $cal = static::createFromYear($year);
        }

        $this->calendar = $cal;
    }

    /**
     * Create calendary array from year & month
     * 
     * @param   integer     $year
     * @param   integer     $month
     * @param   boolean     $is_start_sunday    true: Day of week is Sunday (default: false)
     * @param   string      $fmt                Date format of value of array (null: AstroTime instance)
     * @return  array       [ <week> => [ <num>=><Date>, ... ], ...]
     */
    public static function createFromMonth($year, $month, $is_start_sunday=false, $fmt=null) {
        $time = static::getFirstTime($year, $month);
        $start = static::getStart($time, $is_start_sunday);
        $end   = static::getEnd($time, $is_start_sunday, 'month');

        $cal = static::createFromPeriods($start, $end, $is_start_sunday, $fmt);
        return $cal;
    }

    /**
     * Create calendary array from year
     * 
     * @param   integer     $year
     * @param   boolean     $is_start_sunday    true: Day of week is Sunday (default: false)
     * @param   string      $fmt                Date format of value of array (null: AstroTime instance)
     * @return  array       [ <week> => [ <num>=><Date>, ... ], ...]
     */
    public static function createFromYear($year, $is_start_sunday=false, $fmt=null) {
        $time = static::getFirstTime($year);
        $start = static::getStart($time, $is_start_sunday);
        $end   = static::getEnd($time, $is_start_sunday, 'year');

        $cal = static::createFromPeriods($start, $end, $is_start_sunday, $fmt);
        return $cal;
    }

    /**
     * Create calendary monthly array from year
     * 
     * @param   integer     $year
     * @param   boolean     $is_start_sunday    true: Day of week is Sunday (default: false)
     * @param   string      $fmt                Date format of value of array (null: AstroTime instance)
     * @return  array       [ <month> => [ <week> => [ <num>=><Date>, ... ], ...], ...]
     */
    public static function createFromYearMonthly($year, $is_start_sunday=false, $fmt=null) {
        $cal = [];

        for($m=1; $m<=12; $m++) {
            $_cal = static::createFromMonth($year, $m, $is_start_sunday, $fmt);
            $cal[$m] = $_cal;
        }
        return $cal;
    }

    /**
     * Specify the periods
     * 
     * @param   AstroTime   $start  Start date
     * @param   AstroTime   $end    End date
     * @param   boolean     $is_start_sunday    (default: false)
     */
    public static function createFromPeriods(AstroTime $start, AstroTime $end, $is_start_sunday, $fmt=null) {
        $w = 1;
        $cal = [];
        $time = $start;

        while($time->lte($end)) {
            $cal[$w][] = (is_null($fmt)) ? $time : $time->format($fmt);
            if (static::isWeekEnd($time, $is_start_sunday)) $w++;

            $time = $time->addDay(1);
        }
        return $cal;
    }

    /**
     * Is time WeekEnd?
     * 
     * @param   AstroTime   $time
     * @param   boolean     $is_start_sunday    (default: false)
     * @return  boolean
     */
    public static function isWeekEnd($time, $is_start_sunday=false) {
        if ($is_start_sunday) return $time->isSaturday();
        return $time->isSunday();
    }

    /**
     * Create first of year or month as AstroTime Object
     * 
     * @param   integer     $year
     * @param   integer     $month  default: 1
     */
    private static function getFirstTime($year, $month=1) {
        return new AstroTime("{$year}-{$month}-1 00:00:00");
    }

    /**
     * Start datetime
     * 
     * @param   AstroTime   $time
     * @param   boolean     $is_start_sunday
     * @return  AstroTime
     */
    private static function getStart(AstroTime $time, $is_start_sunday=false) {
        if ($is_start_sunday && $time->isSunday()) {
            return $time;
        }

        $start = $time->startOfWeek();
        if ($is_start_sunday) {
            $start = $start->subDay(1);
        }
        return $start;
    }

    /**
     * End datetime
     * 
     * @param   AstroTime   $time
     * @param   boolean     $is_start_sunday
     * @return  AstroTime
     */
    private static function getEnd(AstroTime $time, $is_start_sunday=false, $type='month') {
        $time = ($type === 'year') ? $time->endOfYear() : $time->endOfMonth();
        if ($is_start_sunday) {
            if ($time->isSunday()) {
                $end = $time->addDay(6);
            } else {
                $time = $time->endOfWeek();
                $end = $time->subDay(1);
            }
        } else {
            $end = $time->endOfWeek();
        }
        return $end;
    }
}