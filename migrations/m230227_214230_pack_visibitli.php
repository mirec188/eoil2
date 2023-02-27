<?php

use yii\db\Migration;

/**
 * Class m230227_214230_pack_visibitli
 */
class m230227_214230_pack_visibitli extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \Yii::$app->db->createCommand("

            ALTER TABLE `Pack`
ADD `visible` tinyint NOT NULL default 1;
        ")->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m230227_214230_pack_visibitli cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m230227_214230_pack_visibitli cannot be reverted.\n";

        return false;
    }
    */
}
