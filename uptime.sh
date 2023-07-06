#!/bin/bash

if [[ ( $@ == "--help") ||  $@ == "-h" ]]
then
        echo "Usage: $0 [StarDate] [Interval] [Reinit] [Debug}"
        printf "\n"
        echo "StartDate    : Date to get transaction from (YYYY-MM-DDTHH:MM:SS) (default 2022-05-01T00:00:00)"
        echo "Interval     : Repeat each Interval"
        echo "Reinit       : (0/1) Reinit Statistics from <startDate>"
        echo "Debug        : (0/1) Enable Debug Mode"
        exit 0
fi

startdate=$1
interval=$2
reinit=$3
debug=$4

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
        # Now filling number of transactions CSV file
        #echo $d

        uptime_csv="./{replace_with_your_own_filename}.csv"

        ftpuptime="curl -s -T \"${uptime_csv}\" ftp://{put_your_ftp_server_address_here} --user {ftp_user}:{ftp_password}"

        #echo $ftpstring

        if [ ! -f "$uptime_csv" ] || [ "$reinit" == "1" ]; then
                if [ "$debug" != "0" ]; then
                        echo "$uptime_csv does not exist. Create and Init File from $startdate"
                fi
                reinit="0"
                echo "Time,Score" > "$uptime_csv"

                # fill up file with transaction history
                # get startdate
                dt_sdt=$(date -d "$startdate" "+%s")
                dt_comp=$(($dt_sdt+$interval-86400))
                dt_comp2=$(($dt_comp+86400))
                # get current date
                dt_cur=$(date "+%s")
                # loop by one day until date is dt_cur
                while [ "$dt_comp2" -lt "$dt_cur" ];
                do
                        #echo $dt_comp
                        #echo $dt_cur
                        d_comp=$(date -d "@${dt_comp}" "+%Y-%m-%d %H:%M:%S")
                        d_comp2=$(date -d "@${dt_comp2}" "+%Y-%m-%d %H:%M:%S")
                        #echo "$date $dt_comp"
                        #echo "$d_comp"
                        # Call API
                        epoch=$(date +%s)

                        if [ "$debug" != "0" ]; then
                                echo "$d_comp,journalctl --user -u mina -S \"${d_comp}\" --until \"${d_comp2}\" | grep \"Sent block with state\" | wc -l"
                        fi

                        uptime_csv_content="$d_comp,$(journalctl --user -u mina -S "${d_comp}" --until "${d_comp2}" | grep "Sent block with state" | wc -l)"


                        if [ "$debug" != "0" ]; then
                                echo $uptime_csv_content
                        fi
                        #echo $available2
                        # Now Add this to file
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

        eval "$ftpuptime"
        #eval "$ftpsp"
        #eval "$ftpsns"
        #eval "$ftpsnp"

        sleep $interval
        #echo "after sleep"
done
