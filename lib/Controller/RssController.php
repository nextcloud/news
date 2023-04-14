<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author    Paul Tirk <paultirk@paultirk.com>
 * @copyright 2020 Paul Tirk
 */

namespace OCA\News\Controller;

use FeedIo\Feed;
use FeedIo\FeedIo;
use OCA\News\Service\ItemServiceV2;
use OCA\News\Utility\XMLResponse;
use OCP\AppFramework\Http;
use \OCP\IRequest;
use OCP\IUserManager;
use \OCP\IUserSession;
use Psr\Log\LoggerInterface;

/**
 * This controller is in charge of exposing RSS feeds from
 * gathered feed items.
 */
class RssController extends Controller
{
    use JSONHttpErrorTrait;

    /**
     * @var ItemServiceV2
     */
    private $itemService;

    /**
     * @var IUserManager
     */
    private $userManager;

    /**
     * @var FeedIo
     */
    private $feedIo;

    public function __construct(
        IRequest      $request,
        IUserSession  $userSession,
        IUserManager  $userManager,
        ItemServiceV2 $itemService,
        FeedIo        $feedIo
    ) {
        parent::__construct($request, $userSession);

        $this->userManager = $userManager;
        $this->itemService = $itemService;
        $this->feedIo = $feedIo;
    }

    /**
     * @NoCSRFRequired
     * @PublicPage
     *
     * @param string $name
     * @return array|mixed|\OCP\AppFramework\Http\JSONResponse
     */
    public function starred($userId)
    {
        $user = $this->userManager->get($userId);
        if ($user === null) {
            return $this->errorResponseWithExceptionV2(
                new \Exception("User not found"),
                Http::STATUS_NOT_FOUND
            );
        }

        $starredItems = $this->itemService->starred($user->getUID());

        $feed = new Feed();
        $feed->setTitle("{$user->getDisplayName()}'s starred items");

        foreach ($starredItems as $starredItem) {
            $author = new Feed\Item\Author();
            $author->setName($starredItem->getAuthor());

            $item = new Feed\Item();
            $item->setTitle(
                $starredItem->getTitle())
                    ->setAuthor($author)
                    ->setDescription($starredItem->getBody())
                    ->setLink($starredItem->getUrl());

            $feed->add($item);
        }

        $response = new XMLResponse($this->feedIo->toAtom($feed), Http::STATUS_OK, [
            "Content-Type" => "application/atom+xml"
        ]);

        return $response;
    }
}
