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

	protected function getAmountName() {
		if (!$this->visible) {
			return '';
		}
		return round($this->amount,2).$this->entity->name;
	}

    public function getFullName() {
		$entityName = $this->entity ? $this->entity->name : '';
	    return $this->large.'x'.$this->getAmountName() .' '.$this->description;
	}
	
	public function getEntityName() {
		return $this->entity ? $this->entity->name : '';
	}

	public function getName() {
		
		if ($this->bigOnly) {
			$entityName = $this->entity ? $this->entity->name : '';
			return $this->large.'x'.$this->getAmountName().' '.$this->description;
		}

		$entityName = $this->entity ? $this->entity->name : '';
		if ($this->description) $entityName.' '.$this->description;
		return $this->getAmountName();
	}

	public function getShortName() {
		
		if ($this->bigOnly) {
			$entityName = $this->entity ? $this->entity->name : '';
			return $this->large.'x'.$this->getAmountName();
		}

		$entityName = $this->entity ? $this->entity->name : '';
		return $this->getAmountName();
	}

    public function getEntity() {
        return $this->hasOne(Viscosity::class, ['id' => 'entityId']);
    }
}