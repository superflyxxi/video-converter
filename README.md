# video-converter
Tools to convert video

# Docker Image
This image supports ripping a video or bluray directory into an MKV using ffmpeg.

## Environment Variables
Variable | Description | Required | Example/Default
--- | --- | ---
`INPUT` | The bluray directory/drive or file to convert. | Yes | `/mnt/bluray`
`TITLE` | The title to be used in metadata and naming of the file. | Yes | `Cool Movie`
`YEAR` | The year of the movie to be used in metadata and naming of the file. | No | `2019`
`SEASON` | The season of the TV show. | No | `01`
`EPISODE` | The episode within the season of the TV show. | No | `01`
`SUBTITLE` | The episode title of the TV show. | No | `The One Where They Dance`

## Examples

### Ripping Bluray using VAAPI

```
docker run --rm --device /dev/dri:/dev/dri -v /mnt/bluray:/data -e TITLE=Test -e YEAR=2019 rip-video
```

### Ripping specific file without VAAPI
```
docker run --rm -v `pw`:/data -e INPUT=file.mpg -e TITLE=Test -e YEAR=2019 rip-video
```

# ripfile
This is the main process. This will rip a file or bluray drive into an mkv. It can optionally add a normalized track.
