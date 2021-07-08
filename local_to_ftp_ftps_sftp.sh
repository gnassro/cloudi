#!/bin/bash

#   Cloudi PFE
#   FTP - FTP tansfert in background
#   Argum : $1  =>  user_id
#           $2  =>  file/directorie src
#           $3  =>  ftp_user_name dest
#           $4  =>  ftp_password dest
#           $5  =>  ftp_server dest
#           $6 =>  ftp_port_dest
#           $7  =>  path dest

#           $8  =>  FTP/FTPS/SFTP dest
#           $9  =>  dir/file


#mkdir -p /home/cloudi_log/${1}/TEMP ; chmod -R 777 /home/cloudi_log/${1}

TEMP_FILES_FOLDER="/home/cloudi_log/${1}/TEMP/"
name=$(basename "${2}")

DIR_OR_FILE="-f"
if [[ "${9}" == "dir" ]]
then
DIR_OR_FILE="-F"
fi

# START UPLOAD


chmod -R 777 /home/cloudi_log/${1}
chown -R daemon:daemon /home/cloudi_log/${1}

if [[ ${8} == "sftp" ]]
then

echo "open sftp://${3}:${4}@${5}:${6}" > /opt/lampp/htdocs/out_err.txt
echo "mirror --verbose --use-pget-n=8 -c --verbose -R ${DIR_OR_FILE} ${TEMP_FILES_FOLDER}${name} -O ${7}/;" >> /opt/lampp/htdocs/out_err.txt
lftp <<SFTP
set ssl:verify-certificate no
set sftp:auto-confirm yes
open sftp://${3}:${4}@${5}:${6}
mirror --verbose --use-pget-n=8 -c --verbose -R ${DIR_OR_FILE} ${TEMP_FILES_FOLDER}${name} -O ${7}/;
bye
SFTP

elif [[ ${8} == "ftp" ]]
then

lftp <<FTP
set ssl:verify-certificate no
open ftp://${3}:${4}@${5}:${6}
mirror --verbose --use-pget-n=8 -c --verbose -R ${DIR_OR_FILE} ${TEMP_FILES_FOLDER}${name} -O ${7}/;
bye
FTP

elif [[ ${8} == "ftps" ]]
then

lftp <<FTP
set ssl:verify-certificate no
open ftp://${3}:${4}@${5}:${6}
mirror --verbose --use-pget-n=8 -c --verbose -R ${DIR_OR_FILE} ${TEMP_FILES_FOLDER}${name} -O ${7}/;
bye
FTP

fi

# END UPLOAD