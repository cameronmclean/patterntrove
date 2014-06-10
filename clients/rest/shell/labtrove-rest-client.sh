#!/bin/bash
# LabTrove REST API test client
# J.S.Robinson@soton.ac.uk

LABTROVE_URI="###################"
MASTER_UID="#####################"
USERNAME="#######################"
CURL_COMMAND="/usr/bin/curl"
CURL_OPTS="-v"
DATESTAMP=$(date -R)
XDEBUG_STRING=""

function usage()
{
        echo -e "\nUsage:\tlabtrove-rest-client.sh OPERATION [AGRUMENTS]\n"
	echo -e "Available Operations:\n"
        echo -e "\t addpost XML_INPUT_FILE\n"
        echo -e "\t appendpost POST_ID [DATA_ID] XML_INPUT_FILE\n"
        echo -e "\t adddata XML_METADATA PAYLOAD_DATA_FILE\n"
        echo -e "See: http://dev.labtrove.org/w/index.php/Using_the_Rest_API"
}

if [ ! -f $CURL_COMMAND ]; then 
        echo "This script requires '/usr/bin/curl' to be available"
        exit 1
fi

if [ $# -lt 2  ]; then 
	usage
	exit 1
fi

if [ "$1" = "addpost" ]; then
	sed -e "s/%DATESTAMP%/${DATESTAMP}/g" -e "s/%USERNAME%/${USERNAME}/g"  $2 > /tmp/addfile 
	$CURL_COMMAND $CURL_OPTS --data-urlencode request@/tmp/addfile $LABTROVE_URI/api/rest/addpost/uid/${MASTER_UID}${XDEBUG_STRING}

elif [ "$1" = "appendpost" ]; then
	if [ $# -eq 4 ]; then
		#Supplied a DATA_ID from a previous upload
		sed -e "s/%DATESTAMP%/${DATESTAMP}/g" -e "s/%USERNAME%/${USERNAME}/g" -e "s/%POST_ID%/${2}/" -e "s/%DATA_ID%/${3}/"  $4 > /tmp/appendfile
	else
		sed -e "s/%DATESTAMP%/${DATESTAMP}/g" -e "s/%USERNAME%/${USERNAME}/g" -e "s/%POST_ID%/${2}/" $3 > /tmp/appendfile
	fi
	$CURL_COMMAND $CURL_OPTS --data-urlencode request@/tmp/appendfile $LABTROVE_URI/api/rest/appendpost/uid/${MASTER_UID}${XDEBUG_STRING}

elif [ "$1" = "adddata" ]; then
	# Constructs a request with content-type: multipart/form-data
	# two variables, 'request' - the xml describing the data item, file_0 - the file to be uploaded
	sed -e "s/%FILENAME%/$3/" $2 > /tmp/adddatafile
	$CURL_COMMAND $CURL_OPTS --form "file_0=@$3" --form "request=</tmp/adddatafile" $LABTROVE_URI/api/rest/adddata/uid/${MASTER_UID}${XDEBUG_STRING} 
else 
	usage
	exit 1
fi

function usage()
{
	echo "Usage:\tlabtrove-rest-client.sh OPERATION [AGRUMENTS]\n\n Available Operations:\n"
	echo "\t addpost XML_INPUT_FILE\n"
	echo "\t appendpost [POST_ID] XML_INPUT_FILE\n"
	echo "\t adddata \n"
	echo "See: http://dev.labtrove.org/w/index.php/Using_the_Rest_API"
}
