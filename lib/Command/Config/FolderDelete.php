<?php

namespace OCA\News\Command\Config;

use OCA\News\Service\Exceptions\ServiceValidationException;
use OCA\News\Service\FolderServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FolderDelete extends Command
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
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:folder:delete')
            ->setDescription('Remove a folder')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to remove the folder from')
            ->addArgument('folder-id', InputArgument::REQUIRED, 'Folder ID', null);
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
        $id = $input->getArgument('folder-id');

        if ($id === null) {
            throw new ServiceValidationException('Can not remove root folder!');
        }

        $this->folderService->delete($user, intval($id));

        return 0;
    }
}
