<?php

namespace App\Command;

use App\Service\AccountingService;
use InfluxDB\Client;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wrep\Daemonizable\Command\EndlessCommand;

class DaemonCommand extends EndlessCommand
{

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
                    new InputOption('timeout', 't', InputOption::VALUE_OPTIONAL)
                ])
            )
            ->setTimeout(10);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->http_client = new \GuzzleHttp\Client(['verify'=> getenv('MIKROTIK_SSL_VERIFY') == 'true' ? true : false]);

        $influx_client = new Client(getenv('INFLUXDB_HOST'), getenv('INFLUXDB_PORT'), getenv('INFLUXDB_USER'), getenv('INFLUXDB_PASS'));
        $this->influxdb_database = $influx_client->selectDB(getenv('INFLUXDB_DATABASE'));

        if ($input->getOption('timeout')) {
            $this->setTimeout($input->getOption('timeout'));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $accounting_service = new AccountingService($this->influxdb_database, $this->http_client);

        try {
            $accounting_service->fetch();
            $accounting_service->parse();

            $this->throwExceptionOnShutdown();

            $accounting_service->push();
        } catch (\Exception $e) {
            $output->writeln($e->getMessage());

        }
    }

}