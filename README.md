# mina-node-uptime-moniroring-script
> Simple shell script to monitor your node uptime

# Description
This script, once launched in the background on your mina node server will parse the Mina logs at regular intervals to get uptime ticks.
Each loop will add a line to a simple `csv` file with `timestamp` for the tick and 24 hours splipping node `uptime_value` (from 0 (0% uptime) to 96 (100% uptime)).
The script handles the ftp upload of this file to a distant web server that can handle the graphical display of the node uptime results.

