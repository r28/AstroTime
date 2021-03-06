<?php
//namespace r28\AstroTime\Tests;

use Cake\Chronos\Chronos;
use r28\AstroTime\AstroTime;

class AstroTimeTest extends  PHPUnit\Framework\TestCase
{
    const DATE_STRING = "2019-01-01 00:00:00";
    const TIMEZONE = 'Asia/Tokyo';
    const JD = 2458484.125;
    const MJD = 58483.625;
    const JULIAN_CENTURY =  0.1899828884;
    const DELTA_T = 69.1964;

    public function setUp()
    {
        date_default_timezone_set(self::TIMEZONE);
        $this->at = new AstroTime(self::DATE_STRING, self::TIMEZONE);
    }

    /**
     * 暦種別
     */
    public function test_calendar_type() {
        $this->assertEquals('gregorian', $this->at->calendar_type);
    }

    /**
     * 日付文字列 => ユリウス日
     */
    public function test_date2Julian() {
        $this->assertEquals(self::JD, AstroTime::dateString2Julian(self::DATE_STRING, self::TIMEZONE));
    }

    /**
     * UnixTime => ユリウス日
     */
    public function test_time2Julian() {
        $utime = strtotime(self::DATE_STRING);
        $this->assertEquals(self::JD, AstroTime::time2Julian($utime, self::TIMEZONE));
    }

    /**
     * UTC => ユリウス日
     */
    public function test_utc2Julian() {
        $time = new Chronos(self::DATE_STRING, self::TIMEZONE);
        $utc = $time->setTimezone('UTC');
        $this->assertEquals(self::JD, AstroTime::utc2Julian($utc, self::TIMEZONE));
    }

    /**
     * ユリウス日
     */
    public function test_jd() {
        $time = $this->at;
        $this->assertEquals(self::JD, $time->jd);
    }

    /**
     * 修正ユリウス日
     */
    public function test_mjd() {
        $time = $this->at;
        $this->assertEquals(self::MJD, $time->mjd);
    }

    /**
     * ユリウス世紀数
     */
    public function test_jcentury() {
        $time = $this->at;
        $this->assertEquals(self::JULIAN_CENTURY, $time->jc);
    }

    /**
     * ユリウス日 => 日付文字列
     */
    public function test_julian2datestring() {
        $this->assertEquals(self::DATE_STRING, astrotime::julian2Datestring(self::JD, 'Y-m-d H:i:s', self::TIMEZONE));
    }

    /**
     * deltaT
     */
    public function test_deltaT() {
        $at = new AstroTime(self::DATE_STRING, self::TIMEZONE);
        $time = new Chronos(self::DATE_STRING, self::TIMEZONE);
        $utc = $time->setTimezone('UTC');
        $delta_ts = $at->delta_ts;
        $this->assertEquals(self::DELTA_T, AstroTime::utc2DeltaT($utc, $delta_ts));
    }

}
