<?php

//-------------------Main Block---------------//
date_default_timezone_set("GMT");
$format = filter_input(INPUT_GET, 'format');
/*
  To make sure there are only two formats even if invalid inputs is passed.
*/
switch($format) {
  case 'xml':
  header('Content-Type: text/xml');
  break;

  case 'json':
  header('Content-Type: text/json');
  break;

  default:
  header('Content-Type: text/xml');
  $format = "xml";
}

require_once("function.php");

if(count($_GET)==0) {
  echo $format == "json" ? getErrorConvJson("1100") : getErrorConvXML("1100"); // no parameter is set
  die();
}
if(!isset($_GET['format'])) {
  echo $format == "json" ? getErrorConvJson("1100") : getErrorConvXML("1100"); // format not set
  die();
}
if(!($_GET['format']=='xml' || $_GET['format']=='json'  )) {
  echo $format == "json" ? getErrorConvJson("1200") : getErrorConvXML("1200"); // format not recognize
  die();
}
if(count($_GET) < 4 ) { // first check if the parameter counts less then 4
  echo $format == "json" ? getErrorConvJson("1100") : getErrorConvXML("1100"); // require parameter is missing
  die();
} else if (count($_GET) > 4 ) {
  echo $format == "json" ? getErrorConvJson("1200") : getErrorConvXML("1200"); // too many parameters
  die();
}

if(!file_exists("rates.xml")) {// check if "rates.xml" is missing
  if(file_exists("config.php")) {
    require_once("config.php");
    create(getYQLURI()); // create the file from the uri generated
  } else {
    echo $format == "json" ? getErrorConvJson("1400") : getErrorConvXML("1400"); // config.php is missing too. can't help anymore
    die();
  }
}

if(checkQueryVariable()) { // validate query variableNames are valid first
  $from= filter_input(INPUT_GET, 'from');
  $to = filter_input(INPUT_GET, 'to');
  $from_amnt = filter_input(INPUT_GET, 'amnt',FILTER_VALIDATE_FLOAT);
  $hours = (strtotime("now")-strtotime(getTime())) / 3600;
  if($hours >= 12) {
    updateRates();
  }
} else {
  echo $format == "json" ? getErrorConvJson("1200") : getErrorConvXML("1200"); // parameter not recognize
  die();
}
if($from && $to && $from_amnt) {

  $basefrom = getElementByID("currency",$from);
  $baseto = getElementByID("currency", $to);
  $at = getTime();
  if($basefrom && $baseto && $at) {
    echo $format == "json" ? getConvJson($basefrom, $baseto, $at, $from_amnt) : getConvXML($basefrom, $baseto, $at, $from_amnt);
    die();
  } else {
    echo $format == "json" ? getErrorConvJson("1000") : getErrorConvXML("1000"); // currency type not recognized error
    die();
  }
} else if($from_amnt == false) {
    echo $format == "json" ? getErrorConvJson("1300") : getErrorConvXML("1300"); // currency type must be decimal error
    die();
}

//----------------Main Block Ends----------------//


//------------------Function Block------------------//

/*
  Check if from, to and amnt are spelled correct and
*/
function checkQueryVariable() {
  $array = ['from','to','amnt','format'];
  $ismatch = true;
  foreach ($array as $key) {
    if(!array_key_exists($key,$_GET)) {
      $ismatch = false;
      break;
    }
  }
  return $ismatch;
}

//--------------------Function Block Ends------------------//
?>
