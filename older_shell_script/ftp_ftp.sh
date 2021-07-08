#!/bin/bash

#   Cloudi PFE
#   FTP - FTP tansfert in background
#   Argum : $1  =>  user_id
#           $2  =>  ftp_user_name src
#           $3  =>  ftp_password src
#           $4  =>  ftp_server src
#           $10 =>  ftp_port src
#           $5  =>  file/directorie src
#           --  --  ---
#           $6  =>  ftp_user_name dest
#           $7  =>  ftp_password dest
#           $8  =>  ftp_server dest
#           $11 =>  ftp_port_dest
#           $9  =>  path dest


LOG_OUTPUT="/home/cloudi_log/${1}/log_down"
LOG_OUTPUT_FILTER_REF="/home/cloudi_log/${1}/log_down_filter.ref"
LOG_OUTPUT_FILTER="/home/cloudi_log/${1}/log_down_filter"
TEMP_FILES_FOLDER="/home/cloudi_log/${1}/TEMP"

DELETE_OPTION=""
if [[ "${12}" == "mv" ]]
then
DELETE_OPTION="-DD"
fi

ncftpget -R ${DELETE_OPTION} -T -V -d ${LOG_OUTPUT} -u ${2} -p ${3} -P ${10} ${4} ${TEMP_FILES_FOLDER} ${5}

cat ${LOG_OUTPUT} | grep -A 2 "Cmd: RETR ${5}" >  ${LOG_OUTPUT_FILTER_REF}

cat ${LOG_OUTPUT_FILTER_REF} | grep -v -e "150: Accepted data connection" -e "--" -e "ncftpget ${5}: server said: Can't open ${5}: No such file or directory" > ${LOG_OUTPUT_FILTER}

LOG_OUTPUT="/home/cloudi_log/${1}/log_up"
LOG_OUTPUT_FILTER_REF="/home/cloudi_log/${1}/log_up_filter.ref"
LOG_OUTPUT_FILTER="/home/cloudi_log/${1}/log_up_filter"
name=$(basename "${5}")
TEMP_FILES_FOLDER="/home/cloudi_log/${1}/TEMP/${name}"

ncftpput -R -DD -V -d ${LOG_OUTPUT} -u ${6} -p ${7} -P ${11} ${8} ${9} ${TEMP_FILES_FOLDER}

cat ${LOG_OUTPUT} | grep -A 2 "Cmd: STOR ${name}" >  ${LOG_OUTPUT_FILTER_REF}

cat ${LOG_OUTPUT_FILTER_REF} | grep -v -e "150: Accepted data connection" -e "--" -e "ncftpget ${name}: server said: Can't open ${name}: No such file or directory" > ${LOG_OUTPUT_FILTER}
