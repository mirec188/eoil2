<?php

namespace app\models;

use yii\db\ActiveRecord;

class Producer extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Producer}}';
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