<?php

namespace App\Command;

use App\Service\AccountingService;
use App\Service\InfluxDBService;
use InfluxDB\Client;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wrep\Daemonizable\Command\EndlessCommand;

class DaemonCommand extends EndlessCommand
{

    const INPUT_OPTION_TIMEOUT = 'timeout';

    protected $influxdb_database;
    protected $http_client;

    protected function configure()
    {
        $this
            ->setName('daemon')
            ->setDescription('Push data to InfluxDB endlessly')
            ->setHelp('This command allows you to push data periodically to InfluxDB as an endless process')
            ->setDefinition(
                new InputDefinition([
                    new InputOption(self::INPUT_OPTION_TIMEOUT, 't', InputOption::VALUE_OPTIONAL)
                ])
            )
            ->setTimeout(10);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->http_client = new \GuzzleHttp\Client(['verify' => getenv('MIKROTIK_SSL_VERIFY') == 'true']);

        $influx_client = new Client(getenv('INFLUXDB_HOST'), getenv('INFLUXDB_PORT'), getenv('INFLUXDB_USER'), getenv('INFLUXDB_PASS'));
        $this->influxdb_database = $influx_client->selectDB(getenv('INFLUXDB_DATABASE'));

        if ($input->getOption(self::INPUT_OPTION_TIMEOUT)) {
            $this->setTimeout($input->getOption(self::INPUT_OPTION_TIMEOUT));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accounting_service = [];
        $ips = explode(',',getenv('MIKROTIK_IP'));

        foreach ($ips as $ip){
            $accounting_service[] = new AccountingService($this->http_client, getenv('NETWORK_RANGE'), $ip, getenv('MIKROTIK_PORT'), getenv('MIKROTIK_PROTO'));
        }
        
        $influxdb_service = new InfluxDBService($this->influxdb_database);

        foreach ($accounting_service as $processor){
            try {
                $processor->fetch();
                $processor->parse();
    
                $this->throwExceptionOnShutdown();
    
                $influxdb_service->push($processor->getData());
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
    
            }
        }
        
    }

}