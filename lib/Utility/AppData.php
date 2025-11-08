<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */
namespace OCA\News\Utility;

use OCP\Files\IAppData;
use OCP\Files\GenericFileException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Files\StorageNotAvailableException;
use \Psr\Log\LoggerInterface;

class AppData
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var IAppData
    */
    private $appData;

    public function __construct(
        IAppData $appData,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->appData = $appData;
    }


    /**
     * Get or create an appdata folder
     *
     * @param string $foldername for the directory
     *
     * @return SimpleFolder The object of the appdata folder
     */
    public function getAppFolder(string $foldername): ?\OC\Files\SimpleFS\SimpleFolder
    {
        try {
            return $this->appData->getFolder($foldername);
        } catch (NotFoundException $e) {
            try {
                return $this->appData->newFolder($foldername);
            } catch (NotPermittedException $e) {
                $this->logger->error('Creating appdata folder ' . $foldername. ' is not permitted.');
                return null;
            }
        } catch (StorageNotAvailableException $e) {
                $this->logger->error('AppData storage ' . $foldername . ' is not available.');
                return null;
        } catch (\RuntimeException $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
        return null;
    }

    /**
     * Write data to an appdata file
     *
     * @param string $foldername for the directory
     * @param string $filename for the file
     * @param string $content for the data to write
     */
    public function putFileContent(string $foldername, string $filename, string $content): void
    {
        $folder = $this->getAppFolder($foldername);
        if (!$folder) {
            return;
        }
        try {
            if ($folder->fileExists($filename)) {
                $this->getAppFolder($foldername)->getFile($filename)->putContent($content);
            } else {
                $this->getAppFolder($foldername)->newFile($filename)->putContent($content);
            }
        } catch (GenericFileException|NotPermittedException $e) {
            $this->logger->info('Reading appdata file {file} failed: {error}', [
                'file'   => $foldername.'/'.$filename,
                'error' => $e->getMessage() ?? 'Unknown'
                ]);
        }
    }


    /**
     * Read data from an appdata file
     *
     * @param string $foldername for the directory
     * @param string $filename for the file
     *
     * @return string|null The data read from the appdata file, null on error or file not found
     */
    public function getFileContent(string $foldername, string $filename): ?string
    {
        $folder = $this->getAppFolder($foldername);
        if (!$folder) {
            return null;
        }
        try {
            if ($folder->fileExists($filename)) {
                return $this->getAppFolder($foldername)->getFile($filename)->getContent();
            }
        } catch (GenericFileException|NotPermittedException $e) {
            $this->logger->info('Reading appdata file {file} failed: {error}', [
                'file'   => $foldername.'/'.$filename,
                'error' => $e->getMessage() ?? 'Unknown'
                ]);
        }
        return null;
    }

    /**
     * Return mtime from an appdata file
     *
     * @param string $foldername for the directory
     * @param string $filename for the file
     *
     * @return int|null The mtime read from the appdata file, null on error or file not found
     */
    public function getMTime(string $foldername, string $filename): ?int
    {
        $folder = $this->getAppFolder($foldername);
        if (!$folder) {
            return null;
        }
        try {
            if ($folder->fileExists($filename)) {
                return $this->getAppFolder($foldername)->getFile($filename)->getMTime();
            }
        } catch (GenericFileException|NotPermittedException $e) {
            $this->logger->info('Getting mtime from file {file} failed: {error}', [
                'file'   => $foldername.'/'.$filename,
                'error' => $e->getMessage() ?? 'Unknown'
                ]);
        }
        return null;
    }
}
