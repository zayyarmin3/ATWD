<?php
  /////////////Main block starts
  $code = filter_input(INPUT_POST,'code');
  $name = filter_input(INPUT_POST,'cname');
  $rate = filter_input(INPUT_POST, 'rate', FILTER_VALIDATE_FLOAT);
  $loc = filter_input(INPUT_POST,'countries');
  if($code && $name && $rate && $loc) {
    if(file_exists("rates.xml")) {
      $code = strtoupper($code);
      $name = ucwords(strtolower($name));
      $loc = ucwords(strtolower($loc));


      $dom = new DOMDocument();
      $dom -> load("rates.xml");
      $xpath = new DOMXPath($dom);
      $query = "//currency[@id='{$code}']";
      $ocurrency = $xpath -> query($query) -> item(0);

      if($ocurrency != null) {
        echo getError("2700"); // currency already exists
        die();
      } else if($ocurrency == null && isInISO($code) == false) {
        echo getError("2800"); // currency not recognized by ISO
        die();
      }

      $currency = $dom -> createElement("currency");

      //children of currency
      $currencyID = $dom -> createAttribute('id');
      $currencyID -> value = $code;
      $rateNode = $dom -> createElement("rate",number_format($rate,4,'.',''));
      $codeNameNode = $dom -> createElement("name", $name);
      $locNode = $dom -> createElement("loc", $loc);

      $currencies = $dom -> getElementsByTagName("currencies") -> item(0);

      $currency -> appendChild($currencyID);
      $currency -> appendChild($codeNameNode);
      $currency -> appendChild($locNode);
      $currency -> appendChild($rateNode);
      $currencies -> appendChild($currency);
      $dom -> save("rates.xml");
      $response = '<?xml version="1.0" encoding="UTF-8" ?>';
      $response .= '<method type="PUT">';
      $response .= '<at>'.date("d D M Y H:i").'</at>';
      $response .= '<rate>'.number_format($rate,4).'</rate>';
      $response .= '<curr>';
      $response .= '<code>'.$code.'</code>';
      $response .= '<name>'.$name.'</name>';
      $response .= '<loc>'.$loc.'</loc>';
      $response .= '</curr>';
      $response .= '</method>';
      echo $response;
      die();
    } else { // 2500 Error in service since rates.xml not found
      echo getError("2500");
      die();
    }
  } else if ($rate == null) { // 2100 Rate in wrong format or is missing
    echo getError("2100");
    die();
  } else if ($code == null) { // 2200 Currency code in wrong format or is missing
    echo getError("2200");
    die();
  } else if ($loc == null) { // 2300 Country name in wrong format or is missing
    echo getError("2300");
    die();
  } else if ($name == null) { // 2600 Currency name in wrong format or is missing
    echo getError("2600");
    die();
  }
  /*
    Note Cannot catch the Error 2000 "Method not recognized or is missing"
    because one of the methods is already checked when loaded from the start
    Prevention is better than cure :)
  */



  /////////////Main block ends

  ///////////////////////////////////////////////////

  //Functions blcok starts

  /*
    Get Error String in xml format
  */
  function getError($id) {
    $xml = simplexml_load_file("errors.xml");
    $result = $xml -> xpath("//error[@id='{$id}']");
    $error = $result[0];
    $response = '<?xml version="1.0" encoding="UTF-8" ?>';
    $response .= '<method type="PUT">';
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


  // Functions Blcok ends




?>
