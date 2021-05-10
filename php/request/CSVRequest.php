<?php

require_once "request/Request.php";
require_once "convert/ConvertFile.php";

class CSVRequest {

  public $arrConvertFiles = array();

  public function __construct(SplFileObject $file) {
    $columns = $file->fgetcsv();
    while(!$file->eof()) {
      $row = $file->fgetcsv();
      $data = self::getArrayForRow($columns, $row);
      Logger::debug("Creating metadata: {}", $data);
      $req = Request::newInstanceFromEnv("/data/".$data["filename"]);
      $this->arrConvertFiles[] = $req;
      foreach (array_keys($data) as $key) {
        $value = $data[$key];
        if ($value != NULL) {
          switch ($key) {
            case "filename":
              // Already Processed
              break;

            case "title":
              $req->title = $value;
              break;

            case "year":
              $req->year = $value;
              break;

            case "season":
              $req->season = $value;
              break;

            case "episode":
              $req->episode = $value;
              break;

            case "subtitle":
              $req->subtitle = $value;
              break;

            case "playlist":
              $req->playlist = $value;
              break;

            case "subtitleTracks":
              $req->setSubtitleTracks($value);
              break;

            case "audioTracks":
              $req->setAudioTracks($value);
              break;

            case "videoTracks":
              $req->setVideoTracks($value);
              break;

            case "subtitleFormat":
              $req->subtitleFormat = $value;
              break;

            case "audioFormat":
              $req->audioFormat = $value;
              break;

            case "audioQuality":
              $req->audioQuality = $value;
              break;

            case "audioChannelLayout":
              $req->audioChannelLayout = $value;
              break;

            case "audioSampleRate":
              $req->audioSampleRate = $value;
              break;

            case "videoFormat":
              $req->videoFormat = $value;
              break;
          }
        }
      }
    }
  }

  private static function getArrayForRow($columns, $row) {
    Logger::debug("Get array for row {}={}", $columns, $row);
    $data = array();
    for ($i=0; $i<count($columns); $i++) {
      $data[$columns[$i]] = $row[$i];
    }
    return $data;
  }

  public function convert() {
    Logger::info("Starting conversion");
    $finalResult = 0;
    foreach ($this->arrConvertFiles as $req) {
      Logger::info("Beginning to convert {}", $req);
      $result = 255;
      try {
          $convert = new ConvertFile($req);
          $result = $convert->convert();
      } catch (Exception $ex) {
          Logger::error("Got exception for file {}: {}", $file, $ex->getMessage());
      } finally {
          $finalResult = max($finalResult, $result);
      }
    }
    return $finalResult;
  }

}
?>
