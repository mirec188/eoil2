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

    public function getFullName() {
		$entityName = $this->entity ? $this->entity->name : '';
	    return $this->large.'x'.round($this->amount,2).$entityName .' '.$this->description;
	}
	
	public function getEntityName() {
		return $this->entity ? $this->entity->name : '';
	}

	public function getName() {
		
		if ($this->bigOnly) {
			$entityName = $this->entity ? $this->entity->name : '';
			return $this->large.'x'.round($this->amount,2).$entityName.' '.$this->description;
		}

		$entityName = $this->entity ? $this->entity->name : '';
		if ($this->description) $entityName.' '.$this->description;
		return round($this->amount,2).''.$entityName;
	}

	public function getShortName() {
		
		if ($this->bigOnly) {
			$entityName = $this->entity ? $this->entity->name : '';
			return $this->large.'x'.round($this->amount,2).$entityName;
		}

		$entityName = $this->entity ? $this->entity->name : '';
		return round($this->amount,2).''.$entityName;
	}

    public function getEntity() {
        return $this->hasOne(Viscosity::class, ['id' => 'entityId']);
    }
}