<?php

namespace App\Service;


use App\Entity\IpAccount;
use GuzzleHttp\Client;
use InfluxDB\Database;
use InfluxDB\Point;
use IPTools\IP;
use IPTools\Range;

class AccountingService
{

    /**
     * @var IpAccount[]
     */
    public $data = [];

    protected $body_result;

    protected $influxdb_database;
    protected $http_client;

    /**
     * AccountingService constructor.
     * @param Database $influxdb_database
     * @param Client $http_client
     */
    public function __construct(Database $influxdb_database, Client $http_client)
    {
        $this->influxdb_database = $influxdb_database;
        $this->http_client = $http_client;
    }

    /**
     * @throws \Exception
     */
    public function fetch()
    {
        $res = $this->http_client->request('GET', 'http://' . getenv('MIKROTIK_IP') . '/accounting/ip.cgi');

        $this->body_result = $res->getBody();

        //Only continue if the status is OK 200 and there is data
        if ($res->getStatusCode() != 200 || empty($res->getBody())) {
            throw new \Exception($res->getReasonPhrase());
        }
    }

    /**
     * @throws \Exception
     */
    public function parse()
    {
        $lines = explode("\n", $this->body_result);

        foreach ($lines as $line) {

            if (empty($line)) {
                break;
            }

            $line = explode(" ", $line);

            $source = new IP($line[0]);
            $dest = new IP($line[1]);
            $bytes = $line[2];//byte
            $packets = $line[3];//packet

            if (Range::parse(getenv('NETWORK_RANGE'))->contains($source)) {
                $ip = $source->__toString();

                if (!isset($this->data[$ip])) {
                    $this->data[$ip] = new IpAccount($ip);
                }

                $this->data[$ip]->add_upload($bytes, $packets);
            }

            if (Range::parse(getenv('NETWORK_RANGE'))->contains($dest)) {
                $ip = $dest->__toString();

                if (!isset($this->data[$ip])) {
                    $this->data[$ip] = new IpAccount($ip);
                }

                $this->data[$ip]->add_download($bytes, $packets);
            }

        }
    }

    /**
     * @throws Database\Exception
     * @throws \InfluxDB\Exception
     */
    public function push()
    {
        $points = [];

        foreach ($this->data as $d) {

            $counters = [];

            if($d->down_packet > 0){
                $counters["down_packet"] = (int)$d->down_packet;
                $counter["down_byte"] = (int)$d->down_byte;
            }

            if($d->up_packet > 0){
                $counters["up_packet"] = (int)$d->up_packet;
                $counter["up_byte"] = (int)$d->up_byte;
            }

            $points[] = new Point(
                'net_traffic',
                null,
                ["ip" => $d->ip, "host" => getenv('MIKROTIK_IP')],
                $counters
            );

        }

        $this->influxdb_database->writePoints($points, Database::PRECISION_MILLISECONDS);
    }

    public function show()
    {

    }

}