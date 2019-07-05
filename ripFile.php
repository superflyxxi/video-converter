#!/bin/php
<?php

function getEnvWithDefault($env, $default) {
	if (getEnv($env)) {
		return getEnv($env);
	} else {
		return $default;
	}
}

$title = getEnv("TITLE");
if (!$title) {
	print_r("Missing TITLE variable");
	exit(1);
}

$output = getEnv("OUTPUT");
if (!$output) { 
	$output = $title;
	if (getEnv("SEASON")) {
		$output .= " - s".getEnv("SEASON")."e".getEnv("EPISODE");
	}
	if (getEnv("SUBTITLE")) {
		$output .= " - ".getEnv("SUBTITLE");
	}
	if (getEnv("YEAR")) {
		$output .= " (".getEnv("YEAR").")";
	}
}
$outputDir = getEnvWithDefault("OUTPUT_DIR", "/data");

$input = getEnvWithDefault("INPUT", "/data");
$inputPrefix = "";
if (is_dir($input) || substr($input, -strlen($input)) === ".iso") {
	print("Using bluray directory");
	$inputPrefix="bluray:";
} else {
	print("Using filename");
}

$playlistArgs = (getEnv("PLAYLIST") ? "-playlist ".getEnv("PLAYLIST") : "");

$subtitleTrackArgs = "-map 0:".getEnvWithDefault("SUBTITLE_TRACK", "s?")
	." -c:s ".getEnvWithDefault("SUBTITLE_FORMAT", "ass");

$audioFormat = getEnvWithDefault("AUDIO_FORMAT", "aac");
$audioTrackArgs = "-map 0:".getEnvWithDefault("AUDIO_TRACK", "a")." -c:a ".$audioFormat;
if ("copy" != $audioFormat) {
	foreach $track in (explode(" ", getEnvWithDefault("AUDIO_CHANNEL_MAPPING_TRACKS", "1"))) {
		$audioTrackArgs .= " -filter:".$track." channelmap=channel_layout=".getEnvWithDefault("AUDIO_CHANNEL_LAYOUT", "5.1");
	}
	$audioTrackArgs .= " -q:a ".getEnvWithDefault("AUDIO_QUALITY", "2");
}

$hwaccel = ("true" == getEnvWithDefault("HWACCEL", "true"));
$ffmpegHwaccelArgs = "";
$dinterlaceArgs = "";
if ($hwaccel) {
	$ffmpegHwaccelArgs = "-hwaccel vaapi -hwaccel_output_format vaapi -hwaccel_device /dev/dri/renderD128";
	if ("true" == getEnvWithDefault("DEINTERLACE", "false")) {
		$deinterlaceArgs = "-vf deinterlace_vaapi=rate=field:auto=1";
	}
}

$videoFromat = getEnvWithDefault("VIDEO_FORMAT", "notcopy");
$videoTrackArgs = "-map 0:".getEnvWithDefault("VIDEO_TRACK", "v");
if ( "copy" == $videoFromat ) {
        $videoTrackArgs .= " -c:v copy";
} else if ($hwaccel) {
	$videoTrackArgs .= " -c:v hevc_vaapi -qp 20 -level:v 41";
} else if ("true" == getEnvWithDefault("HDR", "false")) {
	$videoTrackArgs .= " -c:v libx265 -crf 20 -level:v 41";
} else {
	$videoTrackArgs .= " -c:v libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10";
}

if [[ -f "${INPUT}" ]]; then
	# if a file
	FILE_DIR=`dirname "${INPUT}"`
	FILE_DIR=`realpath "${FILE_DIR}"`
	CONTAINER_INPUT=/data/`basename "${INPUT}"`
else
	FILE_DIR=`realpath "${INPUT}"`
	CONTAINER_INPUT="/data"
fi
OUTPUT_FILE="${OUTPUT_DIR}/${OUTPUT}.ffmpeg.mkv"

CONTAINER_NAME=`echo ${INPUT} | sed 's# #_#g'`

if [[ "${DOCKER_DAEMON:-n}" == "y" ]]; then
	DOCKER_DAEMON_ARGS="-d"
else
	DOCKER_DAEMON_ARGS="-it"
	echo INPUT=${INPUT}
	echo TITLE=${TITLE}
	echo SUBTITLE=${SUBTITLE}
	echo YEAR=${YEAR}
	echo SEASON=${SEASON}
	echo EPISODE=${EPISODE}
	echo FFMPEG_HWACCEL_ARGS=${FFMPEG_HWACCEL_ARGS}
	echo DEINTERLACE_ARGS=${DEINTERLACE_ARGS}
	echo VIDEO_TRACK_ARGS=${VIDEO_TRACK_ARGS}
	echo PLAYLIST_ARGS=${PLAYLIST_ARGS}
	echo AUDIO_TRACK_ARGS=${AUDIO_TRACK_ARGS}
	echo SUBTITLE_TRACK_ARGS=${SUBTITLE_TRACK_ARGS}
	echo OTHER_METADATA=${OTHER_METADATA}
	docker run --rm -it -v "${FILE_DIR}":/data ${FFMPEG_DOCKER} -i "${INPUT_PREFIX}${CONTAINER_INPUT}"
	SLEEP=${SLEEP:-30s}
	echo "Sleeping for ${SLEEP}, now's your chance to stop"
	sleep ${SLEEP}
fi

set -ex
docker run \
  ${DOCKER_HWACCEL_ARGS} \
  --name ${CONTAINER_NAME} \
  -v "${FILE_DIR}":/data \
  ${DOCKER_DAEMON_ARGS} \
  ${FFMPEG_DOCKER} -${OVERWRITE_FILE:-y} \
	${FFMPEG_HWACCEL_ARGS} \
	${PLAYLIST_ARGS} -i "${INPUT_PREFIX}${CONTAINER_INPUT}" \
	${VIDEO_TRACK_ARGS} ${DEINTERLACE_ARGS} \
	${AUDIO_TRACK_ARGS} \
	${SUBTITLE_TRACK_ARGS} \
	-metadata "title"="${TITLE}" -metadata "year"=${YEAR} -metadata "subtitle"="${SUBTITLE}" \
	-metadata "season"="${SEASON}" -metadata "episode"="${EPISODE}" \
	${OTHER_METADATA} \
	-f matroska "${OUTPUT_FILE}"

if [[ "${DOCKER_DAEMON}" != "y" && "${NORMALIZE:-n}" == "y" ]]; then
	# Save an Array of Values from output for only measured values
	NORMALIZE_SH=./normalizeAudio.sh
	INPUT="${OUTPUT_FILE}" AUDIO_CHANNEL_LAYOUT=${AUDIO_CHANNEL_LAYOUT} AUDIO_FORMAT=${AUDIO_FORMAT} \
		AUDIO_QUALITY=${AUDIO_QUALITY} ${NORMALIZE_SH}
fi;
?>

