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

    public function getCategories() {
        if (!$this->id) return false;
        
        $ids = $this->getCategoryIds();   

        if (!empty($ids)) {
            $ids = implode(',', $ids);
            return Category::find()->where("id in ($ids)")->all();
        }

        return false;
    }

    public function getCategoryIds() {
        if (!$this->id) return false;
        $q = "SELECT categoryId, parentId FROM ProductHasCategory INNER JOIN Category ON ProductHasCategory.categoryId = Category.id WHERE productId = {$this->id} and (parentId <> 0 OR categoryId IN (
                SELECT id FROM Category WHERE parentId = 0 AND id NOT IN (SELECT id FROM Category WHERE parentId > 0)
            ))";
        
        $result = \Yii::$app->db->createCommand($q)->queryAll();

        $ids = [];

        if ($result) {
            foreach ($result as $r) {
                // if ($r['parentId'] > 0) {
                    $ids[]=$r['categoryId'];    
                // }
            }
        }

        return $ids;
    }


    public function getProducer() {
        return $this->hasOne(Producer::class, ['id' => 'producerId']);
    }

    public function getViscosity() {
        return $this->hasOne(Viscosity::class, ['id' => 'viscosityId']);
    }

    public function getProductPacks() {
        return $this->hasMany(ProductPack::class, ['productId' => 'id']);
    }

    public function getSpecifications() {
        return $this->hasMany(Specification::class, ['id' => 'specificationId'])->viaTable('SpecificationHasProduct', ['productId' => 'id']);
    }

    public function getProductAttributes() {
        return $this->hasMany(ProductAttribute::class, ['productId' => 'id']);
    }

}