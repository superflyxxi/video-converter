[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=alert_status)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=security_rating)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter)

[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=bugs)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter)
[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=code_smells)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=coverage)](https://sonarcloud.io/summary/new_code?id=superflyxxi_video-converter)
[![Duplicated Lines (%)](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=duplicated_lines_density)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=sqale_index)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter)
[![Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=superflyxxi_video-converter&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=superflyxxi_video-converter):

# video-converter

Tools to convert video

## Limitations

- Channel layouts that end in `(side)` will not be supported.
- Cannot normalize audio and change the channel layouts in one pass.
- DVD directory/ISO are not yet supported.

## Docker Image

This image supports ripping a video or bluray directory into an MKV using ffmpeg. As a result,
you'll see in the working directory a file with the following naming:
`{title} ({year}) - s{season}e{episode} - {show-title}.{filename}.mkv`.
You may want to rip the bluray to mkv before running this tool as ffmpeg is not very good at metadata
from blurays.

`docker run {image} [options] [filename]`

### CLI Options

All the command line options listed below can also be overridden using an environment variable. Any `-` will be
replaced with `_`. Any `.` will be replaced with two `_`. For example, `--log-level` can be set using the
`LOG_LEVEL` environment variable. The final argument will be the filename to process.

Required Options:
- `--title`

Option (short) | Description | Default | Example
--- | --- | --- | ---
`--log-level` | The logging level to use. [Log Levels](https://github.com/Seldaek/monolog/blob/main/doc/01-usage.md#log-levels). | `250` | `100`
`--disable-postfix` | Pass this to avoid having the input filename as a postfix to the output files. | | 
`--playlist` | If the input is bluray, override the playlist to be used. | | `183`
`--title` | The title to be used in metadata and naming of the file. | | `Cool Movie`
`--year` | The year of the movie to be used in metadata and naming of the file. | | `2019`
`--season` | The season of the TV show. | | `01`
`--episode` | The episode within the season of the TV show. | | `01`
`--show-title` | The episode title of the TV show. | | `The One Where They Dance`
`--video-tracks` | The input video tracks to convert. | `*` | `0`
`--video-format` | The desired output video format to use. | `libx265` | `hevc_vaapi`
`--video-upscale` | The upscale multiplier to use for uscaling the video. | `1` | `2.25`
`--hdr` | The input is in HDR and the desired output should also be HDR. | |
`--deinterlace-mode` | Whether to use fieldmap/decimate which will allow 30fps to 24fps(`00`), double framerate (`01`), or default behavior of deinterlacing while keeping the same framerate (`02`). | `02` | `00`
`--deinterlace` | Whether deinterlacing should be performed. `false` will disable deinterlacing. `true` will force deinterlacing. `check-idet` will determine wether more than 1% of frames are interlaced. `check-probe` will be based on video data. | `false` | `probe`
`--audio-tracks` | The input audio tracks to convert. | `*` | `1`
`--audio-format` | The desired output audio format. | `ac3` | `aac`
`--audio-quality` | The desired output audio quality based on the `--audio-format`. | `576` | `2`
`--audio-sample-rate` | The desired output audio sample rate. If not provided, input sample rate will be used. | | `48000`
`--audio-channel-layout` | The desired output audio channel layout. | ` ` | `5.1`
`--audio-channel-layout-tracks` | The space-separated list of input audio tracks that should have the `--audio-channel-layout` applied. | `*` | `1`
`--normalize-audio-tracks` | The space-separated list of input audio tracks that should be normalized. | | `1 2`
`--normalize-audio-format` | The desired output audio format for normalized tracks. | --audio-format | `aac`
`--normalize-audio-quality` | The desired output audio quality based on the `--normalize-audio-format` for normalized tracks. | --audio-quality | `2`
`--subtitle-tracks` | The input subtitle tracks to convert. | `*` | `1`
`--subtitle-format` | The desired output subtitle format. | `ass` | `srt`
`--subtitle-conversion-output` | The mode for which the conversion of image subtitles to srt should be stored. `MERGE`: merge it with the mkv. `FILE`: keep each file separate. | `MERGE` | `FILE`
`--subtitle-conversion-blacklist` | Characters to blacklist during subtitle conversion. Note: It's best to use single quote around the values when passing argument values. | `` \ |~/`_ `` | `\ |`

### CSV File

If the `filename` is a CSV file, the files defined within the CSV will be converted based on the definition within.
The options listed below are not supported; all others are supported in the CSV.
A `filename` header must be provided in order for this to function. If a header is provided, then every row must have a
value for that header. Any setting not mentioned in the CSV will default to the CLI option's value.

- `--disable-postfix`
- `--hdr`
- `--deinterlace-check`
- `--subtitle-conversion-output`
- `--subtitle-conversion-blacklist`

### Examples

#### Ripping Bluray using VAAPI

This is currently untested.

```sh
docker run --rm -it --device /dev/dri -v /mnt/bluray:/data -w /data video-converter --title=Test --year=2019 .
```

#### Ripping specific file without VAAPI

```sh
docker run --rm -it -v "$(pwd):/data" -w /data video-converter --title=Test --year=2019 file.mpg
```
