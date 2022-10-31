<?php

namespace App\Command;

use App\Service\AccountingService;
use App\Service\InfluxDBService;
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
        $client = new \GuzzleHttp\Client(['verify' => getenv('MIKROTIK_SSL_VERIFY') == 'true']);

        $influx_client = new Client(getenv('INFLUXDB_HOST'), getenv('INFLUXDB_PORT'), getenv('INFLUXDB_USER'), getenv('INFLUXDB_PASS'));
        $database = $influx_client->selectDB(getenv('INFLUXDB_DATABASE'));

        $accounting_service = [];
        $ips = explode(',',getenv('MIKROTIK_IP'));

        foreach ($ips as $ip) {
            $accounting_service[] = new AccountingService($this->http_client, getenv('NETWORK_RANGE'), $ip, getenv('MIKROTIK_PORT'), getenv('MIKROTIK_PROTO'));
        }
        $influxdb_service = new InfluxDBService($database);

        foreach ($accounting_service as $processor) {
            try {
                $processor->fetch();
                $processor->parse();
    
                $influxdb_service->push($processor->getData(), $processor->getIP());
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
    
            }
        }
    }

}