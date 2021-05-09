<?php

require_once "request/Request.php";
require_once "convert/ConvertFile.php";

class CSVRequest {

  public $arrConvertFiles = array();

  public function __construct(SplFileObject $file) {
    $arrLines = $file->fgetcsv();
    $i = 1;
    $columns = $arrLines[0];
    for(; $i<count($arrLines); $i++) {
      $row = $arrLines[$i];
      $data = self::getArrayForRow($columns, $row);
      $cf = new ConvertFile($data["filename"]);
      $this->arrConvertFiles[] = $cf;
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
    $data = array();
    for ($i=0; $i<count($columns); $i++) {
      $data[$columns[$i]] = $row[$i];
    }
    return $data;
  }

  public function convert() {
    $finalResult = 0;
    foreach ($this->arrConvertFiles as $file) {
        try {
            $result = $file->convert();
        } catch (Exception $ex) {
            Logger::error("Got exception for file {}: {}", $file, $ex->getMessage());
            $result = 255;
        } finally {
            $finalResult = max($finalResult, $result);
        }
    }
    return $finalResult;
  }

}
?>
