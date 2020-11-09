<?php

namespace App\Command;

use App\Service\AccountingService;
use InfluxDB\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class JsonCommand extends Command
{

    protected function configure()
    {
        $this
            ->setName('json')
            ->setDescription('Return data as JSON output.')
            ->setHelp('This command will return the data fetched from the accounting API as a JSON string.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = new \GuzzleHttp\Client(['verify' => getenv('MIKROTIK_SSL_VERIFY') == 'true']);

        $accounting_service = new AccountingService($client, getenv('NETWORK_RANGE'), getenv('MIKROTIK_IP'), getenv('MIKROTIK_PORT'), getenv('MIKROTIK_PROTO'));

        $accounting_service->fetch();
        $accounting_service->parse();

        $output->write(json_encode(array_values($accounting_service->getData())));
    }

}