<?php

use yii\db\Migration;

/**
 * Class m210405_185700_indexe
 */
class m210405_185700_indexe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        \Yii::$app->db->createCommand("ALTER TABLE `product_alternative`
ADD INDEX `product_id` (`product_id`),
ADD INDEX `alternative_id` (`alternative_id`);")->execute();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210405_185700_indexe cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210405_185700_indexe cannot be reverted.\n";

        return false;
    }
    */
}
