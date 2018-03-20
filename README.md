# Mikro-Watch

PHP process to capture and relay or display the mikrotik Accounting data.

Mikrotik-Watch will : 

- Fetch the data from the mikrotik router.
- Process each bidirectional transfer to get metrics for each internal IP (Within specified range)
- Push or display data. 

Currently push to inflxudb directly, but plans are to output data for telegraf ingestion or display in a simple webpage.

![Grafana Example](https://github.com/ncareau/mikro-watch/raw/master/demo/panel.PNG)

## Running

- `php mikro-watch influxdb` will push one time to influxdb
- `php mikro-watch daemon` will start a daemon pushing to influxdb each 10 sec. 

## Note

Enabling FastTrack means that some packet will not be accounted for. To disable this (This will result in more CPU usage on your router) disable the fasttrack rule in `IP -> Firewall -> Filter Rules`. 

Calling the accounting webpage resets the data. This means that if multiple process or user call the webpage, only a subset of the data will be captured.