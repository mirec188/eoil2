<?php

namespace app\models;

use yii\db\ActiveRecord;

class Category extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Category}}';
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

    public function getSubcategories() {
        return $this->hasMany(Category::class, ['parentId' => 'id']);
    }
}