<?php

namespace App\Entity;

class IpAccount
{
    public $ip;
    public $dnsname = "";
    public $down_byte = 0;
    public $down_packet = 0;
    public $up_byte = 0;
    public $up_packet = 0;

    /**
     * IpAccount constructor.
     * @param string $ip
     */
    public function __construct(string $ip)
    {
        $this->ip = $ip;
    }

    /**
     * @param int $bytes
     * @param int $packets
     */
    public function add_upload(int $bytes, int $packets)
    {
        $this->up_packet = $this->up_packet + $packets;
        $this->up_byte = $this->up_byte + $bytes;
    }

    /**
     * @param int $bytes
     * @param int $packets
     */
    public function add_download(int $bytes, int $packets)
    {
        $this->down_packet = $this->down_packet + $packets;
        $this->down_byte = $this->down_byte + $bytes;
    }

    /**
     * @param string $ip
     */
    public function set_dnsname(string $ip)
    {
        if (getenv('DNS_REVERSE_LOOKUP') == 'true') {
            # Reverse lookup. If no name is returned from DNS, getHostByAddr returns the IP
            $dnsname = getHostByAddr($ip);

            # Trim domain names only, not IP addresses if DNS_REVERSE_LOOKUP_TRIM_SUFFIX is true
            if (!preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $dnsname)
                && getenv('DNS_REVERSE_LOOKUP_TRIM_SUFFIX') == 'true'
            ) {
                $dnsname = preg_replace("/\..*$/", '', $dnsname);
            }

            $this->dnsname = $dnsname;
        }
    }


}
