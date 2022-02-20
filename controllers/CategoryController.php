<?php

namespace app\controllers;

use yii\rest\ActiveController;
use \Yii;
use app\models\Product;
use app\models\ProductPack;
use yii\data\ActiveDataProvider;

class CategoryController extends ActiveController
{

	public $modelClass = 'app\models\Product';

	public function actions() {
	    $actions = parent::actions();
	    unset($actions['index']);
        unset($actions['view']);
	    return $actions;
	}

    protected function verbs() {
        $verbs = parent::verbs();
        
        $verbs['index'] = ['POST', 'GET', 'HEAD']; //methods you need in action
        
        return $verbs;
    }

    public function actionIndex() {
      $categories = $this->getCategories();
      $response = [
          "items" => $categories
      ];

      return $response;
    }

    private function getCategories() {
        $categories = \app\models\Category::find()->where("parentId IN (3, 13)")->all();

        $result = [];
        foreach ($categories as $category) {
            // if ($category->parentId == 0) {
                $c = [
                    "id"=>$category->id,
                    "name"=>$category->name,
                    "parentId"=>$category->parentId,
                    "type"=>$category->type,
                    "subcategories"=>[]
                ];


                foreach ($category->subcategories as $subcategory) {
                    if ($category->id == $subcategory->parentId) {
                        $c['subcategories'][] = [
                            "id"=>$subcategory->id,
                            "name"=>$subcategory->name,
                            "parentId"=>$subcategory->parentId,
                            "type"=>$subcategory->type,
                        ];
                    }
                }

                $result[] = $c;
            // }
        }
        
        return $result;
    }

}
