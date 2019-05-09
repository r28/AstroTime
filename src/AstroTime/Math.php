<?php
/**
 * General Functions for Mathematical Calculate
 * 
 */

namespace r28\AstroTime\Math {

    /**
     * Gauss Function
     * 
     * @param   float   $value
     * @return  float
     */
    function gauss($value) {
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
     * Normarize angle (angle to 0<=$angle<360)
     * 
     * @param   float   $angle  Angle as degree
     * @return  float
     */
    function normarizeAngle($angle) {
        if ( $angle < 0.0 ) {
          $angle1 = -$angle;
          $angle1 -= 360 * floor($angle1 / 360.0);
          $angle1 = 360 - $angle1;

        } elseif ($angle <= 360.0) {
            $angle1 = $angle;

        } else {
            $angle1 = $angle - 360 * floor($angle / 360);
        }

        return $angle1;
    }

    /**
     * Convert angle unit 'degree' to 'radian'
     * 
     * @param float     $angle  Angle as degree
     * @return float    Angle as radian
     */
    function angleDegree2Radian($angle) {
        return $angle * M_PI / 180.0;
    }

    /**
     * cos() for angle as 'degree'
     *  - before calculate cos(), convert degree to radian
     * 
     * @param   float   $angle
     * @return  float
     */
    function degreeCos($angle) {
        return cos( angleDegree2Radian($angle) );
    }

    /**
     * sin() for angle as 'degree'
     *  - before calculate sin(), convert degree to radian
     * 
     * @param   float   $angle
     * @return  float
     */
    function degreeSin($angle) {
        return sin( angleDegree2Radian($angle) );
    }
}    