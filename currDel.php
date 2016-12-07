<?php
  /////////////Main block starts
  require_once("function.php");
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
        echo getError("2600","DELETE"); // 2600 Currency Not Found
        die();
      }
    } else { // 2500 Error in service since rates.xml not found
      echo getError("2500","DELETE");
      die();
    }
  }else if ($code == null) { // 2200 Currency code in wrong format or is missing
    echo getError("2200","DELETE");
    die();
  }
  /*
    Note Cannot catch the Error 2000 "Method not recognized or is missing"
    because one of the methods is already checked when loaded from the start
    Prevention is better than cure :)
  */
  //Main Block ends
  //////////////////////////////////////////////////////////////////////////////////





?>
