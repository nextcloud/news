<?php

declare(strict_types=1);

namespace OCA\News\Command\Config;

use OCA\News\Db\Filter;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
use OCA\News\Service\FeedServiceV2;
use OCA\News\Service\FilterService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FeedFilterSet extends Command
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
        $this->setName('news:feed:filter:set')
            ->setDescription('Create or update keyword filter settings for a feed')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to update filter settings for')
            ->addArgument('feed-id', InputArgument::REQUIRED, 'Feed ID')
            ->addOption('title', null, InputOption::VALUE_OPTIONAL, 'Comma-separated title keywords')
            ->addOption('body', null, InputOption::VALUE_OPTIONAL, 'Comma-separated body keywords')
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, 'Comma-separated URL keywords');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = (string) $input->getArgument('user-id');
        $feedId = (int) $input->getArgument('feed-id');

        $title = $input->getOption('title');
        $body = $input->getOption('body');
        $url = $input->getOption('url');

        if ($title === null && $body === null && $url === null) {
            $output->writeln('At least one option must be set: --title, --body, or --url');
            return 1;
        }

        try {
            $this->feedService->find($userId, $feedId);
        } catch (ServiceNotFoundException $e) {
            $output->writeln($e->getMessage());
            return 1;
        }

        $filter = $this->filterService->findByFeedId($userId, $feedId);

        if ($filter === null) {
            $filter = new Filter();
            $filter->setFeedId($feedId);
        }

        if ($title !== null) {
            $filter->setTitleKeywords((string) $title);
        }
        if ($body !== null) {
            $filter->setBodyKeywords((string) $body);
        }
        if ($url !== null) {
            $filter->setUrlKeywords((string) $url);
        }

        if ($filter->getId() === null) {
            $filter = $this->filterService->insert($filter);
        } else {
            $filter = $this->filterService->update($userId, $filter);
        }

        $this->filterService->clearAndReapplyFilter($userId, $feedId);

        $output->writeln((string) json_encode($filter->toAPI(), JSON_PRETTY_PRINT));

        return 0;
    }
}
