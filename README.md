# Mikro-Watch

 [![Sonarcloud Status](https://sonarcloud.io/api/project_badges/measure?project=ncareau_mikro-watch&metric=alert_status)](https://sonarcloud.io/dashboard?id=ncareau_mikro-watch) ![PHP Composer](https://github.com/ncareau/Mikro-Watch/workflows/PHP%20Composer/badge.svg?branch=master) [![Docker Pulls](https://img.shields.io/docker/pulls/ncareau/mikro-watch)](https://hub.docker.com/r/ncareau/mikro-watch)

PHP process to capture and relay or display the mikrotik Accounting data.

Mikro-Watch will : 

- Fetch the data from the mikrotik router.
- Process each bidirectional transfer to get metrics for each internal IP (within specified range)
- Push or display data. 

Currently push to inflxudb directly, but plans are to output data for telegraf ingestion or display in a simple webpage.

![Grafana Example](https://github.com/ncareau/mikro-watch/raw/master/demo/panel.PNG)

## Requirements
    
- PHP 5.6 or later
- Composer


- or Docker

## Configuration and running

First, make sure your router is configured to accept request to the account page by going to `IP -> Accounting -> Web Access`

In `Address`, enter a single/range of authorized IP that can access the accounting page. Ex ( `192.168.0.50/32` for single ip, `192.168.0.0/24` for the whole /24 subnet)

### Docker

```bash
docker run -d --name mikro-watch \
    -e MIKROTIK_IP=10.0.0.1 \
    -e NETWORK_RANGE=10.0.0.1-10.0.0.255 \
    -e INFLUXDB_HOST=10.0.0.1 \
    -e INFLUXDB_USER=user \
    -e INFLUXDB_PASS=***pass*** \
    -e INFLUXDB_DATABASE=influxdb \
    -e DNS_REVERSE_LOOKUP=false \
    -e DNS_REVERSE_LOOKUP_TRIM_SUFFIX=false \
    ncareau/mikro-watch
```

### Docker compose

```yaml
version: '3'
services:

    mikro-watch:
        image: ncareau/mikro-watch
        container_name: mikro-watch
        environment:
            - MIKROTIK_IP=10.0.0.1
            - NETWORK_RANGE=10.0.0.1-10.0.0.255
            - INFLUXDB_HOST=10.0.0.1
            - INFLUXDB_USER=user
            - INFLUXDB_PASS=***pass***
            - INFLUXDB_DATABASE=influxdb
            - DNS_REVERSE_LOOKUP=false
            - DNS_REVERSE_LOOKUP_TRIM_SUFFIX=false
        restart : unless-stopped 
```
 
### Manual

Copy the `.env` file to `.env.local` and change the information with your current network.

- `php mikro-watch influxdb` will push once to influxdb
- `php mikro-watch daemon` will start a daemon pushing to influxdb each 10 sec. 
  - `--timeout 5` or `-t 5` to change the timeout between calls in seconds. 
- `php mikro-watch json` will output the accounting result as json.  

Instructions to install this application as a systemd service are located in the `mikrowatch.service` file.

### Environment Variables

| Variable | Description |
| --- | --- |
| `MIKROTIK_IP` | IP of your mikrotik router |
| `MIKROTIK_PROTO` | Default: `https` |
| `MIKROTIK_SSL_VERIFY` | if using `https`, verify for valid ssl certificate |
| `MIKROTIK_PORT` | Use if your Mikrotik listens on a port other than 80 for http or 443. Must include `:` For example `:8081` |
| `NETWORK_RANGE` | Range of your network to filter ips. |
| `INFLUXDB_HOST` | Influxdb host |
| `INFLUXDB_USER` | Influxdb username |
| `INFLUXDB_PASS` | Influxdb password |
| `INFLUXDB_DATABASE` | Influxdb database |
| `DNS_REVERSE_LOOKUP` | Enable reverse lookup |
| `DNS_REVERSE_LOOKUP_TRIM_SUFFIX` | Remove domain suffix from domain name (hostname only) |


## Note & Troubleshooting

When FastTrack is enabled, some packet will not be accounted for. To disable this (This will result in more CPU usage on your router) disable the fasttrack rule in `IP -> Firewall -> Filter Rules`. 

Calling the accounting webpage resets the counter. This means that if multiple processes or users call the mikrotik api, only a subset of the data will be captured.
