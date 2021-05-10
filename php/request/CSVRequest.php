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
              $cf->title = $value;
              break;

            case "year":
              $cf->year = $value;
              break;

            case "season":
              $cf->season = $value;
              break;

            case "episode":
              $cf->episode = $value;
              break;

            case "subtitle":
              $cf->subtitle = $value;
              break;

            case "playlist":
              $cf->oRequest->playlist = $value;
              break;

            case "subtitleTracks":
              $cf->oRequest->setSubtitleTracks($value);
              break;

            case "audioTracks":
              $cf->oRequest->setAudioTracks($value);
              break;

            case "videoTracks":
              $cf->oRequest->setVideoTracks($value);
              break;

            case "subtitleFormat":
              $cf->oRequest->subtitleFormat = $value;
              break;

            case "audioFormat":
              $cf->oRequest->audioFormat = $value;
              break;

            case "audioQuality":
              $cf->oRequest->audioQuality = $value;
              break;

            case "audioChannelLayout":
              $cf->oRequest->audioChannelLayout = $value;
              break;

            case "audioSampleRate":
              $cf->oRequest->audioSampleRate = $value;
              break;

            case "videoFormat":
              $cf->oRequest->videoFormat = $value;
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
    foreach ($this->arrConvertFiles as $file) {
      Logger::info("Beginning to convert {}", $file);
      $result = 255;
      try {
          $result = $file->convert(Request::newInstanceFromEnv("/data/".$file));
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
