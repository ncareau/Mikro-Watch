<?php

namespace App\Entity;

class IpAccount
{
    public $ip;
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


}