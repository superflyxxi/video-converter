<?php

interface FFmpegArgGenerator
{

    public function getStreams($inputFile);

    public function getAdditionalArgs($outTrack, $request, $stream);
}
?>
