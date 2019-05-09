<?php
require_once dirname(__FILE__).'/vendor/autoload.php';

use r28\AstroTime\AstroTime;
use r28\AstroTime\Coordinates;

$dateString = '2019-05-08 00:00:00';
//$dateString = '1994-11-08 16:00:00';
$time = new AstroTime($dateString, 'Asia/Tokyo', true);
$time->setJulianCentury(true);

echo "Date: ".$dateString.PHP_EOL;
echo "JD  : ".$time->jd.PHP_EOL;
echo "ユリウス世紀数: ".$time->jc;
echo PHP_EOL;
echo "delta_t: ".$time->delta_t.PHP_EOL;

$sun = new Coordinates\SunEcliptic;
$sun_lambda = $sun->getLongitude($time);
//$_sun_phi = floor($sun_phi / 360);
//$sun_phi -= ($_sun_phi * 360);
echo "太陽黄経: {$sun_lambda}".PHP_EOL;

$moon = new Coordinates\MoonEcliptic;
$moon_lambda = $moon->getLongitude($time);
echo "  月黄経: {$moon_lambda}".PHP_EOL;

