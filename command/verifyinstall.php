<?php
/**
 * ownCloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @copyright Bernhard Posselt 2015
 */

namespace OCA\News\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Riimu\Kit\PathJoin\Path;

use OCA\News\Utility\FileChecksumValidator;


class VerifyInstall extends Command {

    private $fileChecksums;

    public function __construct($checksums) {
        parent::__construct();
        $this->fileChecksums = $checksums;
    }

    protected function configure() {
        $this->setName('news:verify-install')
             ->setDescription('Run this command to check if your News ' .
                              'installation has outdated or missing files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $checksums = json_decode($this->fileChecksums, true);
        $root = __DIR__ . '/../';
        $progressbar = new ProgressBar($output, count($checksums));
        $errors = [];
        $missing = [];

        foreach($checksums as $file => $checksum) {
            $progressbar->advance();
            $absPath = Path::normalize($root . $file);

            if (!file_exists($absPath)) {
                $missing[] = $absPath;
            } elseif (md5(file_get_contents($absPath)) !== $checksum) {
                $errors[] = $absPath;
            }
        }

        $output->writeln("\n");

        if (count($errors) > 0 || count($missing) > 0) {
            foreach ($missing as $path) {
                $output->writeln('<error>Missing file:</error> ' . $path);
            }
            foreach ($errors as $path) {
                $output->writeln('<error>Invalid checksum:</error> ' . $path);
            }
            $output->writeln("\nYour News installation does not " .
                             'match the recorded files and versions. This ' .
                             'is either caused by missing or old files or an ' .
                             'invalid or out of date appinfo/checksum.json ' .
                             'file.');
            $output->writeln('Either way, please make sure that the contents ' .
                             'of the News app\'s directory match the ' .
                             'contents of the installed tarball.');
            return 1;
        } else {
            $output->writeln('<info>Installation verified, everything OK!' .
                             '</info>');
        }
    }

}
