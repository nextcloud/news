<?php

namespace OCA\News\Command\Config;

use OCA\News\Controller\ApiPayloadTrait;
use OCA\News\Service\FolderServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FolderList extends Command
{
    use ApiPayloadTrait;

    /**
     * @var FolderServiceV2 service for the folders.
     */
    protected $folderService;

    public function __construct(FolderServiceV2 $folderService)
    {
        parent::__construct(null);

        $this->folderService = $folderService;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:folder:list')
            ->setDescription('List all folders')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to list the folders for')
            ->addOption('recursive', null, InputOption::VALUE_NONE, 'Fetch the folder recursively');
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $input->getArgument('user-id');
        $recursive = $input->getOption('recursive');

        if ($recursive !== false) {
            $folders = $this->folderService->findAllForUserRecursive($user);
        } else {
            $folders = $this->folderService->findAllForUser($user);
        }

        $output->writeln(json_encode($this->serialize($folders), JSON_PRETTY_PRINT));

        return 0;
    }
}
