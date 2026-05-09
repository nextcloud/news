<?php

declare(strict_types=1);

namespace OCA\News\Command\Config;

use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FilterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FeedFilterDelete extends Command
{
    /**
     * @var FeedServiceV2
     */
    protected $feedService;

    /**
     * @var FilterService
     */
    protected $filterService;

    public function __construct(FeedServiceV2 $feedService, FilterService $filterService)
    {
        parent::__construct(null);

        $this->feedService = $feedService;
        $this->filterService = $filterService;
    }

    /**
     * Configure command
     */
    protected function configure(): void
    {
        $this->setName('news:feed:filter:delete')
            ->setDescription('Delete keyword filter settings for a feed')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to delete filter settings for')
            ->addArgument('feed-id', InputArgument::REQUIRED, 'Feed ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = (string) $input->getArgument('user-id');
        $feedId = (int) $input->getArgument('feed-id');

        try {
            $this->feedService->find($userId, $feedId);
        } catch (ServiceNotFoundException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        $filter = $this->filterService->findByFeedId($userId, $feedId);

        if ($filter !== null && $filter->getId() !== null) {
            $this->filterService->delete($userId, $filter->getId());
        }

        $this->filterService->clearAndReapplyFilter($userId, $feedId);

        return 0;
    }
}
