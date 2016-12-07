<?php

require_once('config.php');
/*
  Get element from the given url, elementName and its id
  Note: The method only returns a single object
*/
function getElementByID($elementName,$id,$url=RATES) {
  $xml = simplexml_load_file($url);
  $result = $xml -> xpath("//{$elementName}[@id='{$id}']");
  if($result) {
    return $result[0];
  } else {
    return null;
  }
}

/*
  Get the time from currencies the rates.xml
*/
function getTime() {
  $xml = new DOMDocument();
  $xml -> load(RATES);
  return $xml -> getElementsByTagName("currencies")-> item(0)->getAttribute("at");
}

/*
  Get string in xml format from given error id and requested format
*/
function getErrorConvXML($id) {
  $error = getElementByID("error",$id,ERRORS);
  $response = '<?xml version="1.0" encoding="UTF-8"?>';
  $response .= '<conv>';
  $response .= '<error>';
  $response .= '<code>'. $error['id']. '</code>';
  $response .= '<message>'. $error -> message . '</message>';
  $response .= '</error>';
  $response .= '</conv>';
  return $response;
}

/*
  Get string in json format from given error id and requested format
*/
function getErrorConvJson($id) {
  $error = getElementByID("error",$id,ERRORS);
  $response = '<?xml version="1.0" encoding="UTF-8"?>';
  $response .= '<forex>';// Adds this root tag so that json_encode produces the required format
  $response .= '<conv>';
  $response .= '<error>';
  $response .= '<code>'. $error['id']. '</code>';
  $response .= '<message>'. $error ->message . '</message>';
  $response .= '</error>';
  $response .= '</conv>';
  $response .= '</forex>';
  $simplexml = simplexml_load_string($response);
  return json_encode($simplexml);
}

/*
  Get the xml string output from the given parameters
*/
function getConvXML($base_from, $base_to, $at, $from_amnt) {
  $baseAmtFrom = (float)$base_from -> rate;
  $baseAmtTo = (float)$base_to -> rate;
  $baseRate = $baseAmtTo / $baseAmtFrom;
  $to_amnt = $baseRate * $from_amnt;
  $response = '<?xml version="1.0" encoding="UTF-8"?>';
  $response .= '<conv>';
  $response .= '<at>'.$at.'</at>';
  $response .= '<rate>' .number_format($baseRate,4) .'</rate>';
  $response .= '<from>';
  $response .= '<code>'. $base_from['id'].'</code>';
  $response .= '<curr>' . $base_from->name . '</curr>';
  $response .= '<loc>' . $base_from->loc .'</loc>';
  $response .= '<amnt>' .number_format($from_amnt ,4) .'</amnt>';
  $response .= '</from>';
  $response .= '<to>';
  $response .= '<code>'. $base_to['id'].'</code>';
  $response .= '<curr>' . $base_to->name . '</curr>';
  $response .= '<loc>' . $base_to->loc .'</loc>';
  $response .= '<amnt>' .number_format($to_amnt ,4).'</amnt>';
  $response .= '</to>';
  $response .= '</conv>';
  return $response;
}

/*
  Get the json string output from the given parameters
*/
function getConvJson($base_from, $base_to, $at, $from_amnt) {
  $baseAmtFrom = (float)$base_from -> rate;
  $baseAmtTo = (float)$base_to -> rate;
  $baseRate = $baseAmtTo / $baseAmtFrom;
  $to_amnt = $baseRate * $from_amnt;
  $response = '<?xml version="1.0" encoding="UTF-8"?>';
  $response .= '<forex>'; // Adds this root tag so that json_encode produces the required format
  $response .= '<conv>';
  $response .= '<at>'.$at.'</at>';
  $response .= '<rate>' .number_format($baseRate,4) .'</rate>';
  $response .= '<from>';
  $response .= '<code>'. $base_from['id'].'</code>';
  $response .= '<curr>' . $base_from->name . '</curr>';
  $response .= '<loc>' . $base_from->loc .'</loc>';
  $response .= '<amnt>' .number_format($from_amnt ,4) .'</amnt>';
  $response .= '</from>';
  $response .= '<to>';
  $response .= '<code>'. $base_to['id'].'</code>';
  $response .= '<curr>' . $base_to->name . '</curr>';
  $response .= '<loc>' . $base_to->loc .'</loc>';
  $response .= '<amnt>' .number_format($to_amnt ,4).'</amnt>';
  $response .= '</to>';
  $response .= '</conv>';
  $response .= '</forex>';
  $simplexml = simplexml_load_string($response);
  return json_encode($simplexml);
}

/*
  update the rates.xml
  returns true if update is successful. false if failed
*/
function updateRates() {
  $currencies = '';
  $xml = new DOMDocument();
  $xml -> load(RATES);
  $currency = $xml -> getElementsByTagName("currencies") -> item(0);
  $elements = $xml -> getElementsByTagName("currency");
  $length = $elements -> length;
  for($i = 0; $i < $length; $i++) { // loop through rates.xml to gather currencies and change to pair that yql understands
    if($i == $length - 1) { // the last element
      $id = $elements -> item($i) -> getAttribute('id');
      $currencies .= '"GBP'.$id.'"';
    } else {
      $id = $elements -> item($i) -> getAttribute('id');
      $currencies .= '"GBP'.$id.'", ';
    }
  }
  $query = 'select * from yahoo.finance.xchange where pair in ('.$currencies.')';
  $yql = YQLBASE.urlencode($query).YQLTRAIL;
  $updateXML = file_get_contents($yql);
  if($updateXML == null) { // nulls means no data returns from yahoo or something wrong with loaing rates.xml
    return false;
  }
  $uppdate_simpleXML = simplexml_load_string($updateXML);
  $updateResults = $uppdate_simpleXML -> results -> asXML();
  $xmlDomResult = new DOMDocument("1.0");
  $xmlDomResult -> loadXML($updateResults);
  $rates = $xmlDomResult -> getElementsByTagName("rate");
  foreach($rates as $r) { // this loop blog updates the rates
    $id = substr($r -> getAttribute('id'),3);
    $rateValue = $r -> getElementsByTagName("Rate") -> item(0) -> nodeValue;
    $currency = getDOMElementByID($xml, 'currency', $id);
    $rate = $currency -> getElementsByTagName('rate') -> item(0);
    $rate -> nodeValue = $rateValue;
    $currency -> replaceChild($rate,$rate);
  }
  $xml ->getElementsByTagName('currencies') -> item(0) -> setAttribute('at', date('d M Y H:i')); // update the date in currencies tag
  $xml -> save(RATES); // finally save to take effect
  return true;
}

/*
  Get  DOMelement from the given DOMDocument, elementName, its id and uri
  Note: The method only returns a single object
*/
function getDOMElementByID($domdoc,$elementName,$id) {
  $xpath = new DOMXPath($domdoc);
  $query = "//{$elementName}[@id='{$id}']";
  return $xpath -> query($query) -> item(0);
}
/*
  Creat the rates.xml yql
*/
function create($uri) {
    $xmlSimple = simplexml_load_file($uri);
    $locSimple = simplexml_load_file(COUNTRIES);
    $results = $xmlSimple -> results -> asXML();
    $xmlDomResult = new DOMDocument("1.0");
    $xmlDomResult -> loadXML($results);
    $rates = $xmlDomResult -> getElementsByTagName("rate");
    $xmlDomFileOut = new DOMDocument("1.0","UTF-8");
    $currencies = $xmlDomFileOut -> createElement("currencies");
    $at = $xmlDomFileOut -> createAttribute('at');
    $at -> value = date('d M Y H:i');
    $currencies -> appendChild($at);
    foreach($rates as $r) {

      //acquiring data for creating elements;
      $codeValue = substr($r -> getAttribute('id'),3);
      $rateValue = $r -> getElementsByTagName("Rate") -> item(0) -> nodeValue;
      $locResults = $locSimple -> xpath("//CcyNtry[Ccy='{$codeValue}']");
      $currencyName = $locResults[0] -> CcyNm;
      $locations = '';
      for($i = 0; $i < count($locResults); $i++) {
        if($i == (count($locResults)-1)) {
          $locations .= ucwords(strtolower($locResults[$i] -> CtryNm));
        } else {
          $locations .= ucwords(strtolower($locResults[$i] -> CtryNm)).", ";
        }
      }

      //primary child node
      $currency = $xmlDomFileOut -> createElement("currency");

      //secondary child nodes and attributes
      $currencyID = $xmlDomFileOut -> createAttribute('id');
      $currencyID -> value = $codeValue;
      $rateNode = $xmlDomFileOut -> createElement("rate",$rateValue);
      $codeNameNode = $xmlDomFileOut -> createElement("name", $currencyName);
      $locNode = $xmlDomFileOut -> createElement("loc", $locations);

      // appending child and attributes under specific tags
      $currency -> appendChild($currencyID);
      $currency -> appendChild($codeNameNode);
      $currency -> appendChild($locNode);
      $currency -> appendChild($rateNode);
      $currencies -> appendChild($currency);

    }
    $xmlDomFileOut ->appendChild($currencies);
    $xmlDomFileOut ->save(RATES);

  }

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

  /*
    Get Error String in xml format
  */
  function getError($id,$method) {
    $xml = simplexml_load_file("errors.xml");
    $result = $xml -> xpath("//error[@id='{$id}']");
    $error = $result[0];
    $response = '<?xml version="1.0" encoding="UTF-8" ?>';
    $response .= '<method type="'.$method.'">';
    $response .= '<error>';
    $response .= '<code>'. $error['id'].'</code>';
    $response .= '<msg>'.$error -> message .'</msg>';
    $response .= '</error>';
    $response .= '</method>';
    return $response;
  }

  /*
    Check if the code is included in ISO list
    Return true if exists, else false
  */
  function isInISO($code) {
    $xml  = simplexml_load_file('http://www.currency-iso.org/dam/downloads/lists/list_one.xml');
    $result = $xml -> xpath("//CcyNtry[Ccy='{$code}']");
    if($result != null) {
      return true;
    } else {
      return false;
    }
  }

?>
