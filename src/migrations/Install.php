<?php
/**
 * QandA plugin for Craft CMS 3.x
 *
 * Question & Answers
 *
 * @link      https://kurious.agency
 * @copyright Copyright (c) 2019 Kurious Agency
 */

namespace kuriousagency\qanda\migrations;

use kuriousagency\qanda\QandA;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    Kurious Agency
 * @package   QandA
 * @since     0.0.1
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

   /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%qanda_questions}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%qanda_questions}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                    'siteId' => $this->integer(),
                    'question' => $this->text(),
					'answer' => $this->text(),
					'email' => $this->string(255),
					'firstName' => $this->string(255),
					'lastName' => $this->string(255),
					'enabled' => $this->boolean()->notNull()->defaultValue(false),
                    'customerId' => $this->integer(),
                    'hasRelation' => $this->boolean()
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * @return void
     */
    protected function createIndexes()
    {
        $this->createIndex(null, '{{%qanda_questions}}', 'customerId', false);
        $this->createIndex(null, '{{%qanda_relations}}', 'questionId', false);
        $this->createIndex(null, '{{%qanda_relations}}', 'elementId', false);

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * @return void
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%qanda_questions}}', ['id'], '{{%elements}}', ['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%qanda_questions}}', ['customerId'], '{{%commerce_customers}}', ['id']);
        $this->addForeignKey(null, '{{%qanda_relations}}', ['questionId'], '{{%qanda_questions}}',['id'], 'CASCADE');
        $this->addForeignKey(null, '{{%qanda_relations}}', ['elementId'], '{{%elements}}', ['id'], 'CASCADE');
    }

    /**
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * @return void
     */
    protected function removeTables()
    {
        $this->dropTableIfExists('{{%qanda_questions}}');
    }
}
