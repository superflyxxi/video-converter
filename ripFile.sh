#!/bin/bash
if [ -z "${INPUT}" ]; then
	echo "Need to specify INPUT";
	exit 1;
fi

if [ -z "${TITLE}" ]; then
	echo "Need to specify TITLE or YEAR";
	exit 2;
fi

FFMPEG_DOCKER=${FFMPEG_DOCKER:-ffmpeg-vaapi}
if [[ "y" == "${DOCKER_PULL:-y}" ]]; then
	docker pull ${FFMPEG_DOCKER}
fi

if [ -z "${OUTPUT}" ]; then
	OUTPUT="${TITLE}"
	if [ ! -z "${SEASON}" ]; then
		OUTPUT="${OUTPUT} - s${SEASON}e${EPISODE}"
	fi
	if [ ! -z "${SUBTITLE}" ]; then
		OUTPUT="${OUTPUT} - ${SUBTITLE}"
	fi
	if [ ! -z "${YEAR}" ]; then
		OUTPUT="${OUTPUT} (${YEAR})"
	fi
fi
OUTPUT_DIR=${OUTPUT_DIR:-/data}

INPUT_EXT="${INPUT: -4}"
if [[ -d ${INPUT} ]] || [[ ${INPUT_EXT,,} == ".iso" ]]; then
	echo "Using bluray directory"
	INPUT_PREFIX="bluray:"
else
	echo "Using filename"
fi

if [ -v PLAYLIST ]; then
	PLAYLIST_ARGS="-playlist ${PLAYLIST}";
fi

SUBTITLE_TRACK=${SUBTITLE_TRACK:-s?}
SUBTITLE_FORMAT=${SUBTITLE_FORMAT:-ass}
SUBTITLE_TRACK_ARGS="-c:s ${SUBTITLE_FORMAT} -map 0:${SUBTITLE_TRACK}"

AUDIO_TRACK=${AUDIO_TRACK:-a}
AUDIO_CHANNEL_LAYOUT=${AUDIO_CHANNEL_LAYOUT:-5.1}
# AUDIO_QUALITY=${AUDIO_QUALITY:-576}
AUDIO_QUALITY=${AUDIO_QUALITY:-2} # Variable Bitrate of 2 is good
AUDIO_FORMAT=${AUDIO_FORMAT:-aac} # libfdk_aac is for aac highest quality, aac for great quality, and eac3 is Dolby Digital Ex
# AUDIO_TRACK_ARGS="-c:a ${AUDIO_FORMAT} -q:a ${AUDIO_QUALITY} -map 0:${AUDIO_TRACK}"
AUDIO_TRACK_ARGS="-map 0:${AUDIO_TRACK} -c:a ${AUDIO_FORMAT}"
if [[ "copy" != "${AUDIO_FORMAT}" ]]; then
	AUDIO_TRACK_ARGS="-filter:a channelmap=channel_layout=${AUDIO_CHANNEL_LAYOUT} ${AUDIO_TRACK_ARGS} -q:a ${AUDIO_QUALITY}"
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

echo INPUT=${INPUT}
if [[ -f "${INPUT}" ]]; then
	# if a file
	FILE_DIR=`dirname "${INPUT}"`
	FILE_DIR=`realpath "${FILE_DIR}"`
	CONTAINER_INPUT=/data/`basename "${INPUT}"`
else
	FILE_DIR=`realpath "${INPUT}"`
	CONTAINER_INPUT="/data"
fi

if [[ "${DOCKER_DAEMON:-n}" == "y" ]]; then
	DOCKER_DAEMON_ARGS="-d"
else
	DOCKER_DAEMON_ARGS="-it"
fi

OUTPUT_FILE="${OUTPUT_DIR}/${OUTPUT}.ffmpeg.mkv"

set -ex
docker run \
  ${DOCKER_HWACCEL_ARGS} \
  --name ${INPUT} \
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

