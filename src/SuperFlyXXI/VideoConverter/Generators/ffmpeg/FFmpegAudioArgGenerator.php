<?php
namespace SuperFlyXXI\VideoConverter\Generators\ffmpeg;

use SuperFlyXXI\VideoConverter\LogWrapper;
use SuperFlyXXI\VideoConverter\Generators\Ffmpeg\FFmpegArgGenerator;
use SuperFlyXXI\VideoConverter\Input\Stream;
use SuperFlyXXI\VideoConverter\Requests\Request;
use SuperFlyXXI\VideoConverter\Input\InputFile;

class FFmpegAudioArgGenerator implements FFmpegArgGenerator
{
    public static $log;

    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream)
    {
        $args = " ";
        if ("copy" != $request->audioFormat) {
            self::$log->debug(
                "Audio Channel Layout Tracks",
                [
                    "audioChannelLayoutTracks" => $request->getAudioChannelLayoutTracks()
                ]
            );
            if ($request->audioChannelLayout != null &&
                ($request->areAllAudioChannelLayoutTracksConsidered() ||
                in_array($inputTrack, $request->getAudioChannelLayoutTracks()))) {
                self::$log->debug("Taking channel layout from request");
                $channelLayout = $request->audioChannelLayout;
                if (null != $channelLayout && preg_match("/(\d+)\.(\d+)/", $channelLayout, $matches)) {
                    $channels = $matches[1] + $matches[2];
                }
            }
            if (! isset($channelLayout)) {
                //self::$log->debug("Using channel layout from original stream");
                //$channelLayout = $stream->channel_layout;
            }
            if (! isset($channels)) {
                self::$log->debug("Using channels from original stream");
                $channels = $stream->channels;
            }
            self::$log->debug(
                "Audio has channelLayout and channels",
                [
                    "outputTrack" => $outTrack,
                    "channelLayout" => $channelLayout,
                    "channels" => $channels
                ]
            );
            $filter = $request->customFilter;
            if (null != $channelLayout && $channels <= $stream->channels) {
                // only change the channel layout if the number of original channels is more than requested
                $channelLayout = preg_replace("/\(.+\)/", "", $channelLayout);
                if (null != $filter) {
                    $filter .= ',';
                }
                $filter .= 'channelmap=channel_layout=' . $channelLayout;
            }
            if (null != $filter) {
                self::$log->debug("Filter available", ["outTrack"=>$outTrack,"filter"=>$filter]);
                $args .= ' -filter:a:' . $inputTrack . ' "' . $filter . '"';
            }
            self::$log->debug(
                "Requsted sample rate vs input sample rate",
                [
                    "requestedAudioSampleRate" => $request->audioSampleRate,
                    "inputAudioSampleRate" => $stream->audio_sample_rate
                ]
            );
            $sampleRate = $request->audioSampleRate;
            if (null == $sampleRate) {
                $sampleRate = $stream->audio_sample_rate;
            }
            if (null != $sampleRate) {
                $args .= " -ar:a:" . $outTrack . " " . $sampleRate;
            }
            $args .= " -c:a:" . $outTrack . " " . $request->audioFormat;
            $args .= " -q:a:" . $outTrack . " " . $request->audioQuality;
        } else {
            // specify copy
            $args .= " -c:a:" . $outTrack . " copy";
        }
        $args .= " -metadata:s:a:" . $outTrack . " language=" . $stream->language;
        if (null != $request->audioTitle) {
            $args .= " -metadata:s:a:" . $outTrack . " title='" . $request->audioTitle ."'";
        }
        return $args;
    }

    public function getStreams(InputFile $inputFile)
    {
        return $inputFile->getAudioStreams();
    }
}

FFmpegAudioArgGenerator::$log = new LogWrapper("FFmpegArgGenerator");
