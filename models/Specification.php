<?php

namespace app\models;

use yii\db\ActiveRecord;

class Specification extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Specification}}';
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