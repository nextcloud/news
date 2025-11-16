<?php

namespace OCA\News\Command\Config;

use OCA\News\Service\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ItemExport extends Command
{
    /**
     * @var ExportService
     */
    protected $exportService;

    public function __construct(ExportService $exportService)
    {
        parent::__construct(null);

        $this->exportService = $exportService;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:item:export')
            ->setDescription('Get items as json data')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User data to export');
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

        $articles = $this->exportService->articles($user);
        $json = json_encode($articles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $output->write($json);
        return 0;
    }
}
