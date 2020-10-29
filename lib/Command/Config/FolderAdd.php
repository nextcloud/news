<?php

namespace OCA\News\Command\Config;

use OCA\News\Db\Feed;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FolderServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FolderAdd extends Command
{
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
     */
    protected function configure()
    {
        $this->setName('news:folder:add')
            ->setDescription('Add a folder')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to add the folder for')
            ->addArgument('name', InputArgument::REQUIRED, 'Folder name', null)
            ->addOption('parent', null, InputOption::VALUE_OPTIONAL, 'Parent folder');
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
        $name = $input->getArgument('name');
        $parent = (int) $input->getOption('parent') ?? 0;

        $this->folderService->create($user, $name, $parent);

        return 0;
    }
}
