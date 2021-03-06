<?php

declare(strict_types=1);

namespace OCA\News\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Auto-generated migration step: Please modify to your needs!
 */
class Version140200Date20200824201413 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 *
	 * @return void
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('news_folders')) {
			$table = $schema->createTable('news_folders');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('parent_id', 'bigint', [
				'notnull' => false,
				'length' => 8,
			]);
			$table->addColumn('name', 'string', [
				'notnull' => true,
				'length' => 100,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);
			$table->addColumn('opened', 'boolean', [
				'notnull' => true,
				'default' => true,
			]);
			$table->addColumn('deleted_at', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('last_modified', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['last_modified'], 'news_folders_last_mod_idx');
			$table->addIndex(['parent_id'], 'news_folders_parent_id_idx');
			$table->addIndex(['user_id'], 'news_folders_user_id_idx');
		}

		if (!$schema->hasTable('news_feeds')) {
			$table = $schema->createTable('news_feeds');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);
			$table->addColumn('last_modified', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('url_hash', 'string', [
				'notnull' => true,
				'length' => 32,
			]);
			$table->addColumn('url', 'text', [
				'notnull' => true,
			]);
			$table->addColumn('location', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('title', 'text', [
				'notnull' => true,
			]);
			$table->addColumn('link', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('favicon_link', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('http_last_modified', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('http_etag', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('added', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('articles_per_update', 'bigint', [
				'notnull' => true,
				'length' => 8,
				'default' => 0,
			]);
			$table->addColumn('update_error_count', 'bigint', [
				'notnull' => true,
				'length' => 8,
				'default' => 0,
			]);
			$table->addColumn('last_update_error', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('basic_auth_user', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('basic_auth_password', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('deleted_at', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->addColumn('folder_id', 'bigint', [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('prevent_update', 'boolean', [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('pinned', 'boolean', [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('full_text_enabled', 'boolean', [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('ordering', 'integer', [
				'notnull' => true,
				'default' => 0,
			]);
			$table->addColumn('update_mode', 'integer', [
				'notnull' => true,
				'default' => 0,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['last_modified'], 'news_feeds_last_mod_idx');
			$table->addIndex(['user_id'], 'news_feeds_user_id_index');
			$table->addIndex(['folder_id'], 'news_feeds_folder_id_index');
			$table->addIndex(['url_hash'], 'news_feeds_url_hash_index');
		}

		if (!$schema->hasTable('news_items')) {
			$table = $schema->createTable('news_items');
			$table->addColumn('id', 'bigint', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('guid_hash', 'string', [
				'notnull' => true,
				'length' => 32,
			]);
			$table->addColumn('fingerprint', 'string', [
				'notnull' => false,
				'length' => 32,
			]);
			$table->addColumn('content_hash', 'string', [
				'notnull' => false,
				'length' => 32,
			]);
			$table->addColumn('rtl', 'boolean', [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('search_index', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('guid', 'text', [
				'notnull' => true,
			]);
			$table->addColumn('url', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('title', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('author', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('pub_date', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('updated_date', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'unsigned' => true,
			]);
			$table->addColumn('body', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('enclosure_mime', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('enclosure_link', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('media_thumbnail', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('media_description', 'text', [
				'notnull' => false,
			]);
			$table->addColumn('feed_id', 'bigint', [
				'notnull' => true,
				'length' => 8,
			]);
			$table->addColumn('status', 'bigint', [
				'notnull' => true,
				'length' => 8,
				'default' => 0,
			]);
			$table->addColumn('unread', 'boolean', [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('starred', 'boolean', [
				'notnull' => true,
				'default' => false,
			]);
			$table->addColumn('last_modified', 'bigint', [
				'notnull' => false,
				'length' => 8,
				'default' => 0,
				'unsigned' => true,
			]);
			$table->setPrimaryKey(['id']);
			$table->addIndex(['last_modified'], 'news_items_last_mod_idx');
			$table->addIndex(['fingerprint'], 'news_items_fingerprint_idx');
			$table->addIndex(['guid_hash', 'feed_id'], 'news_items_item_guid');
			$table->addIndex(['unread', 'feed_id'], 'news_items_unread_feed_id');
			$table->addIndex(['starred', 'feed_id'], 'news_items_starred_feed_id');
			$table->addIndex(['unread', 'id'], 'news_items_unread_id');
		}
		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 *
	 * @return void
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options) {
	}
}
