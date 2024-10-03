<?php

declare(strict_types=1);
/**
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */
namespace OCA\News\Listeners;

use OCP\Config\BeforePreferenceDeletedEvent;
use OCP\Config\BeforePreferenceSetEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/** @template-implements IEventListener<BeforePreferenceSetEvent|BeforePreferenceDeletedEvent> */
class UserSettingsListener implements IEventListener {

	public function handle(Event $event): void {
		if ($event instanceof BeforePreferenceSetEvent) {
			if ($event->getAppId() === 'news') {
				$event->setValid(true);
			}
		} elseif ($event instanceof BeforePreferenceDeletedEvent) {
			if ($event->getAppId() === 'news') {
				$event->setValid(true);
			}
		}
	}
}
