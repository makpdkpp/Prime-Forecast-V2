<?php

if (!function_exists('thaiDate')) {
    /**
     * Convert date to Thai format (DD/MM/YYYY in Buddhist Era)
     * 
     * @param string|null $date
     * @param string $format
     * @return string
     */
    function thaiDate($date, $format = 'd/m/Y')
    {
        if (!$date || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
            return '-';
        }
        
        try {
            $carbon = \Carbon\Carbon::parse($date);
            $buddhistYear = $carbon->year + 543;
            
            // Replace year in format with Buddhist year
            if ($format == 'd/m/Y') {
                return $carbon->format('d/m/') . $buddhistYear;
            } elseif ($format == 'd/m/y') {
                return $carbon->format('d/m/') . substr($buddhistYear, -2);
            } else {
                // For custom formats, replace Y with Buddhist year
                return str_replace($carbon->format('Y'), $buddhistYear, $carbon->format($format));
            }
        } catch (\Exception $e) {
            return '-';
        }
    }
}

if (!function_exists('thaiDateShort')) {
    /**
     * Convert date to short Thai format (DD/MM/YY in Buddhist Era)
     * 
     * @param string|null $date
     * @return string
     */
    function thaiDateShort($date)
    {
        return thaiDate($date, 'd/m/y');
    }
}
