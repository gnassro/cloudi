#!/bin/bash

#   Cloudi PFE
#   FTP - FTP tansfert in background
#   Argum : $1  =>  user_id
#           $2  =>  ftp_user_name src
#           $3  =>  ftp_password src
#           $4  =>  ftp_server src
#           $6 =>  ftp_port src
#           $5  =>  file/directorie src
#
#           $7  =>  COPY/MOVE
#           $8  =>  FTP/FTPS/SFTP source
#           $9  =>  dir/file


#mkdir -p /home/cloudi_log/${1}/TEMP ; chmod -R 777 /home/cloudi_log/${1}

TEMP_FILES_FOLDER="/home/cloudi_log/${1}/TEMP/"
name=$(basename "${5}")

DELETE_OPTION=""
ADD_SLASH=""
DIR_OR_FILE="-f"

if [[ "${7}" == "mv" ]]
then
if [[ "${9}" == "dir" ]]
then
DELETE_OPTION="--Remove-source-dirs"
ADD_SLASH="/"
else
DELETE_OPTION="--Remove-source-files"
fi
fi

if [[ "${9}" == "dir" ]]
then
DIR_OR_FILE="-F"
fi

# START DOWNLOAD

if [[ ${8} == "sftp" ]]
then

lftp <<SFTP
set ssl:verify-certificate no
set sftp:auto-confirm yes
open sftp://${2}:${3}@${4}:${6}
mirror --verbose --use-pget-n=8 -c --verbose ${DELETE_OPTION} ${DIR_OR_FILE} ${5}${ADD_SLASH} -O ${TEMP_FILES_FOLDER};
bye
SFTP

elif [[ ${8} == "ftp" ]]
then

lftp <<FTP
set ssl:verify-certificate no
open ftp://${2}:${3}@${4}:${6}
mirror --verbose --use-pget-n=8 -c --verbose ${DELETE_OPTION} ${DIR_OR_FILE} ${5}${ADD_SLASH} -O ${TEMP_FILES_FOLDER};
bye
FTP

elif [[ ${8} == "ftps" ]]
then

lftp <<FTPS
set ssl:verify-certificate no
open ftp://${2}:${3}@${4}:${6}
mirror --verbose --use-pget-n=8 -c --verbose ${DELETE_OPTION} ${DIR_OR_FILE} ${5}${ADD_SLASH} -O ${TEMP_FILES_FOLDER};
bye
FTPS

fi

chmod -R 777 /home/cloudi_log/${1}
chown -R daemon:daemon /home/cloudi_log/${1}

# END DONWLOAD