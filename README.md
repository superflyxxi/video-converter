# video-converter
Tools to convert video

# Docker Image
This image supports ripping a video or bluray directory into an MKV using ffmpeg.

## Environment Variables
Variable | Description | Required | Example/Default
--- | --- | ---
`TITLE` | The title to be used in metadata and naming of the file. | Yes | `Cool Movie`
`YEAR` | The year of the movie to be used in metadata and naming of the file. | Yes | `2019`

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
