<?php
require_once "ffmpeg/generators/FFmpegArgGenerator.php";
require_once "InputFile.php";
require_once "request/Request.php";
require_once "Stream.php";

class FFmpegAudioArgGenerator implements FFmpegArgGenerator
{

    public function getAdditionalArgs($outTrack, Request $request, $inputTrack, Stream $stream)
    {
        $args = " ";
        if ("copy" != $request->audioFormat) {
            Logger::verbose("Audio Channel Layout Tracks {}", $request->getAudioChannelLayoutTracks());
            if ($request->audioChannelLayout != NULL && ($request->areAllAudioChannelLayoutTracksConsidered() || in_array($inputTrack, $request->getAudioChannelLayoutTracks()))) {
                Logger::debug("Taking channel layout from request");
                $channelLayout = $request->audioChannelLayout;
                if (NULL != $channelLayout && preg_match("/(0-9]+)\.([0-9]+)/", $channelLayout, $matches)) {
                    $channels = $matches[1] + $matches[2];
                }
            }
            if (! isset($channelLayout)) {
                Logger::debug("Using channel layout from original stream");
                $channelLayout = $stream->channel_layout;
            }
            if (! isset($channels)) {
                Logger::debug("Using channels from original stream");
                $channels = $stream->channels;
            }
            Logger::debug("Audio {} has channelLayout={} and channels={}", $outTrack, $channelLayout, $channels);
            if (NULL != $channelLayout && $channels <= $stream->channels) {
                // only change the channel layout if the number of original channels is more than requested
                $channelLayout = preg_replace("/\(.+\)/", '', $channelLayout);
                $args .= " -filter:a:" . $outTrack . ' channelmap=channel_layout=' . $channelLayout;
            }
            $args .= " -c:a:" . $outTrack . " " . $request->audioFormat;
            $args .= " -q:a:" . $outTrack . " " . $request->audioQuality;
            Logger::debug("Requsted sample rate vs input sample rate: {} vs {}", $request->audioSampleRate, $stream->audio_sample_rate);
            $sampleRate = $request->audioSampleRate;
            if (NULL != $sampleRate) {
                $sampleRate = $stream->audio_sample_rate;
            }
            if (NULL != $sampleRate) {
                $args .= " -ar:" . $outTrack . " " . $sampleRate;
            }
        } else {
            // specify copy
            $args .= " -c:a:" . $outTrack . " copy";
        }
        $args .= " -metadata:s:a:" . $outTrack . " language=" . $stream->language;
        return $args;
    }

    public function getStreams(InputFile $inputFile)
    {
        return $inputFile->getAudioStreams();
    }
}
?>
