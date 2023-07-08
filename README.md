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

# Exemple
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


