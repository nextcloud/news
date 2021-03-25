<?php
declare(strict_types=1);

namespace OCA\News\Command\Debug;

use OCA\News\Controller\ApiPayloadTrait;
use OCA\News\Db\ListType;
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
class ItemList extends Command
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
        $this->setName('news:item:list')
            ->setDescription('List all items')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to list the items for')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Item type', ListType::ALL_ITEMS)
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit for item amount', 40)
            ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, 'Item list offset', 0)
            ->addOption('reverse-sort', 'r', InputOption::VALUE_NONE, 'Item list sorting');
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

        $type = $input->getOption('type');
        if (!is_numeric($type)) {
            $output->writeln('Invalid type!');
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

        $items = $this->itemService->findAllWithFilters(
            $user,
            intval($type),
            intval($limit),
            intval($offset),
            $reverseSort,
            []
        );

        $output->writeln(json_encode($this->serialize($items), JSON_PRETTY_PRINT));

        return 0;
    }
}
