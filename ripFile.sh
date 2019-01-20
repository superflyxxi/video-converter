#!/bin/bash
if [ -z "${INPUT}" ]; then
	echo "Need to specify INPUT";
	exit 1;
fi

if [ -z "${TITLE}" ]; then
	echo "Need to specify TITLE or YEAR";
	exit 2;
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
OUTPUT_DIR=${OUTPUT_DIR:-/usr/media/rip}

LOG=${LOG:-y}
if [[ "${LOG}" == "y" ]]; then
	LOGFILE=${OUTPUT_DIR}/${OUTPUT}.log
	exec 1>"${LOGFILE}"
	exec 2>"${LOGFILE}"
fi

set -v
INPUT_EXT="${INPUT: -4}"
if [[ -d ${INPUT} ]] || [[ ${INPUT_EXT,,} == ".iso" ]]; then
	echo "Using bluray directory"
	INPUT=bluray:${INPUT}
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
AUDIO_TRACK_ARGS="-filter:a channelmap=channel_layout=${AUDIO_CHANNEL_LAYOUT} -c:a ${AUDIO_FORMAT} -q:a ${AUDIO_QUALITY} -map 0:${AUDIO_TRACK}"

HWACCEL=${HWACCEL:-y}
HWACCEL_ARGS="-hwaccel vaapi "
if [[ ${HWACCEL} == "y" ]]; then
	HWACCEL_ARGS="${HWACCEL_ARGS} -hwaccel_output_format vaapi -hwaccel_device /dev/dri/renderD128"
	if [[ ${DEINTERLACE:-n} == "y" ]]; then
		DEINTERLACE_ARGS="-vf deinterlace_vaapi=rate=field:auto=1"
	fi
fi

VIDEO_TRACK=${VIDEO_TRACK:-v}
if [[ ${HWACCEL} == "y" ]]; then
	VIDEO_TRACK_ARGS="-c:v hevc_vaapi -qp 20 -level:v 41 -map 0:${VIDEO_TRACK}"
else
	VIDEO_TRACK_ARGS="-c:v libx265 -crf 20 -level:v 41 -map 0:${VIDEO_TRACK}"
fi

if [[ ${HDR:-n} == "y" ]]; then
	VIDEO_TRACK_ARGS="-c:v libx265 -crf 20 -level:v 51 -pix_fmt yuv420p10le -color_primaries 9 -color_trc 16 -colorspace 9 -color_range 1 -profile:v main10 -map 0:${VIDEO_TRACK}"
fi

set -x

ffmpeg -${OVERWRITE_FILE:-y} ${HWACCEL_ARGS} \
	${PLAYLIST_ARGS} -i "${INPUT}" \
	${VIDEO_TRACK_ARGS} ${DEINTERLACE_ARGS} \
	${AUDIO_TRACK_ARGS} \
	${SUBTITLE_TRACK_ARGS} \
	-metadata "title"="${TITLE}" -metadata "year"=${YEAR} -metadata "subtitle"="${SUBTITLE}" \
	-metadata "season"="${SEASON}" -metadata "episode"="${EPISODE}" \
	-f matroska "${OUTPUT_DIR}/${OUTPUT}.mkv"

if [[ ${NORMALIZE:-y} == "y" ]]; then
	# Save an Array of Values from output for only measured values
	NORMALIZE_TRACK=${NORMALIZE_TRACK:-1}
	VALUES=(`ffmpeg -i "${OUTPUT_DIR}/${OUTPUT}.mkv" -map 0:${NORMALIZE_TRACK} -filter:a loudnorm=print_format=json -f null - \
		| grep \"input \
		| awk '{print $3}' \
		| grep -o "\-*[0-9.]*"`)
	echo Measured_I=${VALUES[0]} Measured_TP=${VALUES[1]} Measured_LRA=${VALUES[2]} Measured_Thresh=${VALUES[3]}
	ffmpeg -i "${OUTPUT_DIR}/${OUTPUT}.mkv" -map 0:${NORMALIZE_TRACK} -filter:a \
		loudnorm=measured_I=${VALUES[0]}:measured_TP=${VALUES[1]}:measured_LRA=${VALUES[2]}:measured_thresh=${VALUES[3]} \
		-filter:a channelmap=channel_layout=${AUDIO_CHANNEL_LAYOUT} -c ${AUDIO_FORMAT} -q ${AUDIO_QUALITY} \
		-metadata:s:a:0 "title=Normalized Audio" \
		-f matroska "${OUTPUT_DIR}/${OUTPUT}-normAudio.mkv"
	ffmpeg -i "${OUTPUT_DIR}/${OUTPUT}.mkv" -i "${OUTPUT_DIR}/${OUTPUT}-normAudio.mkv" -map 0:v -map 0:a -map 1:a -map 0:s? \
		-c copy -f matroska "${OUTPUT_DIR}/${OUTPUT}-norm.mkv"
fi;

