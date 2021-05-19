<?php

namespace app\models;

use yii\db\ActiveRecord;

class Pricelist extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Pricelist}}';
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