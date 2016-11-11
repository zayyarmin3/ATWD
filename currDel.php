<?php
  /////////////Main block starts
  $code = filter_input(INPUT_POST,'code');
  if($code) {
    if(file_exists("rates.xml")) {
      $code = strtoupper($code);

      $dom = new DOMDocument();
      $dom -> load("rates.xml");
      $xpath =new DOMXPath($dom);
      $query = "//currency[@id='{$code}']";
      $currency = $xpath -> query($query) -> item(0);
      $currencies = $dom -> getElementsByTagName('currencies') -> item(0);
      if($currency) {
        $currencies -> removeChild($currency);
        $dom -> save("rates.xml");
        $response = '<?xml version="1.0" encoding="UTF-8" ?>';
        $response .= '<method type="DELETE">';
        $response .= '<at>'.date("d D M Y H:i").'</at>';
        $response .= '<code>'.$code.'</code>';
        $response .= '</method>';
        echo $response;
        die();
      } else {
        echo getError("2600"); // 2600 Currency Not Found
        die();
      }

      die();
    } else { // 2500 Error in service since rates.xml not found
      echo getError("2500");
      die();
    }
  }else if ($code == null) { // 2200 Currency code in wrong format or is missing
    echo getError("2200");
    die();
  }
  /*
    Note Cannot catch the Error 2000 "Method not recognized or is missing"
    because one of the methods is already checked when loaded from the start
    Prevention is better than cure :)
  */
  //Main Block ends
  //////////////////////////////////////////////////////////////////////////////////

  //////////////Functions blcok starts

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

  ////////////// Functions Blcok ends




?>
