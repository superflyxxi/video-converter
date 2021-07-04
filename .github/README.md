# video-converter
Tools to convert video

# Limitations
- Channel layouts that end in `(side)` will not be supported.
- DVD directory/ISO are not yet supported.

# Docker Image
This image supports ripping a video or bluray directory into an MKV using ffmpeg. As a result, 
you'll see in the same directory mapped to `/data` a file with the following naming:
`{TITLE} ({YEAR}) - s{SEASON}e{EPISODE} - {SUBTITLE}.{inputFileName}.mkv`. 
You may want to rip the bluray to mkv before running this tool as ffmpeg is not very good at metadata 
from blurays.

## Variables
Environment Variable | CSV Heading | Description | Required | Default | Example
--- | --- | --- | --- | --- | ---
`INPUT` | `filename`| The bluray directory/drive or file to convert. If not provided, all files in `/data` will be converted. | No | | `title_00.mkv`
`TITLE` | `title`| The title to be used in metadata and naming of the file. | Yes | | `Cool Movie`
`YEAR` | `year` | The year of the movie to be used in metadata and naming of the file. | No | | `2019`
`SEASON` | `season` | The season of the TV show. | No | | `01`
`EPISODE` | `episode` | The episode within the season of the TV show. | No | | `01`
`SUBTITLE` | `subtitle` | The episode title of the TV show. | No | | `The One Where They Dance`
`PLAYLIST` | `playlist` | If the input is bluray, override the playlist to be used. | No | | `183`
`SUBTITLE_TRACKS` | `subtitleTracks` | The input subtitle tracks to convert. | No | `*` | `1`
`SUBTITLE_FORMAT` | `subtitleFormat` | The desired output subtitle format. | No | `ass` | `copy`
`SUBTITLE_CONVERSION_OUTPUT` | N/A | The mode for which the conversion of image subtitles to srt should be stored. `MERGE`: merge it with the mkv. `FILE`: keep each file separate. | No | `MERGE` | `FILE`
`SUBTITLE_CONVERSION_BLACKLIST` | N/A | Characters to blacklist during subtitle conversion. Note: It's best to use single quote around the values when passing to docker as `-e`. | No | ``\|~/`_`` | `\|`
`AUDIO_TRACKS` | N/A | The input audio tracks to convert. | No | `*` | `1`
`AUDIO_FORMAT` | `audioFormat` | The desired output audio format. | No | `aac` | `eac3`
`AUDIO_QUALITY` | `audioQuality` | The desired output audio quality based on the `AUDIO_FORMAT`. | No | `2` | `560`
`AUDIO_SAMPLE_RATE` | `audioSampleRate` | The desired output audio sample rate. If not provided, input sample rate will be used. | No | | `48000`
`AUDIO_CHANNEL_LAYOUT` | `audioChannelLayout` | The desired output audio channel layout. | No | ` ` | `7.1`
`AUDIO_CHANNEL_LAYOUT_TRACKS` | N/A | The space-separated list of input audio tracks that should have the `AUDIO_CHANNEL_LAYOUT` applied. | No | `*` | `1`
`NORMALIZE_AUDIO_TRACKS` | N/A | The space-separated list of input audio tracks that should be normalized. | No | | `1 2`
`VIDEO_TRACKS` | N/A | The input video tracks to convert. | No | `*` | `0`
`VIDEO_FORMAT` | `videoFormat` | The desired output video format to use. This is ignored unless it is `copy`. | No | `nocopy` | `copy`
`DEINTERLACE` | N/A | Boolean determining whether deinterlacing should be done. If not specified, deinterlacing will be enabled if the source is interlaced. | No |  | `true`
`DEINTERLACE_CHECK` | N/A | How to check for deinterlacing. `idet` will determine wether more than 1% of frames are interlaced. `probe` will be based on video data. | No | `probe` | `idet`
`DEINTERLACE_MODE` | N/A | Whether to use fieldmap/decimate which will allow 30fps to 24fps(`00`), double framerate (`01`), or default behavior of deinterlacing while keeping the same framerate (`02`). | No | `02` | `00`
`HDR` | N/A | The input is in HDR and the desired output should also be HDR. | No | `false` | `true`
`APPLY_POSTFIX` | N/A | Whether to apply the input filename as a postfix to the output files. | No | `true` | `false` 

## Examples

### Ripping Bluray using VAAPI
This is currently untested.
```
docker run --rm -it --device /dev/dri:/dev/dri -v /mnt/bluray:/data -e INPUT=. -e TITLE=Test -e YEAR=2019 rip-video
```

### Ripping specific file without VAAPI
```
docker run --rm -it -v `pwd`:/data -e INPUT=file.mpg -e TITLE=Test -e YEAR=2019 rip-video

```

### Find correct playlist of Bluray
```
docker run --rm -it -v /mnt/bluray:/data --entrypoint /home/ripvideo/scripts/find-playlist.perl rip-video /data
```

