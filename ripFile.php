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

AUDIO_TRACK=${AUDIO_TRACK:-a}
AUDIO_CHANNEL_MAPPING_TRACKS=${AUDIO_CHANNEL_MAPPING_TRACKS:-1}
AUDIO_CHANNEL_LAYOUT=${AUDIO_CHANNEL_LAYOUT:-5.1}
AUDIO_QUALITY=${AUDIO_QUALITY:-2} # Variable Bitrate of 2 is good
AUDIO_FORMAT=${AUDIO_FORMAT:-aac} # libfdk_aac is for aac highest quality, aac for great quality, and eac3 is Dolby Digital Ex
AUDIO_TRACK_ARGS="-map 0:${AUDIO_TRACK} -c:a ${AUDIO_FORMAT}"
if [[ "copy" != "${AUDIO_FORMAT}" ]]; then
	for audioLayoutTrack in ${AUDIO_CHANNEL_MAPPING_TRACKS}; do
		AUDIO_LAYOUT_ARGS="${AUDIO_LAYOUT_ARGS} -filter:${audioLayoutTrack} channelmap=channel_layout=${AUDIO_CHANNEL_LAYOUT}"
	done
	AUDIO_TRACK_ARGS="${AUDIO_TRACK_ARGS} ${AUDIO_LAYOUT_ARGS} -q:a ${AUDIO_QUALITY}"
fi

HWACCEL=${HWACCEL:-y}
if [[ "${HWACCEL}" == "y" ]]; then
	DOCKER_HWACCEL_ARGS="--device /dev/dri:/dev/dri"
	FFMPEG_HWACCEL_ARGS="-hwaccel vaapi -hwaccel_output_format vaapi -hwaccel_device /dev/dri/renderD128"
	if [[ "${DEINTERLACE:-n}" == "y" ]]; then
		DEINTERLACE_ARGS="-vf deinterlace_vaapi=rate=field:auto=1"
	fi
else
	DOCKER_HWACCEL_ARGS="--user ${UID}"
fi

VIDEO_TRACK=${VIDEO_TRACK:-v}
VIDEO_FROMAT=${VIDEO_FORMAT:-notcopy}
VIDEO_TRACK_ARGS="-map 0:${VIDEO_TRACK}"
if [[ "${VIDEO_FROMAT}" == "copy" ]]; then
        VIDEO_TRACK_ARGS="${VIDEO_TRACK_ARGS} -c:v copy"
elif [[ "${HWACCEL}" == "y" ]]; then
	VIDEO_TRACK_ARGS="${VIDEO_TRACK_ARGS} -c:v hevc_vaapi -qp 20 -level:v 41"
else
	VIDEO_TRACK_ARGS="${VIDEO_TRACK_ARGS} -c:v libx265 -crf 20 -level:v 41"
fi

if [[ "${HDR:-n}" == "y" ]]; then
	VIDEO_TRACK_ARGS="-c:v libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10 -map 0:${VIDEO_TRACK}"
fi

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

