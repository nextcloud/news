<?php
namespace OCA\News\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use Psr\Log\LoggerInterface;
use OCP\BackgroundJob\IJobList;

class RemoveUnusedJob implements IRepairStep
{
    
    /**
     * @var LoggerInterface 
     */
    protected $logger;
    
    /**
     * @var IJobList 
     */
    protected $joblist;
    
    public function __construct(LoggerInterface $logger, IJobList $jobList)
    {
        
        $this->logger = $logger;
        $this->joblist = $jobList;
    }
    
    /**
     * Returns the step's name
     */
    public function getName()
    {
        return 'Remove the unused News update job';
    }
    
    /**
     * @param IOutput $output
     */
    public function run(IOutput $output)
    {
        if($this->joblist->has("OCA\News\Cron\Updater", null)){
            $output->info("Job exists, attempting to remove");
            $this->joblist->remove("OCA\News\Cron\Updater");
            $output->info("Job removed");
        } else {
            $output->info("Job does not exist, all good");
        }
        
    }
}