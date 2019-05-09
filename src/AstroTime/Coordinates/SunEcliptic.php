<?php
/**
 * 太陽位置概算
 * 
 */
namespace r28\AstroTime\Coordinates;

require_once __DIR__.'/../Math.php';
use r28\AstroTime\AstroTime;
use r28\AstroTime\Math;

class SunEcliptic
{
    /**
     * Coefficients
     * @constant
     *  A           : Amplitude of Vibration
     *  k           : Angular Velocity
     *  theta       : Initial Phase
     *  is_multi_a  : Multiply 'A' and t
     */
    const COE = [
        # 摂動項
        'perturbation' => [
            1  => [ 'A' =>      0.0004, 'k' =>  31557.0, 'theta' =>  161.0 ],
            2  => [ 'A' =>      0.0004, 'k' =>  29930.0, 'theta' =>   48.0 ],
            3  => [ 'A' =>      0.0005, 'k' =>   2281.0, 'theta' =>  221.0 ],
            4  => [ 'A' =>      0.0005, 'k' =>    155.0, 'theta' =>  118.0 ],
            5  => [ 'A' =>      0.0006, 'k' =>  33718.0, 'theta' =>  316.0 ],
            6  => [ 'A' =>      0.0007, 'k' =>   9038.0, 'theta' =>   64.0 ],
            7  => [ 'A' =>      0.0007, 'k' =>   3035.0, 'theta' =>  110.0 ],
            8  => [ 'A' =>      0.0007, 'k' =>  65929.0, 'theta' =>   45.0 ],
            9  => [ 'A' =>      0.0013, 'k' =>  22519.0, 'theta' =>  352.0 ],
            10 => [ 'A' =>      0.0015, 'k' =>  45038.0, 'theta' =>  254.0 ],
            11 => [ 'A' =>      0.0018, 'k' => 445267.0, 'theta' =>  208.0 ],
            12 => [ 'A' =>      0.0018, 'k' =>     19.0, 'theta' =>  159.0 ],
            13 => [ 'A' =>      0.0020, 'k' =>  32964.0, 'theta' =>  158.0 ],
            14 => [ 'A' =>      0.0200, 'k' =>  71998.1, 'theta' =>  265.1 ],
            15 => [ 'A' =>     -0.0048, 'k' => 35999.05, 'theta' => 267.52, 'is_multi_a' => true ],
            16 => [ 'A' =>      1.9147, 'k' => 35999.05, 'theta' => 267.52 ],
        ],
        # 比例項
        'proportional' => [
             1 => [ 'A' =>  36000.7695 ],
             2 => [ 'A' =>    280.4659 ],
        ],
    ];

    /**
     * Sun Ecliptioc Longitude
     * @var float
     */
    public $lambda;

    /**
     * Get Sun Ecliptic Longitude (Lambda)
     * 
     * @param   AstroTime   $time
     * @return  float       degree
     */
    public function getLongitude(AstroTime $time) {
        $t = $time->jc;
        $lam = $this->calcLongitudeParams($t);
        $this->lambda = $lam;
        return $lam;
    }

    /**
     * Calculate Ecliptic Longitude
     * 
     * @param   float   $t  Julian Century
     * @return  float   degree
     */
    private function calcLongitudeParams($t) {
        $lam = 0;
        # Perturbation: 1-15
        for($i=1; $i<=15; $i++) {
            $res = static::calcPerturbationTerm($i, $t);
            $lam += $res['lambda'];
        }
        # Perturbation: 16
        $lam += static::COE['perturbation'][16]['A'] * cos( Math\angleDegree2Radian($res['angle']) );

        # proportional: 1
        $ang = Math\normarizeAngle(static::COE['proportional'][1]['A'] * $t);
        # proportional: 2
        $ang = Math\normarizeAngle(static::COE['proportional'][2]['A'] + $ang);

        $lam = Math\normarizeAngle($lam + $ang);

        return $lam;
    }

    /**
     * Perturbation Term
     * 
     * @param   integer     $i      Target term number
     * @param   float       $t      Julian Century
     * @return  array
     */
    private static function calcPerturbationTerm($i, $t) {
        $term = static::COE['perturbation'][$i];
        $ang = Math\normarizeAngle( ($term['k'] * $t) + $term['theta']);
        $a = (array_key_exists('is_multi_a', $term) && $term['is_multi_a'] === true) ? $term['A'] * $t : $term['A'];
        $lam = $a * Math\degreeCos($ang);

        return [ 'angle' => $ang, 'lambda' => $lam ];
    }
}