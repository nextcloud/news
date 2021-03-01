<?php
declare(strict_types=1);

namespace OCA\News\Command\Debug;

use OCA\News\Controller\ApiPayloadTrait;
use OCA\News\Db\ListType;
use OCA\News\Service\Exceptions\ServiceConflictException;
use OCA\News\Service\Exceptions\ServiceNotFoundException;
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
class ItemRead extends Command
{
    use ApiPayloadTrait;

    /**
     * @var ItemServiceV2 service for the items.
     */
    protected $itemService;

    public function __construct(ItemServiceV2 $itemService)
    {
        parent::__construct();

        $this->itemService = $itemService;
    }

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('news:item:read')
            ->setDescription('Read item')
            ->addArgument('user-id', InputArgument::REQUIRED, 'User to modify the item for')
            ->addOption('id', 'i', InputOption::VALUE_REQUIRED, 'Item ID')
            ->addOption('read', 'r', InputOption::VALUE_NONE, 'Item read state');
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
        $read = $input->getOption('read');

        $id = $input->getOption('id');
        if (!is_null($id) && !is_numeric($id)) {
            $output->writeln('Invalid id!');
            return 255;
        }


        try {
            if (is_null($id)) {
                $readItems = $this->itemService->readAll($user, $this->itemService->newest($user)->getId());
                $output->writeln("Marked $readItems items as read", $output::VERBOSITY_VERBOSE);
            } else {
                $items = $this->itemService->read($user, intval($id), $read);
                $output->writeln(json_encode($this->serialize($items), JSON_PRETTY_PRINT));
            }
        } catch (ServiceConflictException | ServiceNotFoundException $e) {
            $output->writeln('Failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
