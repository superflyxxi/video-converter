<?php
namespace SuperFlyXXI\VideoConverter\Requests;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SplFileObject;
use Exception;
use SuperFlyXXI\VideoConverter\Converters\ConvertFile;

class CSVRequest
{
    public static $log;

    public $arrConvertFiles = [];

    public function __construct(SplFileObject $file)
    {
        $columns = $file->fgetcsv();
        while (! $file->eof()) {
            $row = $file->fgetcsv();
            if ([
                null
            ] !== $row) {
                $data = self::getArrayForRow($columns, $row);
                self::$log->debug("Creating metadata", [
                    "metadata" => $data
                ]);
                $req = Request::newInstanceFromEnv($data["filename"]);
                $this->arrConvertFiles[] = $req;
                foreach (array_keys($data) as $key) {
                    $value = $data[$key];
                    if ($value != null) {
                        switch ($key) {
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

                            case "show-title":
                                $req->showTitle = $value;
                                break;

                            case "playlist":
                                $req->playlist = $value;
                                break;

                            case "subtitle-tracks":
                                $req->setSubtitleTracks($value);
                                break;

                            case "subtitle-format":
                                $req->subtitleFormat = $value;
                                break;

                            case "audio-tracks":
                                $req->setAudioTracks($value);
                                break;

                            case "audio-format":
                                $req->audioFormat = $value;
                                break;

                            case "audio-quality":
                                $req->audioQuality = $value;
                                break;

                            case "audio-channel-layout":
                                $req->audioChannelLayout = $value;
                                break;

                            case "audio-channel-layout-tracks":
                                $req->setAudioChannelLayoutTracks($value);
                                break;

                            case "audio-sample-rate":
                                $req->audioSampleRate = $value;
                                break;

                            case "normalize-audio-tracks":
                                $req->setNormalizeAudioTracks($value);
                                break;

                            case "normalize-audio-format":
                                $req->normalizeAudioFormat = $value;
                                break;

                            case "normalize-audio-quality":
                                $req->normalizeAudioQuality = $value;
                                break;

                            case "video-tracks":
                                $req->setVideoTracks($value);
                                break;

                            case "video-format":
                                $req->videoFormat = $value;
                                break;

                            case "deinterlace":
                                $req->setDeinterlace($value);
                                break;

                            case "filename":
                                // valid but do nothing
                                break;

                            default:
                                // invalid
                                self::$log->debug("Invalid CSV Header", [
                                    "header" => $key,
                                    "value" => $value
                                ]);
                                break;
                        }
                    }
                }
            }
            $req->prepareStreams();
        }
    }

    private static function getArrayForRow($columns, $row)
    {
        self::$log->debug("Get array for row", [
            "columns" => $columns,
            "row" => $row
        ]);
        $data = [];
        for ($i = 0; $i < count($columns); $i ++) {
            $data[$columns[$i]] = $row[$i];
        }
        return $data;
    }

    public function convert()
    {
        self::$log->info("Starting conversion");
        $finalResult = 0;
        foreach ($this->arrConvertFiles as $req) {
            self::$log->info("Beginning to convert", [
                "req" => $req
            ]);
            $result = 255;
            try {
                $convert = new ConvertFile($req);
                $result = $convert->convert();
            } catch (Exception $ex) {
                self::$log->error(
                    "Got exception for file",
                    [
                        "errorMessage" => $ex->getMessage(),
                        "trace" => $ex->getTraceAsString()
                    ]
                );
            } finally {
                $finalResult = max($finalResult, $result);
            }
        }
        return $finalResult;
    }
}

CSVRequest::$log = new LogWrapper("CSVRequest");
