<?php

  $carray = array('CAD','CHF','CNY','DKK','EUR','GBP','HKD','HUF','INR','JPY','MXN','MYR',  'NOK',  'NZD',  'PHP',  'RUB',  'SEK',  'SGD',  'THB',  'TRY',  'USD',  'ZAR');
  /*
  Generate the YQL uri base on the $carray
  */
  function getYQLURI() {
    global $carray;
    $yqlbase = 'https://query.yahooapis.com/v1/public/yql?q=';
    $currencies = '';
    $yqlbase_trail = "&diagnostics=true&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys";
    $length = count($carray);
    for($i=0;$i<$length;$i++) {
      if($i == $length - 1) { // the last element
        $id = $carray[$i];
        $currencies .= '"GBP'.$id.'"';
      } else {
        $id = $carray[$i];
        $currencies .= '"GBP'.$id.'", ';
      }
    }
    $query = 'select * from yahoo.finance.xchange where pair in ('.$currencies.')';
    $yqlbase .= urlencode($query).$yqlbase_trail;
    return $yqlbase;
  }
?>
