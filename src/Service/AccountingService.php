<?php

namespace App\Service;


use App\Entity\IpAccount;
use GuzzleHttp\Client;
use IPTools\IP;
use IPTools\Range;

class AccountingService
{

    /**
     * @var Range
     */
    protected $network_range;

    /**
     * @var IP
     */
    protected $mikrotik_ip;

    /**
     * @var string
     */
    protected $mikrotik_port;

    /**
     * @var string
     */
    protected $mikrotik_proto;

    /**
     * @var IpAccount[]
     */
    protected $data = [];

    protected $body_result;

    protected $influxdb_database;
    protected $http_client;

    /**
     * AccountingService constructor.
     * @param Client $http_client
     * @param string $network_range
     * @param string $ip
     * @param string $port
     * @param string $proto
     */
    public function __construct(Client $http_client, string $network_range, string $ip, string $port = ':80', string $proto = 'http')
    {
        $this->http_client = $http_client;

        $this->mikrotik_ip = IP::parse($ip);

        $this->network_range = Range::parse($network_range);

        if (!in_array($proto, ['http', 'https'])) {
            $this->mikrotik_proto = 'http';
        } else {
            $this->mikrotik_proto = $proto;
        }

        if ($port == false) {
            $this->mikrotik_port = "";
        } else {
            $this->mikrotik_port = $port;
        }
    }

    /**
     * @throws \Exception
     */
    public function fetch()
    {

        $res = $this->http_client->request('GET', $this->mikrotik_proto . '://' . (string)$this->mikrotik_ip . $this->mikrotik_port . '/accounting/ip.cgi');

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

            if ($this->network_range->contains($source)) {
                $ip = $source->__toString();

                if (!isset($this->data[$ip])) {
                    $this->data[$ip] = new IpAccount($ip);
                }

                $this->data[$ip]->add_upload($bytes, $packets);
                $this->data[$ip]->set_dnsname($ip);
            }

            if ($this->network_range->contains($dest)) {
                $ip = $dest->__toString();

                if (!isset($this->data[$ip])) {
                    $this->data[$ip] = new IpAccount($ip);
                }

                $this->data[$ip]->add_download($bytes, $packets);
                $this->data[$ip]->set_dnsname($ip);
            }

        }
    }

    /**
     * @return IpAccount[]
     */
    public function getData()
    {
        return $this->data;
    }

}
