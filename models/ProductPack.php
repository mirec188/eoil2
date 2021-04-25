<?php

namespace app\models;

use yii\db\ActiveRecord;

class ProductPack extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{ProductHasPack}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['beginner', 'text', 'closed', 'closed_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
	    ];
    }

    public static function findByEid($eidType, $eId) {

        $q = "SELECT modelId FROM ExternalId WHERE model='ProductHasPack' AND value=:value AND typeId = :eidType";
        $result = \Yii::$app->db->createCommand($q, ['value'=>$eId, 'eidType'=>$eidType])->queryScalar();
        
        if ($result) {
            return self::findOne(['id'=>$result]);
        }

    }


}