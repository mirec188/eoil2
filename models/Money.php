<?php

namespace app\models;

use yii\db\ActiveRecord;

class Money extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Money}}';
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