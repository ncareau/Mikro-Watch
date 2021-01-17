<?php


use PHPUnit\Framework\TestCase;

class IpAccountingTest extends TestCase
{

    const TEST_IP = '10.0.0.1';

    public function testIpCreateEmpty(): void
    {
        $ipAccount = new \App\Entity\IpAccount(self::TEST_IP);

        $this->assertEquals(0, $ipAccount->down_byte);
        $this->assertEquals(0, $ipAccount->down_packet);
        $this->assertEquals(0, $ipAccount->up_byte);
        $this->assertEquals(0, $ipAccount->up_packet);
    }


    public function testIpAddDownload(): void
    {
        $ipAccount = new \App\Entity\IpAccount(self::TEST_IP);

        $ipAccount->add_download(10,10);

        $this->assertEquals(10, $ipAccount->down_byte);
        $this->assertEquals(10, $ipAccount->down_packet);
        $this->assertEquals(0, $ipAccount->up_byte);
        $this->assertEquals(0, $ipAccount->up_packet);
    }

    public function testIpAddUpload(): void
    {
        $ipAccount = new \App\Entity\IpAccount(self::TEST_IP);

        $ipAccount->add_upload(10,10, false);

        $this->assertEquals(0, $ipAccount->down_byte);
        $this->assertEquals(0, $ipAccount->down_packet);
        $this->assertEquals(10, $ipAccount->up_byte);
        $this->assertEquals(10, $ipAccount->up_packet);
    }

}
