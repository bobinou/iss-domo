#!/bin/sh
# ISS-Domo
###
# Send Freebox Server status to Domoticz
###
#
###
# YOU NEED TO CHANGE VALUE FOR YOUR CONFIGURATION
# ISS-Domo Server
ISSDOMO_SERVER="192.168.0.x:8000"
# Domoticz server
DOMOTICZ_SERVER="192.168.0.x:8080"
#
# Freebox Server Temp SW idx
FREE_SERV_TEMP_SW="29"
#
# Freebox Server Temp CPU B idx
FREE_SERV_TEMP_CPU_B="30"
#
# Freebox Server Temp CPU M idx
FREE_SERV_TEMP_CPU_M="31"
#
# Freebox Server FAN idx
FREE_SERV_FAN="32"
#
# END CONFIGURATION VALUE
###

# Get Freebox Server status
curl --silent http://$ISSDOMO_SERVER/freebox > freebox_server.txt

# Get Temp SW
TEMP_SW=$(cat freebox_server.txt | awk -F"," '{print $6}' | awk -F":" '{print $2}')
echo $TEMP_SW

# Get Temp CPU B
TEMP_CPU_B=$(cat freebox_server.txt | awk -F"," '{print $9}' | awk -F":" '{print $2}')
echo $TEMP_CPU_B

# Get Temp CPU M
TEMP_CPU_M=$(cat freebox_server.txt | awk -F"," '{print $10}' | awk -F":" '{print $2}')
echo $TEMP_CPU_M

# Get Fan
FAN=$(cat freebox_server.txt | awk -F"," '{print $5}' | awk -F":" '{print $2}')
echo $FAN

# Send data to Domoticz
curl --silent -s -i -H  "Accept: application/json"  "http://$DOMOTICZ_SERVER/json.htm?type=command&param=udevice&idx=$FREE_SERV_TEMP_SW&nvalue=0&svalue=$TEMP_SW"
curl --silent -s -i -H  "Accept: application/json"  "http://$DOMOTICZ_SERVER/json.htm?type=command&param=udevice&idx=$FREE_SERV_TEMP_CPU_B&nvalue=0&svalue=$TEMP_CPU_B"
curl --silent -s -i -H  "Accept: application/json"  "http://$DOMOTICZ_SERVER/json.htm?type=command&param=udevice&idx=$FREE_SERV_TEMP_CPU_M&nvalue=0&svalue=$TEMP_CPU_M"
curl --silent -s -i -H  "Accept: application/json"  "http://$DOMOTICZ_SERVER/json.htm?type=command&param=udevice&idx=$FREE_SERV_FAN&nvalue=0&svalue=$FAN"
