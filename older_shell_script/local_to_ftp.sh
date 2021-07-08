#!/bin/bash

#   Cloudi PFE
#   LOCAL - FTP tansfert in background
#   Argum : $1  =>  user_id
#           $2  =>  file/directorie src
#           $3  =>  ftp_user_name dest
#           $4  =>  ftp_password dest
#           $5  =>  ftp_server dest
#           $6 =>  ftp_port_dest
#           $7  =>  path dest

LOG_OUTPUT="/home/cloudi_log/${1}/log_up"
LOG_OUTPUT_FILTER_REF="/home/cloudi_log/${1}/log_up_filter.ref"
LOG_OUTPUT_FILTER="/home/cloudi_log/${1}/log_up_filter"
TEMP_FILES_FOLDER="/home/cloudi_log/${1}/TEMP/${2}"

echo "server"
echo ${5}

echo "username"
echo ${3}

ncftpput -R -DD -V -d ${LOG_OUTPUT} -u ${3} -p ${4} -P ${6} ${5} ${7} ${TEMP_FILES_FOLDER}



cat ${LOG_OUTPUT} | grep -A 2 "Cmd: STOR ${2}" >  ${LOG_OUTPUT_FILTER_REF}

cat ${LOG_OUTPUT_FILTER_REF} | grep -v -e "150: Accepted data connection" -e "--" -e "ncftpget ${2}: server said: Can't open ${2}: No such file or directory" > ${LOG_OUTPUT_FILTER}
