<?php
declare(strict_types=1);

namespace OCA\News\Command\Debug;

use OCA\News\Controller\ApiPayloadTrait;
use OCA\News\Service\ItemServiceV2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ItemList
 *
 * @package OCA\News\Command
 */
class FeedItemList extends Command
{
    use ApiPayloadTrait;

    /**
     * @var ItemServiceV2 service for the items.
     */
    protected $itemService;

    public function __construct(ItemServiceV2 $itemService)
    {
        parent::__construct(null);

        $this->itemService = $itemService;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:item:list-feed')
            ->setDescription('List all items in a feed')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to list the items for')
            ->addArgument('feed', InputArgument::REQUIRED, 'Feed to list the items for')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit for item amount', 40)
            ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, 'Item list offset', 0)
            ->addOption('reverse-sort', null, InputOption::VALUE_NONE, 'Item list sorting')
            ->addOption('hide-read', null, InputOption::VALUE_NONE, 'Hide read items');
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

        $feed = $input->getArgument('feed');
        if (!is_numeric($feed)) {
            $output->writeln('Invalid Type!');
            return 255;
        }

        $limit = $input->getOption('limit');
        if (!is_numeric($limit)) {
            $output->writeln('Invalid limit!');
            return 255;
        }

        $offset = $input->getOption('offset');
        if (!is_numeric($offset)) {
            $output->writeln('Invalid offset!');
            return 255;
        }

        $reverseSort = $input->getOption('reverse-sort');
        $hideRead = $input->getOption('hide-read');

        $items = $this->itemService->findAllInFeedWithFilters(
            $user,
            intval($feed),
            intval($limit),
            intval($offset),
            $hideRead,
            $reverseSort,
            []
        );

        $output->writeln(json_encode($this->serialize($items), JSON_PRETTY_PRINT));

        return 0;
    }
}
