# Mikro-Watch

PHP process to capture and relay or display the mikrotik Accounting data.

Mikro-Watch will : 

- Fetch the data from the mikrotik router.
- Process each bidirectional transfer to get metrics for each internal IP (Within specified range)
- Push or display data. 

Currently push to inflxudb directly, but plans are to output data for telegraf ingestion or display in a simple webpage.

![Grafana Example](https://github.com/ncareau/mikro-watch/raw/master/demo/panel.PNG)

## Requirements

PHP 5.6 minimum

## Configuration and running

First, copy the `.env.dist` file to `.env` and fill the information with your current network.

Next, make sure your router is configured to accept request to the account page by going to `IP -> Accounting -> Web Access`

- `php mikro-watch influxdb` will push one time to influxdb
- `php mikro-watch daemon` will start a daemon pushing to influxdb each 10 sec. 
  - `--timeout 5` or `-t 5` to change the timeout between calls in seconds. 

Instructions to install this application as a systemd service are located in the `mikrowatch.service` file.

## Note

Enabling FastTrack means that some packet will not be accounted for. To disable this (This will result in more CPU usage on your router) disable the fasttrack rule in `IP -> Firewall -> Filter Rules`. 

Calling the accounting webpage resets the data. This means that if multiple process or user call the webpage, only a subset of the data will be captured.
