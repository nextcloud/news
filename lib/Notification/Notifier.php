<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Notification;

use OCA\News\AppInfo\Application;
use OCP\IURLGenerator;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\INotifier;
use OCP\Notification\UnknownNotificationException;

class Notifier implements INotifier
{
    private IFactory $l10nFactory;
    private IURLGenerator $urlGenerator;
    private IUserManager $userManager;

    public function __construct(
        IFactory $l10nFactory,
        IURLGenerator $urlGenerator,
        IUserManager $userManager
    ) {
        $this->l10nFactory = $l10nFactory;
        $this->urlGenerator = $urlGenerator;
        $this->userManager = $userManager;
    }

    public function getID(): string
    {
        return Application::NAME;
    }

    public function getName(): string
    {
        return $this->l10nFactory->get(Application::NAME)->t('News');
    }

    public function prepare(INotification $notification, string $languageCode): INotification
    {
        if ($notification->getApp() !== Application::NAME) {
            throw new UnknownNotificationException();
        }

        $l = $this->l10nFactory->get(Application::NAME, $languageCode);

        switch ($notification->getSubject()) {
            case 'shared_article':
                return $this->prepareSharedArticle($notification, $l);

            default:
                throw new UnknownNotificationException();
        }
    }

    private function prepareSharedArticle(INotification $notification, \OCP\IL10N $l): INotification
    {
        $params = $notification->getSubjectParameters();
        $sharerUserId = $params['sharedBy'] ?? '';
        $itemTitle = $params['itemTitle'] ?? $l->t('an article');

        $sharerDisplayName = $sharerUserId;
        $user = $this->userManager->get($sharerUserId);
        if ($user !== null) {
            $sharerDisplayName = $user->getDisplayName();
        }

        $notification->setParsedSubject(
            $l->t('%1$s shared "%2$s" with you', [$sharerDisplayName, $itemTitle])
        );

        $notification->setRichSubject(
            $l->t('{user} shared "{article}" with you'),
            [
                'user' => [
                    'type' => 'user',
                    'id' => $sharerUserId,
                    'name' => $sharerDisplayName,
                ],
                'article' => [
                    'type' => 'highlight',
                    'id' => $notification->getObjectId(),
                    'name' => $itemTitle,
                ],
            ]
        );

        $notification->setLink(
            $this->urlGenerator->linkToRouteAbsolute('news.page.index')
        );

        $notification->setIcon(
            $this->urlGenerator->getAbsoluteURL(
                $this->urlGenerator->imagePath(Application::NAME, 'news.svg')
            )
        );

        return $notification;
    }
}
