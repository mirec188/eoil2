<?php

use yii\db\Migration;

/**
 * Class m210519_190902_cart_checkbox
 */
class m210519_190902_cart_checkbox extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        
        
         \Yii::$app->db->createCommand("

            ALTER TABLE `Cart`
ADD `in_stock` tinyint NULL;

        ")->execute();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210519_190902_cart_checkbox cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210519_190902_cart_checkbox cannot be reverted.\n";

        return false;
    }
    */
}
