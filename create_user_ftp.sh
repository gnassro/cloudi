#!/bin/bash

#   Cloudi PFE
#   CREATE LOCAL SFTP ACCOUNT FOR LOCAL DRIVE
#   Argum : $1  =>  ftp_username
##          $2  =>  ftp_password

mkdir /home/${1}
useradd -d /home/${1} -M -N -g sftp ${1}
chown root:root /home/${1}
chmod 755 /home/${1}

passwd ${1} <<EOF
${2}
${2}
EOF

mkdir /home/${1}/htdoc
chown ${1}:sftp /home/${1}/htdoc
chmod ug+rwX /home/${1}/htdoc
