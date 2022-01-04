<?php

namespace app\models;

use yii\db\ActiveRecord;

class Entity extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Entity}}';
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