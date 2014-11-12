<?php

namespace sersid\config\migrations;

use yii\db\Schema;
use yii\db\Migration;

/**
 * Install
 * php yii migrate --migrationPath=@vendor/sersid/yii2-config/migrations
 * 
 * Uninstall
 * php yii migrate/down --migrationPath=@vendor/sersid/yii2-config/migrations
 */
class m141028_111823_config extends Migration
{
    public $tableName = '{{%config}}';

    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable($this->tableName, [
            'key' => Schema::TYPE_STRING,
            'value' => Schema::TYPE_TEXT,
        ], $tableOptions);
        $this->addPrimaryKey('key', $this->tableName, ['key']);
    }

    public function down()
    {
        $this->dropTable($this->tableName);
    }
}
