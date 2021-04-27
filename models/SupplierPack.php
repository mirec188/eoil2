<?php

namespace app\models;

use yii\db\ActiveRecord;

class SupplierPack extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{SupplierHasPack}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
             [['id', 'productHasPackId', 'price', 'supplierId'], 'safe'],
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


}