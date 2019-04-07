<?php

namespace App\Command;

use App\Entity\Credit;
use App\Entity\Rate;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ParseDataCommand extends Command
{
    private $container;
    private $client;
    const CONSUMERLOANS_TYPE = 1;
    const MORTGAGES_TYPE = 2;
    const AUTOLOANS_TYPE = 3;


    protected static $defaultName = 'app:parse-data';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->client = new Client(['base_uri' => 'https://hotline.finance']);
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Get data from API and store it to DB');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {

            $output->write('The table `credit` will be cleared. Type \'ok\' to continue:');

            $handle = fopen ("php://stdin","r");
            $line = fgets($handle);
            if(trim($line) != 'ok'){
                $output->write('Aborting');
                exit;
            }
            fclose($handle);

            $deletedRows = $this->clearCreditTable();
            $output->writeln($deletedRows . ' row(s) deleted.');

            $params = [
                'query' => [
                    'page' => 0,
                    'is_promo' => 0,
                    'limit' => 20,
                    'sum' => 10000,
                    'period' => 180,
                    'currency' => 2,
                    'payment' => 0,
                    'orderby' => 'priority',
                    'orderdir' => 'desc',
                    'group' => 0,
                ]
            ];
            $data = $this->getData($this->client, '/api/consumerloans', $params);
            if ($data) {
                $this->setData($data, self::CONSUMERLOANS_TYPE);
            }

            $params = [
                'query' => [
                    'page' => 0,
                    'is_promo' => 0,
                    'limit' => 20,
                    'sum' => 400000,
                    'period' => 720,
                    'currency' => 2,
                    'payment' => 30,
                    'orderby' => 'priority',
                    'orderdir' => 'desc',
                    'group' => 0,
                ]
            ];
            $data = $this->getData($this->client, '/api/mortgages', $params);
            if ($data) {
                $this->setData($data, self::MORTGAGES_TYPE);
            }

            $params = [
                'query' => [
                    'page' => 0,
                    'is_promo' => 0,
                    'limit' => 30,
                    'sum' => 400000,
                    'period' => 720,
                    'currency' => 2,
                    'payment' => 30,
                    'orderby' => 'priority',
                    'orderdir' => 'desc',
                    'group' => 0,
                ]
            ];
            $data = $this->getData($this->client, '/api/autoloans', $params);
            if ($data) {
                $this->setData($data, self::AUTOLOANS_TYPE);
            }

            $output->writeln([
                '=======',
                'Success',
                '=======',
            ]);
        } catch (\Exception $e) {
            $output->writeln([
                '=====',
                'Error',
                '=====',
            ]);
        }
    }

    private function prepareRateData(Array $rateItems) {
        $result = [];
        foreach ($rateItems as $row) {
            $result[$row['credit']] = $row;
        }
        return $result;
    }

    private function getData(Client $client, $uri, $params) {
        $response = $client->get($uri, $params);
        if ($response->getStatusCode() !== 200) {
            return false;
        }
        return json_decode($response->getBody()->getContents(), true);
    }

    private function setData($data, $creditType) {

        $credits = $data['credits']['items'];
        $rates = $this->prepareRateData($data['rates']['items']);

        $entityManager = $this->container->get('doctrine')->getManager();

        foreach ($credits as $row) {
            $credit = new Credit();
            $credit->setTitle($row['title']);
            $credit->setType($creditType);
            $credit->setCreditId($row['id']);
            $credit->setBankId($row['bank']);
            $credit->setPeriod($rates[$row['id']]['period']);
            $credit->setRate($rates[$row['id']]['uah']);

            $entityManager->persist($credit);
        }
        $entityManager->flush();
    }

    private function clearCreditTable() {
        $entityManager = $this->container->get('doctrine')->getManager();
        $rows = $entityManager->createQuery('delete from App\Entity\Credit c where c.id > 0')->execute();
        return $rows;
    }
}