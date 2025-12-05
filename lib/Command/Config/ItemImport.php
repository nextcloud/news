<?php

namespace OCA\News\Command\Config;

use OCA\News\Service\ImportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ItemImport extends Command
{
    /**
     * @var ImportService service for the data.
     */
    protected $importService;

    public function __construct(ImportService $importService)
    {
        parent::__construct(null);

        $this->importService = $importService;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:item:import')
            ->setDescription('Import Items from json data')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to import data for')
            ->addArgument('file', InputArgument::REQUIRED, 'Data to import');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $input->getArgument('user-id');
        $data = file_get_contents($input->getArgument('file'));
        if ($data === false) {
            $output->writeln('Failed to read data file!');
            return 2;
        }

        if (!json_validate($data)) {
            $output->writeln('Invalid JSON in data file!');
            return 2;
        }

        $success = $this->importService->articles($user, json_decode($data, true));
        if ($success === false) {
            $output->write("Failed to import data");
            return 1;
        }

        return 0;
    }
}
