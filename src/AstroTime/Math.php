<?php

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

    function normarizeAngle($angle) {
        if ( $angle < 0.0 ) {
          $angle1 = -$angle;
          $angle2 = (int) ($angle1 / 360.0);
          $angle1 -= 360.0 * $angle2;
          $angle1 = 360.0 - $angle1;
        } else {
          $angle1 = (int) ($angle / 360.0);
          $angle1 = $angle - 360.0 * $angle1;
        }
        return $angle1;
    }

    /**
     * Convert angle 'degree' to 'radian'
     * 
     * @param float     $angle  Angle as Degree
     * @return float    Angle as Radian
     */
    function angleDegree2Radian($angle) {
        return $angle * M_PI / 180.0;
    }

}    