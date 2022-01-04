<?php

namespace app\controllers;

use yii\rest\ActiveController;
use \Yii;
use app\models\Product;
use app\models\ProductPack;
use yii\data\ActiveDataProvider;

class PackageController extends ActiveController
{

	public $modelClass = 'app\models\Product';

	public function actions() {
	    $actions = parent::actions();
	    unset($actions['index']);
	    return $actions;
	}

    public function actionIndex() {
        $result = [
            "items" => [],
        ];
        $items = [];
        $packQuery = ProductPack::find();
        $packProvider = new ActiveDataProvider([
            'query' => $packQuery,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        foreach ($packProvider->getModels() as $pack) {
            $item = [
                'id'=>$pack->id,
                'fullName'=>$pack->getFullName(true, true),
                'amount'=>$pack->pack->amount,
                'bigPackage'=>$pack->pack->large,
                'bigOnly'=>$pack->pack->bigOnly ? true : false,
                "price"=>[
                    "price" => 1,
                    "priceDph" => 2,
                    "priceSum" => 3,
                    "priceSumDph" => 4
                ]
            ];
            $items[] = $item;
        }

        $result['items'] = $items;
        return $result;
    }

}
