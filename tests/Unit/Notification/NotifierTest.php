<?php
/**
 * Nextcloud - News
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 */

namespace OCA\News\Tests\Unit\Notification;

use OCA\News\AppInfo\Application;
use OCA\News\Notification\Notifier;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserManager;
use OCP\L10N\IFactory;
use OCP\Notification\INotification;
use OCP\Notification\UnknownNotificationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NotifierTest extends TestCase
{
    /** @var MockObject|IFactory */
    private $l10nFactory;

    /** @var MockObject|IURLGenerator */
    private $urlGenerator;

    /** @var MockObject|IUserManager */
    private $userManager;

    /** @var MockObject|IL10N */
    private $l10n;

    /** @var Notifier */
    private $notifier;

    protected function setUp(): void
    {
        $this->l10nFactory = $this->createMock(IFactory::class);
        $this->urlGenerator = $this->createMock(IURLGenerator::class);
        $this->userManager = $this->createMock(IUserManager::class);
        $this->l10n = $this->createMock(IL10N::class);

        $this->notifier = new Notifier(
            $this->l10nFactory,
            $this->urlGenerator,
            $this->userManager
        );
    }

    public function testGetID(): void
    {
        $this->assertEquals(Application::NAME, $this->notifier->getID());
    }

    public function testGetName(): void
    {
        $this->l10nFactory->expects($this->once())
            ->method('get')
            ->with(Application::NAME)
            ->willReturn($this->l10n);

        $this->l10n->expects($this->once())
            ->method('t')
            ->with('News')
            ->willReturn('News');

        $this->assertEquals('News', $this->notifier->getName());
    }

    public function testPrepareThrowsForUnknownApp(): void
    {
        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn('some_other_app');

        $this->expectException(UnknownNotificationException::class);

        $this->notifier->prepare($notification, 'en');
    }

    public function testPrepareThrowsForUnknownSubject(): void
    {
        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn(Application::NAME);
        $notification->method('getSubject')->willReturn('unknown_subject');

        $this->l10nFactory->method('get')->willReturn($this->l10n);

        $this->expectException(UnknownNotificationException::class);

        $this->notifier->prepare($notification, 'en');
    }

    public function testPrepareSharedArticle(): void
    {
        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn(Application::NAME);
        $notification->method('getSubject')->willReturn('shared_article');
        $notification->method('getObjectId')->willReturn('42');
        $notification->method('getSubjectParameters')->willReturn([
            'sharedBy' => 'alice',
            'itemTitle' => 'Test Article',
        ]);

        $this->l10nFactory->method('get')->willReturn($this->l10n);
        $this->l10n->method('t')->willReturnCallback(function (string $text, array $params = []) {
            if (count($params) > 0) {
                return vsprintf(str_replace(['%1$s', '%2$s'], ['%s', '%s'], $text), $params);
            }
            return $text;
        });

        $user = $this->createMock(IUser::class);
        $user->method('getDisplayName')->willReturn('Alice Smith');
        $this->userManager->method('get')->with('alice')->willReturn($user);

        $this->urlGenerator->method('linkToRouteAbsolute')
            ->with('news.page.index')
            ->willReturn('https://cloud.example.com/apps/news');

        $this->urlGenerator->method('imagePath')
            ->with(Application::NAME, 'news.svg')
            ->willReturn('/apps/news/img/news.svg');

        $this->urlGenerator->method('getAbsoluteURL')
            ->with('/apps/news/img/news.svg')
            ->willReturn('https://cloud.example.com/apps/news/img/news.svg');

        $notification->expects($this->once())
            ->method('setParsedSubject')
            ->with('Alice Smith shared "Test Article" with you')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setRichSubject')
            ->with(
                '{user} shared "{article}" with you',
                [
                    'user' => [
                        'type' => 'user',
                        'id' => 'alice',
                        'name' => 'Alice Smith',
                    ],
                    'article' => [
                        'type' => 'highlight',
                        'id' => '42',
                        'name' => 'Test Article',
                    ],
                ]
            )
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setLink')
            ->with('https://cloud.example.com/apps/news')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setIcon')
            ->with('https://cloud.example.com/apps/news/img/news.svg')
            ->willReturnSelf();

        $result = $this->notifier->prepare($notification, 'en');
        $this->assertSame($notification, $result);
    }

    public function testPrepareSharedArticleWithMissingUser(): void
    {
        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn(Application::NAME);
        $notification->method('getSubject')->willReturn('shared_article');
        $notification->method('getObjectId')->willReturn('42');
        $notification->method('getSubjectParameters')->willReturn([
            'sharedBy' => 'deleted_user',
            'itemTitle' => 'Some Article',
        ]);

        $this->l10nFactory->method('get')->willReturn($this->l10n);
        $this->l10n->method('t')->willReturnCallback(function (string $text, array $params = []) {
            if (count($params) > 0) {
                return vsprintf(str_replace(['%1$s', '%2$s'], ['%s', '%s'], $text), $params);
            }
            return $text;
        });

        $this->userManager->method('get')->with('deleted_user')->willReturn(null);

        $this->urlGenerator->method('linkToRouteAbsolute')->willReturn('https://cloud.example.com/apps/news');
        $this->urlGenerator->method('imagePath')->willReturn('/apps/news/img/news.svg');
        $this->urlGenerator->method('getAbsoluteURL')->willReturn('https://cloud.example.com/apps/news/img/news.svg');

        // When user doesn't exist, the raw user ID should be used as display name
        $notification->expects($this->once())
            ->method('setParsedSubject')
            ->with('deleted_user shared "Some Article" with you')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setRichSubject')
            ->with(
                '{user} shared "{article}" with you',
                [
                    'user' => [
                        'type' => 'user',
                        'id' => 'deleted_user',
                        'name' => 'deleted_user',
                    ],
                    'article' => [
                        'type' => 'highlight',
                        'id' => '42',
                        'name' => 'Some Article',
                    ],
                ]
            )
            ->willReturnSelf();

        $notification->method('setLink')->willReturnSelf();
        $notification->method('setIcon')->willReturnSelf();

        $result = $this->notifier->prepare($notification, 'en');
        $this->assertSame($notification, $result);
    }

    public function testPrepareSharedArticleWithMissingTitle(): void
    {
        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn(Application::NAME);
        $notification->method('getSubject')->willReturn('shared_article');
        $notification->method('getObjectId')->willReturn('42');
        $notification->method('getSubjectParameters')->willReturn([
            'sharedBy' => 'alice',
        ]);

        $this->l10nFactory->method('get')->willReturn($this->l10n);
        $this->l10n->method('t')->willReturnCallback(function (string $text, array $params = []) {
            if (count($params) > 0) {
                return vsprintf(str_replace(['%1$s', '%2$s'], ['%s', '%s'], $text), $params);
            }
            return $text;
        });

        $user = $this->createMock(IUser::class);
        $user->method('getDisplayName')->willReturn('Alice');
        $this->userManager->method('get')->with('alice')->willReturn($user);

        $this->urlGenerator->method('linkToRouteAbsolute')->willReturn('https://cloud.example.com/apps/news');
        $this->urlGenerator->method('imagePath')->willReturn('/apps/news/img/news.svg');
        $this->urlGenerator->method('getAbsoluteURL')->willReturn('https://cloud.example.com/apps/news/img/news.svg');

        // When title is missing, it should fall back to 'an article'
        $notification->expects($this->once())
            ->method('setParsedSubject')
            ->with('Alice shared "an article" with you')
            ->willReturnSelf();

        $notification->expects($this->once())
            ->method('setRichSubject')
            ->with(
                '{user} shared "{article}" with you',
                $this->callback(function ($params) {
                    return $params['article']['name'] === 'an article';
                })
            )
            ->willReturnSelf();

        $notification->method('setLink')->willReturnSelf();
        $notification->method('setIcon')->willReturnSelf();

        $this->notifier->prepare($notification, 'en');
    }

    public function testPrepareSharedArticleWithEmptySharedBy(): void
    {
        $notification = $this->createMock(INotification::class);
        $notification->method('getApp')->willReturn(Application::NAME);
        $notification->method('getSubject')->willReturn('shared_article');
        $notification->method('getObjectId')->willReturn('42');
        $notification->method('getSubjectParameters')->willReturn([
            'itemTitle' => 'Test Article',
        ]);

        $this->l10nFactory->method('get')->willReturn($this->l10n);
        $this->l10n->method('t')->willReturnCallback(function (string $text, array $params = []) {
            if (count($params) > 0) {
                return vsprintf(str_replace(['%1$s', '%2$s'], ['%s', '%s'], $text), $params);
            }
            return $text;
        });

        // Empty sharedBy falls back to ''
        $this->userManager->method('get')->with('')->willReturn(null);

        $this->urlGenerator->method('linkToRouteAbsolute')->willReturn('https://cloud.example.com/apps/news');
        $this->urlGenerator->method('imagePath')->willReturn('/apps/news/img/news.svg');
        $this->urlGenerator->method('getAbsoluteURL')->willReturn('https://cloud.example.com/apps/news/img/news.svg');

        $notification->expects($this->once())
            ->method('setParsedSubject')
            ->with(' shared "Test Article" with you')
            ->willReturnSelf();

        $notification->method('setRichSubject')->willReturnSelf();
        $notification->method('setLink')->willReturnSelf();
        $notification->method('setIcon')->willReturnSelf();

        $this->notifier->prepare($notification, 'en');
    }
}
