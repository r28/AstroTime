<?php
/**
 * 月位置概算
 * 
 */
namespace r28\AstroTime\Coordinates;

require_once __DIR__.'/../Math.php';
use r28\AstroTime\AstroTime;
use r28\AstroTime\Math;

class MoonEcliptic
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
             1 => [ 'A' =>     0.0003, 'k' =>  2322131.0, 'theta' =>      191.0 ],
             2 => [ 'A' =>     0.0003, 'k' =>     4067.0, 'theta' =>       70.0 ],
             3 => [ 'A' =>     0.0003, 'k' =>   549197.0, 'theta' =>      220.0 ],
             4 => [ 'A' =>     0.0003, 'k' =>  1808933.0, 'theta' =>       58.0 ],
             5 => [ 'A' =>     0.0003, 'k' =>   349472.0, 'theta' =>      337.0 ],
             6 => [ 'A' =>     0.0003, 'k' =>   381404.0, 'theta' =>      354.0 ],
             7 => [ 'A' =>     0.0003, 'k' =>   958465.0, 'theta' =>      340.0 ],
             8 => [ 'A' =>     0.0004, 'k' =>    12006.0, 'theta' =>      187.0 ],
             9 => [ 'A' =>     0.0004, 'k' =>    39871.0, 'theta' =>      223.0 ],
            10 => [ 'A' =>     0.0005, 'k' =>   509131.0, 'theta' =>      242.0 ],
            11 => [ 'A' =>     0.0005, 'k' =>  1745069.0, 'theta' =>       24.0 ],
            12 => [ 'A' =>     0.0005, 'k' =>  1908795.0, 'theta' =>       90.0 ],
            13 => [ 'A' =>     0.0006, 'k' =>  2258267.0, 'theta' =>      156.0 ],
            14 => [ 'A' =>     0.0006, 'k' =>   111869.0, 'theta' =>       38.0 ],
            15 => [ 'A' =>     0.0007, 'k' =>    27864.0, 'theta' =>      127.0 ],
            16 => [ 'A' =>     0.0007, 'k' =>   485333.0, 'theta' =>      186.0 ],
            17 => [ 'A' =>     0.0007, 'k' =>   405201.0, 'theta' =>       50.0 ],
            18 => [ 'A' =>     0.0007, 'k' =>   790672.0, 'theta' =>      114.0 ],
            19 => [ 'A' =>     0.0008, 'k' =>  1403732.0, 'theta' =>       98.0 ],
            20 => [ 'A' =>     0.0009, 'k' =>   858602.0, 'theta' =>      129.0 ],
            21 => [ 'A' =>     0.0011, 'k' =>  1920802.0, 'theta' =>      186.0 ],
            22 => [ 'A' =>     0.0012, 'k' =>  1267871.0, 'theta' =>      249.0 ],
            23 => [ 'A' =>     0.0016, 'k' =>  1856938.0, 'theta' =>      152.0 ],
            24 => [ 'A' =>     0.0018, 'k' =>   401329.0, 'theta' =>      274.0 ],
            25 => [ 'A' =>     0.0021, 'k' =>   341337.0, 'theta' =>       16.0 ],
            26 => [ 'A' =>     0.0021, 'k' =>    71998.0, 'theta' =>       85.0 ],
            27 => [ 'A' =>     0.0021, 'k' =>   990397.0, 'theta' =>      357.0 ],
            28 => [ 'A' =>     0.0022, 'k' =>   818536.0, 'theta' =>      151.0 ],
            29 => [ 'A' =>     0.0023, 'k' =>   922466.0, 'theta' =>      163.0 ],
            30 => [ 'A' =>     0.0024, 'k' =>    99863.0, 'theta' =>      122.0 ],
            31 => [ 'A' =>     0.0026, 'k' =>  1379739.0, 'theta' =>       17.0 ],
            32 => [ 'A' =>     0.0027, 'k' =>   918399.0, 'theta' =>      182.0 ],
            33 => [ 'A' =>     0.0028, 'k' =>     1934.0, 'theta' =>      145.0 ],
            34 => [ 'A' =>     0.0037, 'k' =>   541062.0, 'theta' =>      259.0 ],
            35 => [ 'A' =>     0.0038, 'k' =>  1781068.0, 'theta' =>       21.0 ],
            36 => [ 'A' =>     0.0040, 'k' =>      133.0, 'theta' =>       29.0 ],
            37 => [ 'A' =>     0.0040, 'k' =>  1844932.0, 'theta' =>       56.0 ],
            38 => [ 'A' =>     0.0040, 'k' =>  1331734.0, 'theta' =>      283.0 ],
            39 => [ 'A' =>     0.0050, 'k' =>   481266.0, 'theta' =>      205.0 ],
            40 => [ 'A' =>     0.0052, 'k' =>    31932.0, 'theta' =>      107.0 ],
            41 => [ 'A' =>     0.0068, 'k' =>   926533.0, 'theta' =>      323.0 ],
            42 => [ 'A' =>     0.0079, 'k' =>   449334.0, 'theta' =>      188.0 ],
            43 => [ 'A' =>     0.0085, 'k' =>   826671.0, 'theta' =>      111.0 ],
            44 => [ 'A' =>     0.0100, 'k' =>  1431597.0, 'theta' =>      315.0 ],
            45 => [ 'A' =>     0.0107, 'k' =>  1303870.0, 'theta' =>      246.0 ],
            46 => [ 'A' =>     0.0110, 'k' =>   489205.0, 'theta' =>      142.0 ],
            47 => [ 'A' =>     0.0125, 'k' =>  1443603.0, 'theta' =>       52.0 ],
            48 => [ 'A' =>     0.0154, 'k' =>    75870.0, 'theta' =>       41.0 ],
            49 => [ 'A' =>     0.0304, 'k' =>   513197.9, 'theta' =>      222.5 ],
            50 => [ 'A' =>     0.0347, 'k' =>   445267.1, 'theta' =>       27.9 ],
            51 => [ 'A' =>     0.0409, 'k' =>   441199.8, 'theta' =>       47.4 ],
            52 => [ 'A' =>     0.0458, 'k' =>   854535.2, 'theta' =>      148.2 ],
            53 => [ 'A' =>     0.0533, 'k' =>  1367733.1, 'theta' =>      280.7 ],
            54 => [ 'A' =>     0.0571, 'k' =>   377336.3, 'theta' =>       13.2 ],
            55 => [ 'A' =>     0.0588, 'k' =>    63863.5, 'theta' =>      124.2 ],
            56 => [ 'A' =>     0.1144, 'k' =>   966404.0, 'theta' =>      276.5 ],
            57 => [ 'A' =>     0.1851, 'k' =>   35999.05, 'theta' =>      87.53 ],
            58 => [ 'A' =>     0.2136, 'k' =>  954397.74, 'theta' =>     179.93 ],
            59 => [ 'A' =>     0.6583, 'k' =>  890534.22, 'theta' =>      145.7 ],
            60 => [ 'A' =>     1.2740, 'k' =>  413335.35, 'theta' =>      10.74 ],
            61 => [ 'A' =>     6.2888, 'k' => 477198.868, 'theta' =>     44.963 ],
        ],
        # 比例項
        'proportional' => [
             1 => [ 'A' =>  481267.8809 ],
             2 => [ 'A' =>     218.3162 ],
        ],
    ];

    /**
     * Moon Ecliptioc Longitude
     * @var float
     */
    public $lambda;

    /**
     * Get Moon Ecliptic Longitude (Lambda)
     * 
     * @param   AstroTime   $time
     * @return  float       degree
     */
    public function getLongitude(AstroTime $time) {
        // Time is Julian Century of TT
        $time = $time->setJulianCentury(true);
        $t = $time->jc;
        $lam = $this->calcLongitudeParams($t);
        $this->lambda = $lam;
        return $lam;
    }

    private function calcLongitudeParams($t) {
        $lam = 0;
        # Perturbation
        for($i=1; $i<=61; $i++) {
            $res = static::calcPerturbationTerm($i, $t);
            $lam += $res['lambda'];
        }

        # proportional: 1
        $ang = Math\normarizeAngle(static::COE['proportional'][1]['A'] * $t);
        # proportional: 2
        $ang = Math\normarizeAngle(static::COE['proportional'][2]['A'] + $ang);

        $lam = Math\normarizeAngle($lam + $ang);

        return $lam;
    }

    private static function calcPerturbationTerm($i, $t) {
        $term = static::COE['perturbation'][$i];
        $ang = Math\normarizeAngle( ($term['k'] * $t) + $term['theta']);
        $lam = $term['A'] * Math\degreeCos($ang);

        return [ 'angle' => $ang, 'lambda' => $lam ];
    }
}