<?php
/**
 * DateTime library for Astronomical calculation
 *  Copyright (c) r28 (https://redmagic.cc)
 *
 * @require cakephp/chronos     : https://github.com/cakephp/chronos
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
use Cake\Chronos\Chronos;
use \RuntimeException;
use \InvalidArgumentException;

class AstroTime
{
    /**
     * Calendar Type of Gregorian (暦種別:グレゴリオ暦)
     * @const string
     */
    const CALENDAR_TYPE_GREGORIAN = 'gregorian';

    /**
     * Calendar Type of Julian (暦種別:ユリウス暦)
     * @const string
     */
    const CALENDAR_TYPE_JULIAN = 'julian';

    /**
     * Julian and Gregorian boundary 'julian day' (UTC) (ユリウス暦とグレゴリオ暦の境界(UTC)のユリウス日)
     * @const float
     */
    const BOUNDARY_JD =  2299160.5;

    /**
     * Number of days in a year for julian (ユリウス暦の1年の日数)
     * @const float
     */
    const DAY_OF_YEAR_JULIAN = 365.25;

    /**
     * Difference between 'julian day' and 'modified julian day' (ユリウス日 - 修正ユリウス日)
     * @const float
     */
    const JD_MJD = 2400000.5;

    /**
     * Minutes of a day (1日の分数) (60 * 24)
     * @const integer
     */
    const MINUTES_OF_DAY = 1440;

    /**
     * Seconds of a day (1日の秒数) (60 * 60 * 24)
     * @const integer
     */
    const SECONDS_OF_DAY = 86400;

    /**
     * Julian day for J2000.0 (J2000.0のユリウス日)
     * @consta  float
     */
    const J2000_JD = 2451545.0;


    /**
     * @const float
     */
    const T0 = 2443144.5003725;

    /**
     * Small constants determined from the orbital motion of the earth
     *  (地球の軌道運動から定まる微小定数)
     * @const float
     */
    const LC =  1.48082686741 * (10 ** -8);

    /**
     * Another small constant determined from the earth's gravity field
     *  (地球の重力場から定まる別の微小定数)
     * @const float
     */
    const LG = 6.969290134 * (10 ** -10);

    /**
     * @const float
     */
    const LB = 1.550519768 * (10 ** -8);

    /**
     * @const float
     */
    const TDB0 =  -6.55 * (10 ** -5);

    /**
     * Difference between earth time and international atomic time (TT - TAI)
     *  (地球時と国際原子時の差)
     * @const float
     */
    const TT_TAI = 32.184;

    /**
     * @const
     */
    const DEFAULT_UTC_TAI = -37;


    /**
     * Timezone Name
     * @var string
     */
    public $timezoneName;

    /**
     * Datetime (LocalTime)
     * @var Chronos
     */
    public $local;

    /**
     * Datetime (UTC)
     * @var Chronos
     */
    public $utc;

    /**
     * Unix Timestamp (Local)
     * @var integer
     */
    public $timestamp;

    /**
     * Path of setting file for leap soconds (閏秒設定ファイルのPATH)
     * @var string
     */
    static $leap_ini = 'leap.ini';

    /**
     * Calendar type (暦種別)
     * @var string
     */
    public $calendar_type = null;

    /**
     * Julian Day (ユリウス日)
     * @var float
     */
    public $jd = null;

    /**
     * Modified Julian Day (修正ユリウス日)
     * @var float
     */
    public $mjd = null;

    /**
     * Julian Century (ユリウス世紀数)
     * @var float
     */
    public $jc = null;

    /**
     * International Atomi Time (国際原子時)
     * @var Chronos
     */
    public $tai = null;

    /**
     * 地球時 (TT)
     * @var float
     */
    public $tt = null;

    /**
     * 地球力学時 (Terrestrial Dynamical Time)
     * @var float
     */
    public $tdt = null;

    /**
     * delta UT1 = UT1 - UTC <= +/- 0.9(s) 以内になるように閏秒で調整される
     *  - 動的に計算は厳しいため、ゼロとする
     *  - 任意の値を入れる場合は上書きする
     * @var float
     */
    public $delta_ut1 = 0;

    /**
     * 世界時 (UT1)
     * 
     * @var AstroTime
     */
    public $ut1 = null;

    /**
     * (UTC - TAI)
     * @var float
     */
    public $utc_tai = null;

    /**
     * delta(T) = TT - UT1
     * @var float
     */
    public $delta_t = null;

    /**
     * UTC - TAI (閏秒実施結果)
     *  array [ <DateString> => <Second>, ... ]
     * @var array
     */
    public $leaps = [];

    /**
     * TCG (Temps-coordonnée géocentrique) (地心座標時)
     * @var float
     */
    public $tcg = null;

    /**
     * TCB (Temps-coordonnée barycentrique) (太陽系座標時)
     * @var float
     */
    public $tcb = null;

    /**
     * TDB (太陽系力学時の代替)
     * @var float
     */
    public $tdb = null;

    /**
     * 以下 Chronos のプロパティ
     * @var integer | float | string
     */
    public $year;
    public $month;
    public $day;
    public $hour;
    public $minute;
    public $second;
    public $timezone;
    public $micro;
    public $dayOfWeek;
    public $dayOfYear;
    public $daysInMonth;
    public $quarter;


    public function __construct($params=null, $tz=null, $is_calc_astro=true) {
        $this->timezoneName = (is_null($tz)) ? date_default_timezone_get() : $tz;
        if ($params === 'instance') return;

        try {
            $this->local = new Chronos($params, $tz);
            $this->utc = $this->local->copy()->setTimezone('UTC');
            $this->timestamp = $this->local->timestamp;
            $this->setStaticFromLocal();
            if ($is_calc_astro) $this->calcAstro();
        } catch (Exception $e) {
            throw new InvalidArgumentException("Argument is 'DateString': string, 'TimezoneName': string, 'is_calc_astro': boolean");
        }
    }

    /**
     * Create this instance from  year,month,... integer number
     * 
     * @param   integer     $y      Year
     * @param   integer     $m      Month
     * @param   integer     $d      Day
     * @param   integer     $h      Hour
     * @param   integer     $mi     Minute
     * @param   integer     $s      Second
     * @param   string      $tz     Timezonename
     * @param   boolean     $is_calc_astro
     * @throw   InvalidArgumentException
     * @return  AstriTime
     */
    public static function create($y=null, $m=null, $d=null, $h=0, $mi=0, $s=0, $tz=null, $is_calc_astro=true) {
        try {
            $time = new self('instance', $tz);
            $time->local = Chronos::create($y, $m, $d, $h, $mi, $s);
            $time->utc = $time->local->copy()->setTimezone('UTC');
            $time->timestamp = $time->local->timestamp;
            $time->setStaticFromLocal();
            if ($is_calc_astro) $time->calcAstro();
            return $time;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Argument is 'year', 'month',... : Integer");
        }
    }

    /**
     * Create this instance from  UTC Datetime: year,month,... integer number
     * 
     * @param   integer     $y      Year
     * @param   integer     $m      Month
     * @param   integer     $d      Day
     * @param   integer     $h      Hour
     * @param   integer     $mi     Minute
     * @param   integer     $s      Second
     * @param   string      $tz     Timezonename
     * @param   boolean     $is_calc_astro
     * @throw   InvalidArgumentException
     * @return  AstriTime
     */
    public static function createFromUtc($y, $m, $d, $h=0, $mi=0, $s=0, $tz=null, $is_calc_astro=true) {
        if (is_null($tz)) $tz = date_default_timezone_get();
        try {
            $time = new self('instance', $tz);
            $time->utc = Chronos::create($y, $m, $d, $h, $mi, $s, 'UTC');
            $time->local = $time->utc->copy()->setTimezone($tz);
            $time->timestamp = $time->local->timestamp;
            $time->setStaticFromLocal();
            if ($is_calc_astro) $time->calcAstro();
            return $time;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Argument is 'year', 'month',... : Integer");
        }
    }

    /**
     * Create this instance from Unix Timestamp
     * 
     * @param   integer     $timestamp  Unix Timestamp
     * @param   string      $tz         Timezonename
     * @param   boolean     $is_calc_astro
     * @throw   InvalidArgumentException
     * @return  AstroTime
     */
    public static function createFromTimestamp($timestamp, $tz=null, $is_calc_astro=true) {
        try {
            $time = new self('instance', $tz);
            $time->local = Chronos::createFromTimestamp($timestamp);
            $time->utc = $time->local->copy()->setTimezone('UTC');
            $time->timestamp = $time->local->timestamp;
            $time->setStaticFromLocal();
            if ($is_calc_astro) $time->calcAstro();
            return $time;
        } catch (Exception $e) {
            throw new InvalidArgumentException("Argument is 'UnixTime': Integer");
        }
    }

    /**
     * Create this instance from Julian day
     *
     * @param   float   $jd     Julian day
     * @param   string  $tz     Timezonename
     * @param   boolean     $is_calc_astro
     * @throw   InvalidArgumentException
     * @return  AstroTime
     */
    public static function createFromJulian($jd, $tz=null, $is_calc_astro=true) {
        try {
            $time = new self('instance', $tz);
            // Boundary date between Julian and Gregorian
            $utc = ($jd < self::BOUNDARY_JD) ? static::julian2UtcForJulian($jd) : static::julian2UtcForGregorian($jd);
            return static::createFromUtc($utc['year'], $utc['month'], $utc['day'], $utc['hour'], $utc['minute'], $utc['second'], $tz, $is_calc_astro);
        } catch (Exception $e) {
            throw new InvalidArgumentException("Argument is 'Julian: Float");
        }
    }

    private function setStaticFromLocal() {
        $lc = $this->local;
        $this->year     = $lc->year;
        $this->month    = $lc->month;
        $this->day      = $lc->day;
        $this->hour     = $lc->hour;
        $this->minute   = $lc->minute;
        $this->second   = $lc->second;
        $this->timezone = $lc->timezone;
        $this->micro    = $lc->micro;
        $this->dayOfWeek    = $lc->dayOfWeek;
        $this->dayOfYear    = $lc->dayOfYear;
        $this->daysInMonth  = $lc->daysInMonth;
        $this->quarter  = $lc->quarter;
    }

    public function calcAstro() {
        $this->setLeaps()
             ->setCalendarType()
             ->setJulians()
             ->setUt1()
             ->setTai()
             ->setDeltaT()
             ->setTt()
             ->setTcg()
             ->setTcb()
             ->setTdb();
    }

    /**
     * Get formatted 'Local' datetime string
     * 
     * @param   string  $format
     * @return  string
     */
    public function format($format) {
        return $this->local->format($format);
    }

    /**
     * Parsing setting file for leap seconds (うるう秒の設定ファイルを読込)
     *
     * @throws  RuntimeException
     * @return  Array
     */
    public function setLeaps() {
        $leap_ini_dir = dirname(__FILE__).'/settings/';
        $leap_ini_file = $leap_ini_dir.static::$leap_ini;
        if (! is_file($leap_ini_file)) {
            throw new RuntimeException("Leap setting file not found: '".$leap_ini_file."'");
        }
        $this->leaps = parse_ini_file($leap_ini_dir.static::$leap_ini);
        return $this;
    }

    /**
     * Set calendar type
     * 
     * @param   string  $tz     Timezone Name : ex) 'Asia/Tokyo'
     * @return  AstroTime
     */
    public function setCalendarType($tz=null) {
        if (is_null($tz)) {
            $tz = $this->timezoneName;
        } else {
            $this->local = $this->local->copy()->setTimezone($tz);
        }

        $this->calendar_type = static::calendarType($this->utc, $tz);
        return $this;
    }

    /**
     * Set julian day(jd), modified julian day(mjd), julian century(jc)
     * 
     * @return AstroTime
     */
    public function setJulians() {
        $this->jd  = static::utc2Julian($this->utc, $this->timezoneName);
        $this->mjd = static::julian2Mjd($this->jd);
        $this->jc  = static::julianCentury($this->jd);
        return $this;
    }

    /**
     * Set UT1
     * 
     * @return  AstroTime
     */
    public function setUt1() {
        $utc = $this->utc;
        $delta_ut1 = $this->delta_ut1;
        $ut1 = static::utc2Ut1($utc, $delta_ut1);
        $this->ut1 = $ut1->timestamp;
        return $this;
    }

    /**
     * Set TAI
     * 
     * @return AstroTime
     */
    public function setTai() {
        $utc = $this->utc;
        $leaps = $this->leaps;
        $utc_tai = $this->utc2UtcTai($utc, $leaps);
        $this->tai = static::utc2Tai($utc, $utc_tai);
        return $this;
    }

    /**
     * Set delta(T)
     * 
     * @return  AstroTime
     */
    public function setDeltaT() {
        $utc = $this->utc;
        $leaps = $this->leaps;
        $this->delta_t = static::utc2DeltaT($utc, $leaps);
        return $this;
    }

    /**
     * Set TT
     * 
     * @return  AstroTime
     */
    public function setTt() {
        if (is_null($this->ut1)) $this->setUt1();
        if (is_null($this->delta_t)) $this->setDeltaT();

        $ut1 = $this->ut1;
        $delta_t = $this->delta_t;
        $this->tt = static::ut2Tt($ut1, $delta_t);
        return $this;
    }

    /**
     * Set TCG
     * 
     * @return AstroTime
     */
    public function setTcg() {
        if (is_null($this->tt)) $this->setTt();

        $jd = $this->jd;
        $tt = $this->tt;
        $this->tcg = static::julian2Tcg($jd, $tt);
        return $this;
    }

    /**
     * Set TCB
     * 
     * @return AstroTime
     */
    public function setTcb() {
        if (is_null($this->tt)) $this->setTt();

        $jd = $this->jd;
        $tt = $this->tt;
        $this->tcb = static::julian2Tcb($jd, $tt);
        return $this;
    }

    /**
     * Set TDB
     * 
     * @return AstroTime
     */
    public function setTdb() {
        if (is_null($this->tcb)) $this->setTcb();

        $tcb = $this->tcb;
        $jd_tcb = static::time2Julian($tcb);
        $this->tdb = static::tcb2Tdb($tcb, $jd_tcb);
        return $this;
    }


    /**
     * Get calendar type (暦タイプを取得)
     *  - After 1582/10/15  : CALENDAR_TYPE_GREGORIAN (Type gregorian)
     *  - Before            : CALENDAR_TYPE_JULIAN    (Type julian)
     *
     * @param   Chronos     $utc
     * @param   string      $tz     TimezoneName
     * @return  string      Type: 'julian' or 'gregorian'
     */
    public static function calendarType($utc, $tz=null) {
        if (is_null($tz)) $tz = date_default_timezone_get();
        if (is_null($tz) || ! $tz) $tz = 'UTC';

        $bound = Chronos::create(1582, 10, 15, 0, 0, 0, $tz);
        return ($utc->lt($bound)) ? self::CALENDAR_TYPE_JULIAN : self::CALENDAR_TYPE_GREGORIAN;
    }

    /**
     * Convert UTC(Gregorian) to Julian day
     * 
     * @param   Chronos     $utc    UTC
     * @param   string      $tz     TimezoneName
     * @param   string      $calendar_type  CalendarType (Gregorian: gregorian / Julian: julian)
     * @return  float
     */

    public static function utc2Julian($utc, $tz=null, $calendar_type=null) {
        if (is_null($tz)) $tz = date_default_timezone_get();
        if (is_null($calendar_type)) $calendar_type = static::calendarType($utc, $tz);

        if ($calendar_type === static::CALENDAR_TYPE_JULIAN) {
            // Julian
            $jd = self::utc2JulianForJulian($utc);
        } else {
            // Gregorian
            $jd = self::utc2JulianForGregorian($utc);
        }
        return $jd;
    }

    /**
     * Convert Julian day to Modified julian day (JD => MJD)
     * 
     * @param   float   $jd     Julian day
     * @return  float
     */
    public static function julian2Mjd($jd) {
        return $jd - static::JD_MJD;
    }

    /**
     * Convert Julian day to Julian century (JD => JC)
     * 
     * @param   float   $jd     Julian day
     * @return  float
     */
    public static function julianCentury($jd) {
        return ($jd - static::J2000_JD) / (static::DAY_OF_YEAR_JULIAN * 100.0);
    }

    public static function julian2Datestring($jd, $fmt='Y-m-d H:i:s', $tz=null) {
        $time = self::createFromJulian($jd, $tz);
        return $time->local->format($fmt);
    }

    /**
     * Convert Datestring to Julian day
     * 
     * @param   string  $string     Datestring
     * @return  float
     */
    public static function dateString2Julian($string, $tz=null) {
        $time = new self($string, $tz);
        return $time->jd;
    }

    /**
     * Convert UTC(Julian) => Julian day
     * 
     * @param   Chronos     $utc    UTC
     * @return  float
     */
    public static function utc2JulianForGregorian($utc) {
        $y = $utc->year;
        $m = $utc->month;
        $d = $utc->day;
        $k = self::gauss((14 - $m) / 12);

        $h = $utc->hour;
        $mi = $utc->minute;
        $s = $utc->second;

        $jd = self::gauss((-$k + $y + 4800) * 1461 / 4)
            + self::gauss(($k * 12 + $m - 2) * 367 / 12)
            - self::gauss(self::gauss( (-$k + $y + 4900) / 100 ) * 3 / 4 )
            + $d - 32075.5
            + $h / 24
            + $mi / self::MINUTES_OF_DAY
            + $s / self::SECONDS_OF_DAY;

        return $jd;
    }


    /**
     * Convert UTC(Julian) to Julian day
     *  - 1582/10/05 => 1582/10/14 : Nonexistent date
     *      => Treat as a Julian calendar, day += 10
     *
     * @param   Chronos     $utc    UTC(Julian)
     * @return  float
     */
    public static function utc2JulianForJulian($utc) {
        $start = Chronos::create(1582, 10, 05, 0, 0, 0, 'UTC');
        $end   = Chronos::create(1582, 10, 15, 0, 0, 0, 'UTC');

        $utc_j = clone($utc);
        if ($utc_j->gte($start) && $utc_j->lt($end)) {
            $utc_j->addDays(10);
        }

        $y  = $utc_j->year;
        $m  = $utc_j->month;
        $d  = $utc_j->day;
        $h  = $utc_j->hour;
        $mi = $utc_j->minute;
        $s  = $utc_j->second;

        if ($m < 3) {
            $y--;
            $m += 12;
        }

        $jd = static::gauss($y * self::DAY_OF_YEAR_JULIAN)
            + static::gauss(30.59 * ($m - 2))
            + $d - 678914 + self::JD_MJD
            + $h / 24
            + $mi / self::MINUTES_OF_DAY
            + $s / self::SECONDS_OF_DAY;

        return $jd;
    }

    /**
     * Convert Julian day to UTC(Gregorian)
     * 
     * @param   float   $jd     Julian day
     * @return  array   [ 'year', 'month', 'day', 'hour', 'minute', 'second' ]
     */
    public static function julian2UtcForGregorian($jd) {
        $jd += 0.5;
        $_jd = floor($jd);
        $_jds = $jd - $_jd;

        $L = $_jd + 68569;
        $N = self::gauss(4 * $L / 146097);
        $L = $L - self::gauss((146097 * $N + 3) / 4 );
        $I = self::gauss(4000 * ($L + 1) / 1461001);
        $L = $L - self::gauss(1461 * $I / 4) + 31;
        $J = self::gauss(80 * $L / 2447);
        $D = $L - self::gauss(2447 * $J / 80);
        $L = self::gauss($J / 11);
        $M = $J + 2 - 12 * $L;
        $Y = 100 * ($N - 49) + $I + $L;

        $h = floor($_jds * 24);
        $mi = floor(($_jds * 24 - $h) * 60);
        $s = round((($_jds * 24 - $h) * 60 - $mi) * 60);

        $date = [ 'year'=>$Y, 'month'=>$M, 'day'=>$D, 'hour'=>$h, 'minute'=>$mi, 'second'=>$s];
        return $date;
    }

    /**
     * Convert Julian day to UTC(Julian)
     *
     * @param   float   $jd     Julian day
     * @return  array   [ 'year', 'month', 'day', 'hour', 'minute', 'second' ]
     */
    public static function julian2UtcForJulian($jd) {
        $jd -= 0.5;
        $z = floor($jd);
        $tm = $jd - $z;

        $mjd = $jd - static::JD_MJD;
        $n = $mjd + 678883;
        $a = ( 4 * $n ) + 3;
        $b = 5 * static::gauss( ($a % 1461) / 4 ) + 2;

        $Y = static::gauss( $a / 1461 );
        $M = static::gauss( $b / 153 ) + 3;
        $D = static::gauss( ($b % 153) / 5) + 1;

        if ($M > 12) {
            $Y++;
            $M -= 12;
        }
        $h  = floor($tm * 24);
        $mi = floor(($tm * 24 - $h) * 60);
        $s  = round((($tm * 24 - $h) * 60 - $mi) * 60);

        $date = [ 'year'=>$Y, 'month'=>$M, 'day'=>$D, 'hour'=>$h, 'minute'=>$mi, 'second'=>$s];
        return $date;
    }

    /**
     * Calc UT1
     * 
     * @param   Chronos     $utc        UTC
     * @param   float       $delta_ut1  (UT1 - UTC)
     * @return  Chronos
     */
    public static function utc2Ut1($utc, $delta_ut1=0) {
        return $utc->addSecond( $delta_ut1 );
    }

    /**
     * Calc (UTC - TAI) (協定世界時と国際原子時の差)
     * 
     * @param   Chronos     $utc    UTC
     * @param   array
     * @return  integer
     */
    public static function utc2UtcTai($utc, $leaps) {
        asort($leaps);

        $utc_tai = static::DEFAULT_UTC_TAI;
        foreach($leaps as $_dt=>$_val) {
            $_time = strtotime($_dt);
            if ($_time <= $utc->timestamp) {
                $utc_tai = $_val;
                break;
            }
        }
        return $utc_tai;
    }

    /**
     * TAI (International Atmic Time: 国際原子時)
     * 
     * @param   Chronos     $utc        UTC
     * @param   float       $utc_tai    (UTC - TAI)
     * @return  AstroTime
     */
    public static function utc2Tai($utc, $utc_tai) {
        return $utc->subSecond($utc_tai);
    }

    /**
     * delta(T) = TT - UT1
     *  - After 1972-01-01, Before Processing for leap second insertion (+α):
     *      delta(T) = 32.184 - (UTC - TAI)
     *      UTC - TAI : http://jjy.nict.go.jp/QandA/data/leapsec.html
     *  - Other : Rough equation by NASA
     *      [NASA - Polynomial Expressions for Delta T] http://eclipse.gsfc.nasa.gov/SEhelp/deltatpoly2004.html
     *
     * @param   Chronos     $utc    UTC
     * @param   array       $leaps  (UTC - TAI)
     * @return  float
     */
    public static function utc2DeltaT($utc, $leaps) {
        $date = $utc->format('Y-m-d');
        $year = $utc->year;
        $dt = 0;

        if ($year < -500) {
            $t = ($year - 1820) / 100.0;
            $dt = -20;
            $dt += 32 * ($t ** 2);

        } elseif ($year < 500) {
            $t = $year / 100.0;
            $dt  = 10583.6;
            $dt -= 1014.41 * $t;
            $dt += 33.78311         * ($t ** 2);
            $dt -=  5.952053        * ($t ** 3);
            $dt -=  0.1798452       * ($t ** 4);
            $dt +=  0.022174192     * ($t ** 5);
            $dt +=  0.0090316521    * ($t ** 6);

        } elseif ($year < 1600) {
            $t = ($year - 1000) / 100.0;
            $dt  = 1574.2;
            $dt -= 556.01 * $t;
            $dt += 71.23472         * ($t ** 2);
            $dt +=  0.319781        * ($t ** 3);
            $dt -=  0.8503463       * ($t ** 4);
            $dt -=  0.005050998     * ($t ** 5);
            $dt +=  0.0083572073    * ($t ** 6);

        } elseif ($year < 1700) {
            $t = $year - 1600;
            $dt  = 120;
            $dt -= 0.9808 * $t;
            $dt -= 0.01532 * ($t ** 2);
            $dt += ($t ** 3) / 7129.0;

        } elseif ($year < 1800) {
            $t = $year - 1700;
            $dt  = 8.83;
            $dt += 0.1603 * $t;
            $dt -= 0.0059285    * ($t ** 2);
            $dt += 0.00013336   * ($t ** 3);
            $dt -= ($t ** 4) / 1174000.0;

        } elseif ($year < 1860) {
            $t = $year - 1800;
            $dt  = 13.72;
            $dt -= 0.332447 * $t;
            $dt += 0.0068612        * ($t ** 2);
            $dt += 0.0041116        * ($t ** 3);
            $dt -= 0.00037436       * ($t ** 4);
            $dt += 0.0000121272     * ($t ** 5);
            $dt -= 0.0000001699     * ($t ** 6);
            $dt += 0.000000000875   * ($t ** 7);

        } elseif ($year < 1900) {
            $t = $year - 1860;
            $dt  = 7.62;
            $dt += 0.5737           * $t;
            $dt -= 0.251754         * ($t ** 2);
            $dt += 0.01680668       * ($t ** 3);
            $dt -= 0.0004473624     * ($t ** 4);
            $dt += ($t ** 5) / 233174.0;

        } elseif ($year < 1920) {
            $t = $year - 1900;
            $dt  = -2.79;
            $dt += 1.494119     * $t;
            $dt -= 0.0598939    * ($t ** 2);
            $dt += 0.0061966    * ($t ** 3);
            $dt -= 0.000197     * ($t ** 4);

        } elseif ($year < 1941) {
            $t = $year - 1920;
            $dt  = 21.20;
            $dt += 0.84493      * $t;
            $dt -= 0.076100     * ($t ** 2);
            $dt += 0.0020936    * ($t ** 3);

        } elseif ($year < 1961) {
            $t = $year - 1950;
            $dt  = 29.07;
            $dt += 0.407 * $t;
            $dt -= ($t ** 2) / 233.0;
            $dt += ($t ** 3) / 2547.0;

        } elseif ($date < '1972-01-01') {
            $t = $year - 1975;
            $dt  = 45.45;
            $dt += 1.067 * $t;
            $dt -= ($t ** 2) / 260.0;
            $dt -= ($t ** 3) / 718.0;

        } elseif ($date < max(array_keys($leaps))) {
            $utc_tai = self::utc2UtcTai($utc, $leaps);
            $dt = self::TT_TAI - $utc_tai;

        } elseif ($year < 2050) {
            $t = $year - 2000;
            $dt  = 62.92;
            $dt += 0.32217  * $t;
            $dt += 0.005589 * ($t ** 2);

        } elseif ($year <= 2150) {
            $dt  = -20;
            $dt += 32 * ((($year - 1820) / 100.0) ** 2);
            $dt -= 0.5628 * (2150 - $year);

        } else {
            $t = ($year - 1820) / 100;
            $dt  = -20;
            $dt += 32 * ($t ** 2);

        }

        return $dt;
    }

    /**
     * TT (Terrestrial Time: 地球時)
     *  TT = TAI + TT_TAI = UT1 + deltaT
     * 
     * @param   float       $ut1        UT1
     * @param   float       $delta_t    delta(T)
     * @return  float
     */
    public static function ut2Tt($ut1, $delta_t) {
        return $ut1 + $delta_t;
    }

    /**
     * Geocentric Coordinates(地心座標時) : TCG = TT + LG * (JD - T0) * 86400
     *
     * @param   float       $jd     AstroTime
     * @param   float       $tt     TT
     * @return  float
     */
    public static function julian2Tcg($jd, $tt) {
        return $tt + static::LG * ($jd - static::T0) * static::SECONDS_OF_DAY;
    }

    /**
     * Temps-coordonnée barycentrique(太陽系座標時) : TCB = TT + L_B * (JD - T_0) * 86400
     * 
     * @param   float       $jd     Julian day
     * @param   float       $tt     TT
     * @return  float
     */
    public static function julian2Tcb($jd, $tt) {
        return $tt + static::LB * ($jd - static::T0) * static::SECONDS_OF_DAY;
    }

    /**
     * Barycentric Dynamical Time(Substitute) (太陽系力学時の代替) : TDB = TCB - LB * (JD_TCB - T0) * 86400 + TDB0
     * 
     * @param   float   $tcb    TCB
     * @param   float   $jd_tcb Julian day(TCB)
     * @return  float
     */
    public static function tcb2Tdb($tcb, $jd_tcb) {
        return $tcb - static::LB * ($jd_tcb - static::T0) * static::SECONDS_OF_DAY + static::TDB0;
    }

    /**
     * Convert UnixTime to Julian day
     * 
     * @param   integer     $timestamp  Timestamp(UnixTime)
     * @param   string      $tz         TimezoneName
     * @return  float
     */
    public static function time2Julian($timestamp, $tz=null) {
        if (! is_numeric($timestamp)) {
            throw new InvalidArgumentException("Argument is 'UnixTime': Integer");
        }
        $time = Chronos::createFromTimestamp($timestamp, 'UTC');
        return static::utc2Julian($time, $tz);
    }



    /**
     * Gauss Function
     * 
     * @param   float   $value
     * @return  float
     */
    public static function gauss($value) {
        if (! is_numeric($value)) {
            throw new \Exception("'gauss' function need numeric");
        }
        if ($value >= 0) {
            return floor($value);
        } else {
            return ceil($value);
        }
    }

    
    /**
     * Magic method [Setter]
     *  Set Chronos instance to '$this->time', and execute Chronos function.
     *  And call function 'calcAstro()'
     * 
     */
    public function __set($name, $value) {
        if (is_null($this->local)) {
            $this->local = new Chronos;
        }
        $this->local->$name($value);
        $this->utc = $this->local->copy()->setTimezone('UTC');
        $this->calcAstro();
        return $this;
    }

    /**
     * Magic method [Getter]
     *  Set Chronos instance to '$this->local', and get Chronos property. 
     * 
     */
    public function __get($name) {
        if (is_null($this->local)) {
            $this->local = new Chronos;
        }
        return $this->local->$name;
    }

    /**
     * Magic method [Call Function]
     *  Set Chronos intstance to '$this->local', and call Chronos function.
     *  And call function 'calcAstro()'
     */
    public function __call($name, $params) {
        if (is_null($this->local)) {
            $this->local = new Chronos;
        }
        $time = $this->local;
        $arguments = $this->convertArgumentForMagicMethod($params);
        if (count($arguments) === 1) {
            $td = $time->$name($arguments[0]);
        } else {
            $td = call_user_func_array(array($time, $name), $arguments);
        }
        $this->utc = $time->copy()->setTimezone('UTC');
        $this->calcAstro();

        if (! is_object($td)) return $td;
        if (get_class($td) == 'DateInterval') return $td; 

        $td = $this->convertAstroTime2Chronos($td);
        return new self($td);
    }

    /**
     * Magic method [Call Function for Static]
     *  Get Chronos intstance, and call Chronos function.
     *  And call function 'calcAstro()'
     */
    public static function __callStatic($name, $params) {
        $_chronos = new Chronos();
        if (empty($params)) {
            $_temp = $_chronos::$name();
        } else if (count($params) === 1) {
            $_temp = $_chronos::$name($params[0]);
        } else {
            $_temp = call_user_func_array(array($_chronos, $name), $params);
        }
        $time = new self($_temp->format("Y-m-d H:i:s"));
        unset($_temp);

        return $time;
    }

    /**
     * Convert magic method arguments 'AstroTime' to 'Chronos'
     * 
     * @param   array   $arguments;
     * @return  array
     */
    private function convertArgumentForMagicMethod($arguments=null) {
        if (is_null($arguments)) return null;
        if (! is_array($arguments)) {
            return $this->convertAstroTime2Chronos($arguments);
        }

        $args = [];
        foreach($arguments as $arg) {
            $args[] = $this->convertAstroTime2Chronos($arg);
        }
        return $args;
    }

    /**
     * Convert object 'AstroTime' to 'Chronos'
     *  - If $obj is not AstroTime object, return itsself.
     * 
     * @param   AstroTime   $obj
     * @return  Chronos|AstroTime
     */
    public function convertAstroTime2Chronos($obj) {
        if (! is_object($obj)) return $obj;

        $ref = new \ReflectionClass($this);
        if (get_class($obj) != $ref->getName()) return $obj;

        $dateStr = $obj->format('Y-m-d H:i:s');
        return new Chronos($dateStr);
    }

    /**
     * Get Chronos' constant
     * 
     * @param   string  $name   Chronos' constant name
     * @return
     */
    public static function getConstant($name) {
        $const = constant('Cake\Chronos\Chronos::'.$name);
        if ($const) return $const;
        return null;
    }

    /**
     * Alias of getConstant()
     * 
     * @param   string  $name
     * @return
     */
    public static function getConst($name) {
        return self::getConstant($name);
    }

}
