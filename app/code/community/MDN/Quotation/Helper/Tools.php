<?php

class MDN_quotation_Helper_Tools extends Mage_Core_Helper_Abstract {

    /**
     * Days between 2 dates
     *
     * @param unknown_type $debut
     * @param unknown_type $fin
     * @return unknown
     */
    public function daysBetweenDates($debut, $fin) {
        $tDeb = explode("-", $debut);
        $tFin = explode("-", $fin);

        $diff = mktime(0, 0, 0, $tFin[1], $tFin[2], $tFin[0]) -
                mktime(0, 0, 0, $tDeb[1], $tDeb[2], $tDeb[0]);

        return(($diff / 86400) + 1);
    }

}