<?php

include_once "Request.php";

class CSVRequest {

  public function __construct($filename) {
    $f = fopen($filename, "r");
    $colums = fgetcsv($f);
    while (($row = fgetcsv($f)) !== FALSE) {
      $data = self::getArrayForRow($columns, $row);
      $req = Request::newInstanceFromEnv($data["filename"]);
      foreach (array_keys($data) as $key) {
        switch ($key) {
          case "filename":
            // Already Processed
            break;

          case "title":
            $req->setTitle($data[$key]);
            break;
        }
      }
    }
  }

  private static function getArrayForRow($columns, $row) {
    $data = array();
    for ($i=0; $i<count($columns); $i++) {
      $data[$columns[$i]] = $row[$i];
    }
    return $data;
  }
}



?>
