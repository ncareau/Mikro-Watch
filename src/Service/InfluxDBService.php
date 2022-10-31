<?php


namespace App\Service;


use App\Entity\IpAccount;
use IPTools\IP;
use InfluxDB\Database;
use InfluxDB\Point;

class InfluxDBService
{

    protected $influxdb_database;

    /**
     * AccountingService constructor.
     * @param Database $influxdb_database
     */
    public function __construct(Database $influxdb_database)
    {
        $this->influxdb_database = $influxdb_database;
    }

    /**
     * @param $data IpAccount[]
     * @param $host IP
     * @throws Database\Exception
     * @throws \InfluxDB\Exception
     */
    public function push($data, $host)
    {
        $points = [];

        foreach ($data as $d) {

            $counters = [];

            if($d->down_packet > 0){
                $counters["down_packet"] = (int)$d->down_packet;
                $counters["down_byte"] = (int)$d->down_byte;
            }

            if($d->up_packet > 0){
                $counters["up_packet"] = (int)$d->up_packet;
                $counters["up_byte"] = (int)$d->up_byte;
            }

            $tags = ["ip" => $d->ip, "host" => $host->__tostring()];

            if ($d->dnsname) {
                $tags["dnsname"] = $d->dnsname;
            }

            $points[] = new Point(
                'net_traffic',
                null,
                $tags,
                $counters
            );

        }

        $this->influxdb_database->writePoints($points, Database::PRECISION_MILLISECONDS);
    }
}
