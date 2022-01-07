<?php

namespace app\models;

use yii\db\ActiveRecord;

class ProductAttributeType extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{ProductAttributeType}}';
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
}