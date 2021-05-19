<?php

namespace app\models;

use yii\db\ActiveRecord;

class Price extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Price}}';
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