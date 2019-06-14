<?php
//namespace r28\AstroTime\Tests;

use Cake\Chronos\Chronos;
use r28\AstroTime\AstroTime;

class AstroTimeJulianTest extends  PHPUnit\Framework\TestCase
{
    //const DATE_STRING = "1582-10-14 00:00:00";
    //const RETURN_DATE_STRING = "1582-10-24 00:00:00";
    //const JD  =  2299169.125;
    //const MJD =  -100831.37500;
    //const JULIAN_CENTURY = -4.1718240931;
    const DATE_STRING = "1582-10-01 00:00:00";
    const RETURN_DATE_STRING = "1582-10-01 00:00:00";
    const TIMEZONE = 'Asia/Tokyo';
    const JD  = 2299156.125;
    const JD_GREGORIAN = 2299146.125;
    const MJD = -100844.375;
    const JULIAN_CENTURY = -4.1721800137;

    public function setUp()
    {
        date_default_timezone_set(self::TIMEZONE);
        $this->at = new AstroTime(self::DATE_STRING, self::TIMEZONE);
    }

    /**
     * 暦種別
     */
    public function test_calendar_type() {
        $this->assertEquals('julian', $this->at->calendar_type);
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
        $time = new Chronos(self::DATE_STRING);
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
    public function test_julian2DateString() {
        $this->assertEquals(self::RETURN_DATE_STRING, AstroTime::julian2Datestring(self::JD, 'Y-m-d H:i:s', self::TIMEZONE));
    }

    public function test_jd_gregorian() {
        $jd_gregorian = $this->at->jd_gregorian;
        $this->assertEquals(self::JD_GREGORIAN, $jd_gregorian);
    }

}
