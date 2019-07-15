<?php

namespace App\Command;

use App\Service\AccountingService;
use InfluxDB\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfluxDBCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('influxdb')
            ->setDescription('Push data to InfluxDB')
            ->setHelp('This command allows you to push data to InfluxDB')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new \GuzzleHttp\Client();

        $influx_client = new Client(getenv('INFLUXDB_HOST'), getenv('INFLUXDB_PORT'), getenv('INFLUXDB_USER'), getenv('INFLUXDB_PASS'));
        $database = $influx_client->selectDB(getenv('INFLXUDB_DATABASE'));

        $accounting_service = new AccountingService($database, $client);

        $accounting_service->fetch();
        $accounting_service->parse();
        $accounting_service->push();

    }

}