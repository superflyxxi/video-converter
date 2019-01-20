#!/bin/bash
if [ -z "${INPUT}" ]; then
	echo "Need to specify INPUT";
	exit 1;
fi

LOG=${LOG:-y}
if [[ "${LOG}" == "y" ]]; then
	LOGFILE=${INPUT}-norm.log
	exec 1>"${LOGFILE}"
	exec 2>"${LOGFILE}"
fi

AUDIO_CHANNEL_LAYOUT=${AUDIO_CHANNEL_LAYOUT:-5.1}
AUDIO_QUALITY=${AUDIO_QUALITY:-2} # Variable Bitrate of 2 is good
AUDIO_FORMAT=${AUDIO_FORMAT:-aac} # libfdk_aac is for aac highest quality, aac for great quality, and eac3 is Dolby Digital Ex
NORMALIZE_TRACK=${NORMALIZE_TRACK:-1}
set -x

# Save an Array of Values from output for only measured values
VALUES=(`ffmpeg -i "${INPUT}" -map 0:${NORMALIZE_TRACK} -filter:a loudnorm=print_format=json -f null - 2>&1 | grep \"input | awk '{print $3}' | grep -o "\-*[0-9.]*"`)
echo VALUES=${VALUES[*]}
echo Measured_I=${VALUES[0]} Measured_TP=${VALUES[1]} Measured_LRA=${VALUES[2]} Measured_Thresh=${VALUES[3]}
ffmpeg -i "${INPUT}" -y -map 0:${NORMALIZE_TRACK} -filter:a \
	loudnorm=measured_I=${VALUES[0]}:measured_TP=${VALUES[1]}:measured_LRA=${VALUES[2]}:measured_thresh=${VALUES[3]} \
	-filter:a channelmap=channel_layout=${AUDIO_CHANNEL_LAYOUT} -c ${AUDIO_FORMAT} -q ${AUDIO_QUALITY} \
	-metadata:s:a:0 "title=Normalized Audio" \
	-f matroska "${INPUT}-normAudio.mkv"
ffmpeg -i "${INPUT}" -i "${INPUT}-normAudio.mkv" -y -map 0:v -map 0:a -map 1:a -map 0:s? \
	-c copy -f matroska "${INPUT}-norm.mkv"

