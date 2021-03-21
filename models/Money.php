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
            [['username', 'creditdebit', 'sum', 'ledger_id'], 'required'],
            [['beginner', 'text', 'closed', 'closed_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Name'
	    ];
    }
}