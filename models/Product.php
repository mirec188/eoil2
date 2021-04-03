<?php

namespace app\models;

use yii\db\ActiveRecord;

class Product extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Product}}';
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

    public function getFullName($viscosity = false, $oldName = true) {
        if ($this->nameOld && $oldName) $nameOld = ' ('.$this->nameOld.')'; else $nameOld = '';
        $producerName = $this->producer ? $this->producer->name : '';
        return str_replace(' SAE ', ' ', $producerName.' '.$this->name.' '.($this->viscosity && $viscosity ? $this->viscosity->name : '').$nameOld);
    }

    public function isActive() {
        $r = false;
        foreach ($this->productPacks as $p) {
            if ($p->active) $r=true;
        }   
        return $r;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
	    ];
    }

    public function getProducer() {
        return $this->hasOne(Producer::className(), ['id' => 'producerId']);
    }

    public function getViscosity() {
        return $this->hasOne(Viscosity::className(), ['id' => 'viscosityId']);
    }

    public function getProductPacks() {
        return $this->hasMany(ProductPack::className(), ['productId' => 'id']);
    }

}