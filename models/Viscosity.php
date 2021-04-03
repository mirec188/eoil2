<?php

namespace app\models;

use yii\db\ActiveRecord;

class Viscosity extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Viscosity}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['beginner', 'text', 'closed', 'closed_date'], 'safe'],
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