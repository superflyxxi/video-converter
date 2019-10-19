<?php

interface FFmpegArgGenerator
{

    public function getStreams();

    public function getAdditionalArgs($outTrack, $stream);
}
?>
