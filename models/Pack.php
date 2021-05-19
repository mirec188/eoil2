<?php

namespace app\models;

use yii\db\ActiveRecord;

class Pack extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Pack}}';
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