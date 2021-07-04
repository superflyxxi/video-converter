# video-converter
Tools to convert video

## Limitations
- Channel layouts that end in `(side)` will not be supported.
- DVD directory/ISO are not yet supported.

## Docker Image
This image supports ripping a video or bluray directory into an MKV using ffmpeg. As a result, 
you'll see in the same directory mapped to `/data` a file with the following naming:
`{TITLE} ({YEAR}) - s{SEASON}e{EPISODE} - {SUBTITLE}.{inputFileName}.mkv`. 
You may want to rip the bluray to mkv before running this tool as ffmpeg is not very good at metadata 
from blurays.

### Environment Variables
Variable | Description | Required | Default | Example
--- | --- | --- | --- | ---
`INPUT` | The bluray directory/drive or file to convert. If not provided, all files in `/data` will be converted. | No | | `title_00.mkv`
`TITLE`\* | The title to be used in metadata and naming of the file. | Yes | | `Cool Movie`
`YEAR`\* | The year of the movie to be used in metadata and naming of the file. | No | | `2019`
`SEASON`\* | The season of the TV show. | No | | `01`
`EPISODE`\* | The episode within the season of the TV show. | No | | `01`
`SUBTITLE`\* | The episode title of the TV show. | No | | `The One Where They Dance`
`PLAYLIST`\* | If the input is bluray, override the playlist to be used. | No | | `183`
`SUBTITLE_TRACKS`\* | The input subtitle tracks to convert. | No | `*` | `1`
`SUBTITLE_FORMAT`\* | The desired output subtitle format. | No | `ass` | `copy`
`SUBTITLE_CONVERSION_OUTPUT` | The mode for which the conversion of image subtitles to srt should be stored. `MERGE`: merge it with the mkv. `FILE`: keep each file separate. | No | `MERGE` | `FILE`
`SUBTITLE_CONVERSION_BLACKLIST` | Characters to blacklist during subtitle conversion. Note: It's best to use single quote around the values when passing to docker as `-e`. | No | ``\|~/`_`` | `\|`
`AUDIO_TRACKS` | The input audio tracks to convert. | No | `*` | `1`
`AUDIO_FORMAT`\* | The desired output audio format. | No | `aac` | `eac3`
`AUDIO_QUALITY`\* | The desired output audio quality based on the `AUDIO_FORMAT`. | No | `2` | `560`
`AUDIO_SAMPLE_RATE`\* | The desired output audio sample rate. If not provided, input sample rate will be used. | No | | `48000`
`AUDIO_CHANNEL_LAYOUT`\* | The desired output audio channel layout. | No | ` ` | `7.1`
`AUDIO_CHANNEL_LAYOUT_TRACKS` | The space-separated list of input audio tracks that should have the `AUDIO_CHANNEL_LAYOUT` applied. | No | `*` | `1`
`NORMALIZE_AUDIO_TRACKS` | The space-separated list of input audio tracks that should be normalized. | No | | `1 2`
`VIDEO_TRACKS` | The input video tracks to convert. | No | `*` | `0`
`VIDEO_FORMAT`\* | The desired output video format to use. This is ignored unless it is `copy`. | No | `nocopy` | `copy`
`DEINTERLACE` | Boolean determining whether deinterlacing should be done. If not specified, deinterlacing will be enabled if the source is interlaced. | No |  | `true`
`DEINTERLACE_CHECK` | How to check for deinterlacing. `idet` will determine wether more than 1% of frames are interlaced. `probe` will be based on video data. | No | `probe` | `idet`
`DEINTERLACE_MODE` | Whether to use fieldmap/decimate which will allow 30fps to 24fps(`00`), double framerate (`01`), or default behavior of deinterlacing while keeping the same framerate (`02`). | No | `02` | `00`
`HDR` | The input is in HDR and the desired output should also be HDR. | No | `false` | `true`
`APPLY_POSTFIX` | Whether to apply the input filename as a postfix to the output files. | No | `true` | `false` 

### CSV File
If the `INPUT` is a CSV file, the files defined within the CSV will be converted based on the definition within.
This environment variables marked with an `*` are supported in camelCase. A `filename` header must be provided in
order for this to function. If a header is provided, then every row must have a value for that header.

### Examples

#### Ripping Bluray using VAAPI
This is currently untested.
```
docker run --rm -it --device /dev/dri:/dev/dri -v /mnt/bluray:/data -e INPUT=. -e TITLE=Test -e YEAR=2019 rip-video
```

#### Ripping specific file without VAAPI
```
docker run --rm -it -v `pwd`:/data -e INPUT=file.mpg -e TITLE=Test -e YEAR=2019 rip-video

```

#### Find correct playlist of Bluray
```
docker run --rm -it -v /mnt/bluray:/data --entrypoint /home/ripvideo/scripts/find-playlist.perl rip-video /data
```

