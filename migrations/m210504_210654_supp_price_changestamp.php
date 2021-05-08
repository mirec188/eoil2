<?php

use yii\db\Migration;

/**
 * Class m210504_210654_supp_price_changestamp
 */
class m210504_210654_supp_price_changestamp extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
            $q = "ALTER TABLE `SupplierHasPack`
ADD `changeTime` timestamp NOT NULL,
ADD `modifiedBy` varchar(255) NOT NULL AFTER `changeTime`;";

        \Yii::$app->db->createCommand($q)->execute();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210504_210654_supp_price_changestamp cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210504_210654_supp_price_changestamp cannot be reverted.\n";

        return false;
    }
    */
}
