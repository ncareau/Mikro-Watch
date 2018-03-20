<?php

namespace App\Entity;

class IpAccount
{
    public $ip;
    public $down_byte;
    public $down_packet;
    public $up_byte;
    public $up_packet;

    /**
     * IpAccount constructor.
     * @param $ip
     */
    public function __construct($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @param int $bytes
     * @param int $packets
     */
    public function add_upload($bytes, $packets)
    {
        $this->up_packet = $this->up_packet + $packets;
        $this->up_byte = $this->up_byte + $bytes;
    }

    /**
     * @param int $bytes
     * @param int $packets
     */
    public function add_download($bytes, $packets)
    {
        $this->down_packet = $this->down_packet + $packets;
        $this->down_byte = $this->down_byte + $bytes;
    }


}