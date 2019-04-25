# AstroTime
Datetime library for astronomical calculate with Cake\Chronos

# Requirements
- PHP >= 7.0
- composer
- cakephp/chronos

# Wat's this?
Expand 'Cake\Chronos', calculate the numerical value necessary to astronomy calculation.

- Local Time
- UTC Time
- UT1
- Julian Day (JD)
    - after 1582/10/15 : Gregorian calendar
    - before : Julian calendar
- Modified Julian Day (MJD)
- Julian Century (JC)
- International Atomi Time (TAI)
- Terrestrial Time (TT)
- Terrestrial Dynamical Time (TDT)
- delta(T) (TT-UT1) 
- Temps-coordonnée géocentrique (TCG)
- Temps-coordonnée barycentrique (TCB)

# Install
Use composer:
```bash
$ composer require r28/AstroTime
```

# Usage
- Now time
    ```php
    <?php
    use r28\AstroTime\AstroTime;

    $time = new AstroTime;
    ```

- Set any time
    ```php
    $time = new AstroTime('2019-4-30 00:00:00');
    ```

- Set any local timezone name
    ```php
    $time = new AstroTime('2019-4-30 00:00:00', 'Asia/Tokyo');
    ```

- Don't want to calculate any astro value(JD, TAI, etc.)
    ```php
    $time = new AstroTime('2019-4-30 00:00:00', 'Asia/Tokyo', false);
    ```

- Create by year, month, day, etc.
    ```php
    $time = AstroTime::create(2019, 4, 30, 0, 0, 0);
    // or
    $time = AstroTime::create(2019, 4, 30);     // => 2019/04/30 00:00:00
    // or
    $time = AstroTime::create(2019, 4, 30, 0, 0, 0, 'Asia/Tokyo', false);
    ```

- Create by UTC
    ```php
    $time = AstroTime::createFromUtc(2019, 4, 29, 15, 0, 0, 'Asia/Tokyo');
    // UTC: 2019/04/29 15:00:00
    // Local(Asia/Tokyo) : 2019/04/30 00:00:00
    ```
- Create by Unix Timestamp
    ```php
    $time = AstroTime::createFromTimestamp(1556550000, 'Asia/Tokyo');
    // Local : 2019/04/30 00:00:00
    ```
- Create by Julian Day
    ```php
    $time = AstroTime::createFromJulian(2458603.125, 'Asia/Tokyo');
    // Local : 2019/04/30 00:00:00
    ```

# Examples
    # DateTime format
    echo $time->format('Y-m-d H:i:s');          # 2019-04-30 00:00:00

    # Local Time
    echo $time->local->format('Y-m-d H:i:s');   # 2019-04-30 00:00:00

    # UTC
    echo $time->utc->format('Y-m-d H:i:s');     # 2019-04-29 15:00:00

    echo $time->toDateTimeString();             # 2019-04-30 00:00:00

    # JD
    echo $time->jd;     # 2458603.125

    # MJD
    echo $time->mjd;    # 58602.625

    # JC
    echo $time->jc;     # 0.19324093086927

    # JD => Time
    echo AstroTime::createFromJulian($time->jd)->toDateTimeString();    # 2019-04-30 00:00:00

    # JD => Datetime String
    AstroTime::julian2Datestring($time->jd, 'Y-m-d H:i:s', 'Asia/Tokyo');   # 2019-04-30 00:00:00

    # year, month, ...
    echo $time->year;   # 2019
    echo $time->month;  # 4
    echo $time->day;    # 30
    echo $time->hour;   # 0
    echo $time->minute; # 0
    echo $time->second; # 0
    echo $time->timezoneName;   # Asia/Tokyo
    echo $time->micro;          # 0
    echo $time->dayOfWeek;      # 2
    echo $time->dayOfYear;      # 119
    echo $time->daysInMonth;    # 30
    echo $time->quarter;        # 2

    echo $time->ut1;    # 1556550000
    echo $time->tt;     # 1556550068.184
    echo $time->tai;    # 2019-04-29 15:00:36
    echo $time->delta_t:    # 68.184
    echo $time->tcg;    # 1556550069.1148
    echo $time->tcb;    # 1556550088.8931
    echo $time->tdb;    # 1556550068.1839
