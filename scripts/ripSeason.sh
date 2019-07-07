#!/bin/bash
if [ -z "${SEASON_DIR}" ]; then
	echo "Need to specify SEASON_DIR";
	exit 1;
fi

RIP_SH=/usr/media/rip/ripFile.sh

LOG=${LOG:-n}
if [[ "${LOG}" == "y" ]]; then
	LOGFILE=${OUTPUT_DIR}/${OUTPUT}.log
	exec 1>"${LOGFILE}"
	exec 2>"${LOGFILE}"
fi

IFS=$(echo -en "\n\b")
SEASON_REGEX=${SEASON_REGEX:-".*[sS]\([0-9]\+\).*"}
EPISODE_REGEX=${EPISODE_REGEX:-".*[eE]\([0-9]\+\).*"}
TITLE_REGEX=${TITLE_REGEX:-"^\([a-zA-Z0-9'\! ]\+\).*"}
SUBTITLE_REGEX=${SUBTITLE_REGEX:-"^.*[eE][0-9]\+\(.*\)\..*$"}
SEASON_OVR=${SEASON}
EPISODE_OVR=${EPISODE}
TITLE_OVR=${TITLE}
SUBTITLE_OVR=${SUBTITLE}

set -x
for file in $( ls -1 ${SEASON_DIR} ); do
	SEASON=${SEASON_OVR:-`echo "$file" | sed "s/${SEASON_REGEX}/\1/"`}
	EPISODE=${EPISODE_OVR:-`echo "$file" | sed "s/${EPISODE_REGEX}/\1/"`}
	TITLE=${TITLE_OVR:-`echo "$file" | sed "s/${TITLE_REGEX}/\1/" | sed "s/^\ //" | sed "s/\ $//" | sed "s/\ \ /\ /g"`}
	SUBTITLE=${SUBTITLE_OVR:-`echo "$file" | sed "s/${SUBTITLE_REGEX}/\1/" | sed "s/^\ //" | sed "s/\ $//" | sed "s/\ \ /\ /g"`}
	OUTPUT_DIR="${SEASON_DIR}" INPUT="${SEASON_DIR}/${file}" SEASON=${SEASON} EPISODE=${EPISODE} TITLE=${TITLE} SUBTITLE=${SUBTITLE} ${RIP_SH}
	if [[ "${TEST:-n}" == "y" ]]; then
		exit 0
	fi
done

