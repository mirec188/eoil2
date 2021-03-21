<?php

use yii\db\Migration;

/**
 * Class m210319_222335_pokladna_ledger
 */
class m210319_222335_pokladna_ledger extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \Yii::$app->db->createCommand("ALTER TABLE `Money` ADD `ledger_id` tinyint NULL;")->execute();
        \Yii::$app->db->createCommand("UPDATE Money SET ledger_id=0;")->execute();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210319_222335_pokladna_ledger cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration ecode without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210319_222335_pokladna_ledger cannot be reverted.\n";

        return false;
    }
    */
}
