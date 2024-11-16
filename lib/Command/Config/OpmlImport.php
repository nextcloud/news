<?php

namespace OCA\News\Command\Config;

use OCA\News\Service\OpmlService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpmlImport extends Command
{
    /**
     * @var OpmlService service for the data.
     */
    protected $opmlService;

    public function __construct(OpmlService $opmlService)
    {
        parent::__construct(null);

        $this->opmlService = $opmlService;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:opml:import')
            ->setDescription('Import OPML file')
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

        $success = $this->opmlService->import($user, $data);
        if ($success === false) {
            $output->write("Failed to import data");
            return 1;
        }

        return 0;
    }
}
