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
#           $12  =>  COPY/MOVE
#           $13  =>  FTP/FTPS/SFTP source
#           $14  =>  FTP/FTPS/SFTP dest
#           $15  =>  dir/file


#mkdir -p /home/cloudi_log/${1}/TEMP ; chmod -R 777 /home/cloudi_log/${1}

TEMP_FILES_FOLDER="/home/cloudi_log/${1}/TEMP/"
name=$(basename "${5}")

DELETE_OPTION=""
ADD_SLASH=""
DIR_OR_FILE="-f"

if [[ "${12}" == "mv" ]]
then
if [[ "${15}" == "dir" ]]
then
DELETE_OPTION="--Remove-source-dirs"
ADD_SLASH="/"
else
DELETE_OPTION="--Remove-source-files"
fi
fi

if [[ "${15}" == "dir" ]]
then
DIR_OR_FILE="-F"
fi

# START DOWNLOAD

if [[ ${13} == "sftp" ]]
then
lftp <<SFTP
set ssl:verify-certificate no
set sftp:auto-confirm yes
open sftp://${2}:${3}@${4}:${10}
mirror --verbose --use-pget-n=8 -c --verbose ${DELETE_OPTION} ${DIR_OR_FILE} ${5}${ADD_SLASH} -O ${TEMP_FILES_FOLDER};
bye
SFTP

elif [[ ${13} == "ftp" ]]
then

lftp <<FTP
set ssl:verify-certificate no
open ftp://${2}:${3}@${4}:${10}
mirror --verbose --use-pget-n=8 -c --verbose ${DELETE_OPTION} ${DIR_OR_FILE} ${5}${ADD_SLASH} -O ${TEMP_FILES_FOLDER};
bye
FTP

elif [[ ${13} == "ftps" ]]
then

lftp <<FTP
set ssl:verify-certificate no
open ftp://${2}:${3}@${4}:${10}
mirror --verbose --use-pget-n=8 -c --verbose ${DELETE_OPTION} ${DIR_OR_FILE} ${5}${ADD_SLASH} -O ${TEMP_FILES_FOLDER};
bye
FTP

fi

# END DONWLOAD

# START UPLOAD

if [[ ${14} == "sftp" ]]
then
lftp <<SFTP
set ssl:verify-certificate no
set sftp:auto-confirm yes
open sftp://${6}:${7}@${8}:${11}
mirror --verbose --use-pget-n=8 -c --verbose -R ${DELETE_OPTION} ${DIR_OR_FILE} ${TEMP_FILES_FOLDER}${name} -O ${9}/;
bye
SFTP

elif [[ ${14} == "ftp" ]]
then

lftp <<FTP
set ssl:verify-certificate no
open ftp://${6}:${7}@${8}:${11}
mirror --verbose --use-pget-n=8 -c --verbose -R ${DELETE_OPTION} ${DIR_OR_FILE} ${TEMP_FILES_FOLDER}${name} -O ${9}/;
bye
FTP

elif [[ ${14} == "ftps" ]]
then

lftp <<FTP
set ssl:verify-certificate no
open ftp://${6}:${7}@${8}:${11}
mirror --verbose --use-pget-n=8 -c --verbose -R ${DELETE_OPTION} ${DIR_OR_FILE} ${TEMP_FILES_FOLDER}${name} -O ${9}/;
bye
FTP

fi

# END UPLOAD