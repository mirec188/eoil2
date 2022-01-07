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
        unset($actions['view']);
	    return $actions;
	}

    protected function verbs() {
        $verbs = parent::verbs();
        
        $verbs['index'] = ['POST', 'GET', 'HEAD']; //methods you need in action
        
        return $verbs;
    }

    public function actionIndex() {

        $packQuery = ProductPack::find();

        $packQuery->andWhere('active = 1');

        if ($this->request->getMethod() == 'POST' && $json = json_decode(Yii::$app->request->getRawBody(), true)) {
            $this->updateCondition($packQuery, $json);
        }

        $result = [
            "items" => [],
        ];
        $items = [];
        
        $packProvider = new ActiveDataProvider([
            'query' => $packQuery,
            'pagination' => [
                'pageSize' => isset($_GET['limit']) && $_GET['limit'] ? $_GET['limit'] : 10,
            ],
        ]);

        foreach ($packProvider->getModels() as $pack) {
            $item = $this->getPackItem($pack);
            $items[] = $item;
        }

        $result['items'] = $items;
        $result['filterViscosity'] = $this->getFilterViscosity($packQuery);
        $result['filterSpecification'] = $this->getFilterSpecification($packQuery);
        $result['filterAttributeTypes'] = $this->getFilterAttributeTypes($packQuery);
        return $result;
    }

    public function actionView($id) {
        $pack = ProductPack::find()->where(['id' => $id])->one();
        return $this->getPackItemDetail($pack);
    }

    private function getPackItemDetail($pack) {
        $categories = $this->getCategories($pack);
        $specifications = $this->getSpecifications($pack);
        return $item = [
            'id'=>$pack->id,
            'fullName'=>$pack->getFullName(true, true),
            'amount'=>$pack->pack->amount,
            'bigPackage'=>$pack->pack->large,
            'bigOnly'=>$pack->pack->bigOnly ? true : false,
            "price"=>[
                "price" => $pack->getPriceValue(1),
                "priceDph" => $pack->getPriceValueDph(1),
                "priceSum" => $pack->getFullPriceSum(1),
                "priceSumDph" => $pack->getFullPriceSumDph(1)
            ],
            "eoilUrl" => "",
            "product"=> [
                "name"=>$pack->product->name,
                "fullName"=>$pack->product->getFullName(),
                "description"=>$pack->product->info,
                "categories"=>$categories,
                "producer"=> [
                    "id"=>$pack->product->producer->id,
                    "name"=>$pack->product->producer->name,
                ],
    
                "viscosity"=> [
                    "id"=>$pack->product->viscosity->id,
                    "name"=>$pack->product->viscosity->name,
                ],
    
                "specifications"=> $specifications,
                "additionalAttributes"=> $this->getAdditionalAttributes($pack),
            ],
        ];
    }

    private function getPackItem($pack) {
        $categories = $this->getCategories($pack);
        $specifications = $this->getSpecifications($pack);
        $additionalAttributes = $this->getAdditionalAttributes($pack);
        $viscosity = $this->getViscosity($pack);
        return $item = [
            'id'=>$pack->id,
            'fullName'=>$pack->getFullName(true, true),
            'amount'=>$pack->pack->amount,
            'bigPackage'=>$pack->pack->large,
            'bigOnly'=>$pack->pack->bigOnly ? true : false,
            "price"=>[
                "price" => $pack->getPriceValue(1),
                "priceDph" => $pack->getPriceValueDph(1),
                "priceSum" => $pack->getFullPriceSum(1),
                "priceSumDph" => $pack->getFullPriceSumDph(1)
            ],
            "eoilUrl" => "",
            "product"=> [
                "name"=>$pack->product->name,
                "fullName"=>$pack->product->getFullName(),
                "categories"=>$categories,
                "producer"=> [
                    "id"=>$pack->product->producer->id,
                    "name"=>$pack->product->producer->name,
                ],
    
                "viscosity"=> $viscosity,
    
                "specifications"=> $specifications,

                "additionalAttributes"=> $additionalAttributes
            ],
        ];
    }

    private function getAdditionalAttributes($pack) {
        $result = [];
        foreach ($pack->product->productAttributes as $attribute) {
            $result[] = [
                "id"=>$attribute->id,
                "name"=>$attribute->type->name,
                "value"=>$attribute->value,
            ];
        }
        return $result;
    }

    private function getSpecifications($pack) {
        $specifications = [];
        foreach ($pack->product->specifications as $specification) {
            $specifications[] = [
                "id"=>$specification->id,
                "name"=>$specification->name
            ];
        }
        return $specifications;
    }

    private function getViscosity($pack) {
        if ($pack->product && $pack->product->viscosity) return [
            "id"=>$pack->product->viscosity->id,
            "name"=>$pack->product->viscosity->name,
        ];
    }

    private function getCategories($pack) {
        $categories = $pack->product->getCategories();

        $result = [];
        foreach ($categories as $category) {
            if ($category->parentId == 0) {
                $c = [
                    "id"=>$category->id,
                    "name"=>$category->name,
                    "parentId"=>$category->parentId,
                    "type"=>$category->type,
                    "subcategories"=>[]
                ];

                foreach ($categories as $subcategory) {
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
            }
        }
        
        return $result;
    }

    private function updateCondition($packQuery, $json) {
        // $packQuery->andWhere(['product_id' => $json['productId']]);
        if (isset($json['viscosity'])) {
            $packQuery->joinWith('product')->andWhere('viscosityId in ('.implode(',', $json['viscosity']).')');
        }
        if (isset($json['specification'])) {
            $packQuery->andWhere("productId in (SELECT productId FROM SpecificationHasProduct where specificationId in (".implode(',', $json['specification'])."))");
        }
       
        if (isset($json['attributes'])) {
            $attributeWheres = [];
            foreach ($json['attributes'] as $attribute) {
                $attributeWheres[] = "pa.id = ".$attribute['id']. " AND value IN (".implode(",", $attribute['values']).")";
            }
            $packQuery->andWhere("productId IN ( ".
                "SELECT productId FROM ProductAttribute pa ".
                "JOIN ProductAttributeType pat ON pat.id=pa.productAttributeTypeId ".
                "WHERE (".implode(" OR ", $attributeWheres).") ".
            ")");
        }
    }

    private function getFilterViscosity($packQuery) {
        $rawSql = $packQuery->createCommand()->getRawSql();
        $sql = str_replace('SELECT `ProductHasPack`.*', 'SELECT `ProductHasPack`.id', $rawSql);
        $sql = str_replace('SELECT *' , 'SELECT `ProductHasPack`.id', $rawSql);
        
        $sql1 = "
            SELECT v.id, v.name, count(*) as count FROM ProductHasPack php 
            LEFT JOIN  Product p ON p.id=php.productId 
            LEFT JOIN  Viscosity v ON v.id = p.viscosityId
            WHERE php.active=1 AND php.id in (".$sql.")
            GROUP BY v.id";
        
        return Yii::$app->db->createCommand($sql1)->queryAll();
    }

    private function getFilterSpecification($packQuery) {
        $result = [];

        $result = [];
        $rawSql = $packQuery->createCommand()->getRawSql();
        $sql = str_replace('SELECT `ProductHasPack`.*', 'SELECT `ProductHasPack`.id', $rawSql);
        $sql = str_replace('SELECT *' , 'SELECT `ProductHasPack`.id', $rawSql);
        
        $sql1 = "
            SELECT s.id, s.name, count(*) as count 
            FROM SpecificationHasProduct shp
            LEFT JOIN Specification s ON s.id=shp.specificationId
            LEFT JOIN ProductHasPack php ON php.productId = shp.productId
            LEFT JOIN Product p ON p.id=php.productId 
            WHERE php.active=1 AND php.id in (".$sql.")
            GROUP BY php.id";
        
        return Yii::$app->db->createCommand($sql1)->queryAll();
        
    }

    private function getFilterAttributeTypes($packQuery) {
        $result = [];
        
        return $result;
    }

    

}