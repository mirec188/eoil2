<?php

namespace app\models;

use yii\db\ActiveRecord;

class Photo extends ActiveRecord
{ 
    public static function tableName()
    {
        return '{{Photo}}';
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

    public function getFilePath(){
		return '/files/photo/' . date('Y-m', strtotime($this->createTime)) . '/' . $this->id . '/' . $this->hash;
	}
	public function getHash(){
		$fileSalt = \Yii::$app->params['fileSalt'];
		return substr(md5($this->fileName . $fileSalt),0,6);
	}
	
	public function getFileExtension(){
		$pathParts = pathinfo($this->fileName);
		return isset($pathParts['extension']) ? strtolower($pathParts['extension']) : false;
	}
	
	public function getFileBaseName(){
		$pathParts = pathinfo($this->fileName);
		return isset($pathParts['basename']) ? $pathParts['basename'] : false;
	}

}