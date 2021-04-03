<?php

namespace app\models;

use yii\db\ActiveRecord;

class ProductPack extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{ProductHasPack}}';
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