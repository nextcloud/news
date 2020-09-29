<?php

namespace OCA\News\Command\Config;

use OCA\News\Service\OpmlService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OpmlExport extends Command
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
     */
    protected function configure()
    {
        $this->setName('news:opml:export')
            ->setDescription('Print OPML file')
            ->addArgument('userID', InputArgument::REQUIRED, 'User data to export');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = $input->getArgument('userID');

        $output->write($this->opmlService->export($user));
        return 0;
    }
}
