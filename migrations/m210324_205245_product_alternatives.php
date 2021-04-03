<?php

use yii\db\Migration;

/**
 * Class m210324_205245_product_alternatives
 */
class m210324_205245_product_alternatives extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $q = "CREATE TABLE `product_alternative` (
  `product_id` bigint NOT NULL,
  `alternative_id` bigint NOT NULL
) COLLATE 'utf8_general_ci';";

        \Yii::$app->db->createCommand($q)->execute();

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m210324_205245_product_alternatives cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210324_205245_product_alternatives cannot be reverted.\n";

        return false;
    }
    */
}
