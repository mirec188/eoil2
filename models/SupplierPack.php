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
             [['id', 'productHasPackId', 'price', 'supplierId', 'modifiedBy'], 'safe'],
        ];
    }

    public function beforeSave($insert) {
        $this->modifiedBy = 'admin';
        return parent::beforeSave($insert);
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