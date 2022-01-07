<?php

namespace app\models;

use yii\db\ActiveRecord;

class ProductAttribute extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{ProductAttribute}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
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

    public function getType() {
        return $this->hasOne(ProductAttributeType::class, ['id' => 'productAttributeTypeId']);
    }
}