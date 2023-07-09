# mina-node-uptime-moniroring-script
> Simple shell script to monitor your node uptime

# Description
This script, once launched in the background on your mina node server will parse the Mina logs at regular intervals to get uptime ticks.  
Each loop will add a line to a simple `csv` file with `timestamp` for the tick and 24 hours splipping node `uptime_value` (from 0 (0% uptime) to 96 (100% uptime)). 
The script handles the ftp upload of this file to a distant web server that can handle the graphical display of the node uptime results.

# Usage
```
./uptime.sh [StarDate] [Interval] [Reinit] [Debug] [ftp_server] [ftp_login] [ftp_pass]
```

| Param | Description | 
| ----- | ----------- |
|`StartDate`    | Date to get transaction from (YYYY-MM-DDTHH:MM:SS) (default 2022-05-01T00:00:00) |
|`Interval`     | Repeat each Interval |
|`Reinit`       | (0/1) Reinit Statistics from `StartDate` |
|`Debug`        | (0/1) Enable Debug Mode |
|`ftp_server`   | (ftp://server.com) FTP server adress to upload uptime csv file |
|`ftp login`    | FTP login |
|`ftp passowrd` | FTP password |

<br/>
 
# Example
## Init Uptime CSV file from 01/01/2023 with 6 hour step
```
./uptime.sh "2023-01-01T00:00:00" 21600 1 0 "" "" ""
```

The generate filename is based on `uname -n`

Example
`mycomputer.csv`

The content of the file will consist of a `csv` file with `date`,`value` for each line.

Example
```
Time,Score
2023-05-01 00:00:00,91
2023-05-02 00:00:00,96
2023-05-03 00:00:00,96
2023-05-04 00:00:00,92
```

> In the exemple file, we can see the script parse the logs every 24h to get the 24h splipping ticks every 24h.
> On May, 1st, 91 uptime ticks have been successfully sent to the uptime server. It means that 5 ticks have been missed.
> On May, 2d, 96 uptime ticks have been successfully sent to the uptime server. Mina server uptime for this date will be 100%

# How it works
The way the script work is really simple.
It uses filtered journalctl parsing to get the numbers of ticks for the last 24h every `Interval` seconds and then append the result to the `csv` file.

```
d=$(date '+%F %H:%M:%S')
uptime_csv_content="$d,$(journalctl --user -u mina -S "24 hours ago"  | grep "Sent block with state" | wc -l)"
uptime_csv="./${filename}.csv"
...
echo $uptime_csv_content >> "$uptime_csv"
```

# Sending updated `csv` file to a web server
Specifying `ftp_server`, `ftp login`, `ftp passowrd` in the command line will send the updated `csv` file each `Interval` to an ftp server (in a way the file can be used by a simple web page to display the graph result). 

Example : Send updated uptime file to ftp://webserver.com every 2 hours (7200 seconds)  
```
./uptime.sh "2023-01-01T00:00:00" 7200 0 0 "ftp://webserver.com" "user" "pass"
```
# Display the results
You can just rely on the `CSV` file to check your uptime.
You can use the file locally or on a remote machine to handle graphical representation of your uptime.  
Source for a simple web page project displaying graph based on `CSV` file is avalable here :

https://github.com/naamahdaemon/naamahdaemon.github.io

And an example of rendering here : https://naamahdaemon.github.io/



