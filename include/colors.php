<?php
global $color;
$color{'tcl-01'} = "ff9292"; //red
$color{'tcl-02'} = "8fe08d"; //green
$color{'niddk'} = "5f7ac9"; //blue
$color{'test-01'} = "a871c1"; //purple
$color{'test-02'} = "d2a8e5"; //purple
$color{'test-03'} = "e5c8f3"; //purple
$color{'err-01'} = "bf5a1d"; //brown
$color{'A3'} = "ff9292"; //red
$color{'B1'} = "8fe08d"; //green
$color{'B2'} = "5f7ac9"; //blue
$color{'B3'} = "a871c1"; //purple
$color{'B4'} = "bf5a1d"; //brown
$color{''} = "808080"; //grey
$color{'Bulk'} = "8fe08d"; //green
$color{'Single'} = "5f7ac9"; //blue

/**
 *Return an RBG color code
 *
 **/
function colorop($in,$diff) {
    $out = dechex(hexdec($in) - hexdec($diff));
    return $out;
}

function genColor($value) {
    if (!$value)
        return sprintf('#%02x%02x%02x', 0, 0, 0);

    if (!is_numeric($value)) {
        $COL_MIN_AVG = 64;
        $COL_MAX_AVG = 224;
        $COL_STEP = 32;

        $range = $COL_MAX_AVG - $COL_MIN_AVG;
        $factor = $range / 256;
        $offset = $COL_MIN_AVG;
        $base_hash = substr(md5($value), 0, 6);
        $b_R = hexdec(substr($base_hash,0,2));
        $b_G = hexdec(substr($base_hash,2,2));
        $b_B = hexdec(substr($base_hash,4,2));

        $f_R = floor((floor($b_R * $factor) + $offset) / $COL_STEP) * $COL_STEP;
        $f_G = floor((floor($b_G * $factor) + $offset) / $COL_STEP) * $COL_STEP;
        $f_B = floor((floor($b_B * $factor) + $offset) / $COL_STEP) * $COL_STEP;
        //echo "($f_R|$f_B|$f_G)";
        return sprintf('#%02x%02x%02x', $f_R, $f_G, $f_B);
    }

    /*
    // Original color generation method
    $fx = (pi() * $value / 10000000);
    $gx = ((pi() * ($value) * (1 / 10)));
    $r = round(128 * (1 + sin($fx)));
    $g = round(128 * (1 + cos($fx)));
    $b = round(130 + 32 * (1 + cos($gx)));
    return  'rgb(' . $r . ',' . $g . ',' . $b . ')';
    */

    $stepg = 10;
    $stepr = 200;
    $stepb = 80;

    $min = 40;
    $max = 204;

    $range = $max - $min;

    $oddOffset = ($value % 3) * 25; // super sensitive offset

    $f_B = $oddOffset + $min + round(($range/$stepb) * ($value % $stepb));
    $f_G = 38 + round((180/$stepg) * ($value % $stepg));
    $f_R = $oddOffset + $min + round(($range/$stepr) * ($value % $stepr));

    //echo "($f_R|$f_B|$f_G)";
    return sprintf('#%02x%02x%02x', $f_R, $f_G, $f_B);
}


/**
 *Return the RGB color code associated with a particular freezer
 *
 * @param String $freezer
 *
 * @return String the RBG color code for a freezer or "ffffff" if no match
 **/
function getFreezerColor($destination) {
    $color{'tcl-01'} = "ff9292"; //red
    $color{'tcl-02'} = "8fe08d"; //green
    $color{'niddk'} = "5f7ac9"; //blue
    $color{'test-01'} = "a871c1"; //purple
    $color{'test-02'} = "d2a8e5"; //purple
    $color{'test-03'} = "e5c8f3"; //purple

    if(isset($color[$destination])) {
        return $color[$destination];
    } else {
        $code = dechex(crc32($destination));
        $code = substr($code, 0, 6);
        return $code;
    }
}




// A global array representing the RBG color codes for freezer strings
?>
