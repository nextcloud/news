<?php
declare(strict_types=1);

namespace OCA\News\Command\Debug;

use OCA\News\Controller\ApiPayloadTrait;
use OCA\News\Db\ListType;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FolderServiceV2;
use OCA\News\Service\ItemServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ItemRead
 *
 * @package OCA\News\Command
 */
class FolderRead extends Command
{
    /**
     * @var FolderServiceV2 service for the folders.
     */
    protected $folderService;

    /**
     * @var ItemServiceV2 service for the items.
     */
    protected $itemService;

    public function __construct(FolderServiceV2 $folderService, ItemServiceV2 $itemService)
    {
        parent::__construct();

        $this->folderService = $folderService;
        $this->itemService = $itemService;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('news:folder:read')
            ->setDescription('Read folder')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to modify the folder for')
            ->addArgument('id', InputArgument::REQUIRED, 'Folder ID');
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

        $id = $input->getArgument('id');
        if (!is_numeric($id)) {
            $output->writeln('Invalid id!');
            return 255;
        }

        try {
            $read = $this->folderService->read($user, intval($id));
            $output->writeln("Marked $read items as read", $output::VERBOSITY_VERBOSE);
        } catch (ServiceConflictException | ServiceNotFoundException $e) {
            $output->writeln('Failed: ' . $e->getMessage());
            return 0;
        }

        return 0;
    }
}
