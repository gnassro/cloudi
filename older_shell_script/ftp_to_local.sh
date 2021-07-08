#!/bin/bash

#   Cloudi PFE
#   FTP - FTP tansfert in background
#   Argum : $1  =>  user_id
#           $2  =>  ftp_user_name src
#           $3  =>  ftp_password src
#           $4  =>  ftp_server src
#           $6 =>  ftp_port src
#           $5  =>  file/directorie src


LOG_OUTPUT="/home/cloudi_log/${1}/log_down"
LOG_OUTPUT_FILTER_REF="/home/cloudi_log/${1}/log_down_filter.ref"
LOG_OUTPUT_FILTER="/home/cloudi_log/${1}/log_down_filter"
TEMP_FILES_FOLDER="/home/cloudi_log/${1}/TEMP"

DELETE_OPTION=""
if [[ "${7}" == "mv" ]]
then
DELETE_OPTION="-DD"
fi
echo ${2}
echo ${3}
echo ${4}
echo ${5}
echo ${6}
echo ${7}
ncftpget -R ${DELETE_OPTION} -T -V -d ${LOG_OUTPUT} -u ${2} -p ${3} -P ${6} ${4} ${TEMP_FILES_FOLDER} ${5}

cat ${LOG_OUTPUT} | grep -A 2 "Cmd: RETR ${5}" >  ${LOG_OUTPUT_FILTER_REF}

cat ${LOG_OUTPUT_FILTER_REF} | grep -v -e "150: Accepted data connection" -e "--" -e "ncftpget ${5}: server said: Can't open ${5}: No such file or directory" > ${LOG_OUTPUT_FILTER}
