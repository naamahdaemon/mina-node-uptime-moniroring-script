#!/bin/bash

if [[ ( $@ == "--help") ||  $@ == "-h" ]]
then
        echo "Usage: $0 [StarDate] [Interval] [Reinit] [Debug] [ftp_server] [ftp_login] [ftp_pass]"
        printf "\n"
        echo "StartDate    : Date to get transaction from (YYYY-MM-DDTHH:MM:SS) (default 2022-05-01T00:00:00)"
        echo "Interval     : Repeat each Interval"
        echo "Reinit       : (0/1) Reinit Statistics from <startDate>"
        echo "Debug        : (0/1) Enable Debug Mode"
        echo "ftp server   : (ftp://server.com) FTP server adress to upload uptime file"
        echo "ftp login    : FTP login"
        echo "ftp passowrd : FTP password"
        exit 0
fi

startdate=$1
interval=$2
reinit=$3
debug=$4
ftpserver=$5
ftplogin=$6
ftppass=$7

filename="$(uname -n)"

if [ -z "$debug" ]; then
        debug="0"
fi

if [ -z "$reinit" ]; then
        reinit="0"
fi

if [ -z "$interval" ]; then
        interval="900"
fi

if [ "$debug" != "0" ]; then
        echo "**** ENTERING DEBUG MODE ****"
else
        echo "**** ENTERING NORMAL MODE ****"
fi

if [ -z "$startdate" ]; then
        startdate="2023-04-01 00:00:00"
fi

if [ -z "$ftpserver" ]; then
        ftpserver=""
        ftplogin=""
        ftppassword=""
fi

if [ "$debug" != "0" ]; then
        echo "startdate : $startdate"
        echo "interval  : $interval"
        echo "reinit    : $reinit"
        echo "debug     : $debug"
fi

while true
do
        d=$(date '+%F %H:%M:%S')
        uptime_csv_content="$d,$(journalctl --user -u mina -S "24 hours ago"  | grep "Sent block with state" | wc -l)"

        if [ "$debug" != "0" ]; then
                echo $uptime_csv_content
        fi

        uptime_csv="./${filename}.csv"

        ftpuptime="curl -s -T \"${uptime_csv}\" {$ftpserver} --user {$ftpuser}:{$ftppass}"

        if [ "$debug" != "0" ]; then
                echo $ftpuptime
        fi

        if [ ! -f "$uptime_csv" ] || [ "$reinit" == "1" ]; then
                if [ "$debug" != "0" ]; then
                        echo "$uptime_csv does not exist. Create and Init File from $startdate"
                fi
                reinit="0"
                echo "Time,Score" > "$uptime_csv"

                # fill up file with uptime history
                # get startdate
                dt_sdt=$(date -d "$startdate" "+%s")
                dt_comp=$(($dt_sdt+$interval-86400))
                dt_comp2=$(($dt_comp+86400))
                # get current date
                dt_cur=$(date "+%s")
                # loop by one day until date is dt_cur
                while [ "$dt_comp2" -lt "$dt_cur" ];
                do
                        d_comp=$(date -d "@${dt_comp}" "+%Y-%m-%d %H:%M:%S")
                        d_comp2=$(date -d "@${dt_comp2}" "+%Y-%m-%d %H:%M:%S")

                        epoch=$(date +%s)

                        if [ "$debug" != "0" ]; then
                                echo "$d_comp,journalctl --user -u mina -S \"${d_comp}\" --until \"${d_comp2}\" | grep \"Sent block with state\" | wc -l"
                        fi

                        uptime_csv_content="$d_comp,$(journalctl --user -u mina -S "${d_comp}" --until "${d_comp2}" | grep "Sent block with state" | wc -l)"


                        if [ "$debug" != "0" ]; then
                                echo $uptime_csv_content
                        fi

                        d2=$(date -d "@${dt_comp}" "+%F %H:%M:%S")

                        echo $uptime_csv_content >> "$uptime_csv"

                        dt_comp=$(($dt_comp + $interval))
                        dt_comp2=$(($dt_comp+86400))

                        if [ "$debug" != "0" ]; then
                                echo -ne "."
                        fi
                done
                if [ "$debug" != "0" ]; then
                        echo -ne "\\r\\n"
                fi
        fi

        echo $uptime_csv_content >> "$uptime_csv"

        if [ "$ftpserver" != "" ]; then
          eval "$ftpuptime"
        fi

        sleep $interval
done
