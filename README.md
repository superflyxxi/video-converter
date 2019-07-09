# video-converter
Tools to convert video

# Docker Image
This image supports ripping a video or bluray directory into an MKV using ffmpeg. As a result, 
you'll see in the same directory mapped to `/data` a file with the following naming:
`{TITLE} ({YEAR}) - s{SEASON}e{EPISODE} - {SUBTITLE}.ffmpeg.mkv`

## Environment Variables
Variable | Description | Required | Default | Example
--- | --- | --- | --- | ---
`INPUT` | The bluray directory/drive or file to convert. | Yes | | `/mnt/bluray`
`TITLE` | The title to be used in metadata and naming of the file. | Yes | | `Cool Movie`
`YEAR` | The year of the movie to be used in metadata and naming of the file. | No | | `2019`
`SEASON` | The season of the TV show. | No | | `01`
`EPISODE` | The episode within the season of the TV show. | No | | `01`
`SUBTITLE` | The episode title of the TV show. | No | | `The One Where They Dance`
`PLAYLIST` | If the input is bluray, override the playlist to be used. | No | | `183`
`SUBTITLE_TRACK` | The input subtitle tracks to convert. | No | `s?` | `1`
`SUBTITLE_FORMAT` | The desired output subtitle format. | No | `ass` | `copy`
`AUDIO_TRACK` | The input audio tracks to convert. | No | `a` | `1`
`AUDIO_FORMAT` | The desired output audio format. | No | `aac` | `eac3`
`AUDIO_QUALITY` | The desired output audio quality based on the `AUDIO_FORMAT`. | No | `2` | `560`
`AUDIO_CHANNEL_LAYOUT` | The desired output audio channel layout. | No | `5.1` | `7.1`
`AUDIO_CHANNEL_MAPPING_TRACKS` | The space-separated list of input audio tracks that should have the `AUDIO_CHANNEL_LAYOUT` applied. | No | `1` | `1 2 3 4`
`NORMALIZE_AUDIO_TRACKS` | The space-separated list of input audio tracks that should be normalized. | No | ` ` | `1 2 3`
`VIDEO_TRACK` | The input video tracks to convert. | No | `v` | `0`
`VIDEO_FORMAT` | The desired output video format to use. This is ignored unless it is `copy`. | No | `nocopy` | `copy`
`DEINTERLACE` | Boolean determining whether deinterlacing should be done. Only valid if `HWACCEL=y`. | No | `n` | `y`
`HDR` | The input is in HDR and the desired output should also be HDR. | No | `n` | `y`

## Examples

### Ripping Bluray using VAAPI
This is currently untested.
```
docker run --rm -it --device /dev/dri:/dev/dri -v /mnt/bluray:/data -e TITLE=Test -e YEAR=2019 rip-video
```

### Ripping specific file without VAAPI
```
docker run --rm -it -v `pwd`:/data -e INPUT=file.mpg -e TITLE=Test -e YEAR=2019 rip-video

```

### Find correct playlist of Bluray
```
docker run --rm -it -v /mnt/bluray:/data --entrypoint /home/ripvideo/scripts/find-playlist.perl rip-video /data
```
