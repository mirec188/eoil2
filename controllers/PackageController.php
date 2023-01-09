<?php

namespace app\controllers;

use yii\rest\ActiveController;
use \Yii;
use app\models\ProductPack;
use yii\data\ActiveDataProvider;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

class PackageController extends ActiveController
{

	public $modelClass = 'app\models\Product';

    /**
     * @inheritdoc
     */
    // public function behaviors()
    // {
    //     $behaviors = parent::behaviors();
    //     $behaviors['authenticator'] = [
    //         'class' => \sizeg\jwt\JwtHttpBearerAuth::class,
    //     ];

    //     return $behaviors;
    // }


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

        Yii::debug(Yii::$app->request->getRawBody());

        $packQuery->andWhere('`ProductHasPack`.active = 1');
        $packQuery->andWhere("`ProductHasPack`.productId in (SELECT productId FROM ProductHasCategory WHERE categoryId in (3,13))");

        if ($this->request->getMethod() == 'POST' && $json = json_decode(Yii::$app->request->getRawBody(), true)) {
            $this->updateCondition($packQuery, $json);
        }
        // var_dump($packQuery->createCommand()->getRawSql());die();

        $result = [
            "items" => [],
        ];
        $items = [];
        
        $packProvider = new ActiveDataProvider([
            'query' => $packQuery,
            'pagination' => [
                'pageSize' => isset($_GET['limit']) && $_GET['limit'] ? $_GET['limit'] : 12,
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

        $result['pagination'] = $this->getPagination($packProvider);
        return $result;
    }

    public function actionView($id) {
        $pack = ProductPack::find()->where(['id' => $id])->one();
        return $this->getPackItemDetail($pack);
    }

    private function getPackItemDetail($pack) {
        $categories = $this->getCategories($pack);
        $specifications = $this->getSpecifications($pack);
        $viscosity = null;
        if ($pack->product->viscosity) {
            $viscosity = [
                "id"=>$pack->product->viscosity->id,
                "name"=>$pack->product->viscosity->name,
            ];
        }

        $item = [
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
            "eoilUrl" => $pack->getEoilUrl(),
            "image"=> [
                "originalUrl"=>$pack->getPhtoUrl("original"),
                "thumbnailUrl"=>$pack->getPhtoUrl("300x500"),
            ],
            "product"=> [
                "name"=>$pack->product->name,
                "fullName"=>$pack->product->getFullName(),
                "description"=>$pack->product->info,
                "categories"=>$categories,
                "producer"=> [
                    "id"=>$pack->product->producer->id,
                    "name"=>$pack->product->producer->name,
                ],
                "viscosity"=> $viscosity,
    
                "specifications"=> $specifications,
                "additionalAttributes"=> $this->getAdditionalAttributes($pack),
            ],
        ];

        return $item;
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
            "eoilUrl" => $pack->getEoilUrl(),
            "image"=> [
                "originalUrl"=>$pack->getPhtoUrl("original"),
                "thumbnailUrl"=>$pack->getPhtoUrl("300x500"),
            ],
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

        if (isset($json['category'])) {
            $packQuery->andWhere("productId in (SELECT productId FROM ProductHasCategory WHERE categoryId in (".implode(",", $json['category'])."))");
        }

        if (isset($json['viscosity'])) {
            $packQuery->joinWith('product')->andWhere('viscosityId in ('.implode(',', $json['viscosity']).')');
        }
        if (isset($json['specification'])) {
            $packQuery->andWhere("productId in (SELECT productId FROM SpecificationHasProduct where specificationId in (".implode(',', $json['specification'])."))");
        }

        if (isset($json['fulltext'])) {
            $packQuery = $this->updateFulltextCondition($packQuery, $json['fulltext']);
        }
       
        if (isset($json['attributes']) && !empty($json['attributes'])) {
            $attributeWheres = [];
            foreach ($json['attributes'] as $attribute) {
                $ids = sprintf("'%s'", implode("','", $attribute['values'] ) );
                $attributeWheres[] = "pa.productAttributeTypeId= ".$attribute['id']. " AND value IN (".$ids.")";
            }

            $count = count($json['attributes']);
            $packQuery->andWhere("productId IN (
                SELECT productId
                FROM ProductAttribute pa
                WHERE ".implode(" OR ", $attributeWheres)."
                GROUP BY productId
                HAVING COUNT(DISTINCT pa.productAttributeTypeId) = ".$count."
            )");
        }

  

    }

    private function updateFulltextCondition($packQuery, $fulltext) {
        $fulltext = trim($fulltext);

        $words = explode(' ', $fulltext);
        foreach ($words as $word) {
            if ($word) {
                $word = trim(strtolower(str_replace('-', '', $word)));
                $criteriaString = "(Product.name LIKE '%{$word}%' OR Producer.name LIKE '%{$word}%' OR 
                Product.name LIKE '%{$word}%' OR Producer.name LIKE '%{$word}%' OR 
                LOWER(REPLACE(Viscosity.name,'-', '')) LIKE '%{$word}%' OR LOWER(REPLACE(Viscosity.name,'-', '')) LIKE '%{$word}%')";
                $packQuery->joinWith('product')->joinWith('product.producer')->joinWith('product.viscosity')->andWhere($criteriaString);
            }
        }

        return $packQuery;
    }

    private function getFilterViscosity($packQuery) {
        $rawSql = $packQuery->createCommand()->getRawSql();
        $sql = str_replace('SELECT `ProductHasPack`.*', 'SELECT `ProductHasPack`.id', $rawSql);
        $sql = str_replace('SELECT *' , 'SELECT `ProductHasPack`.id', $sql);
        
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
        $sql = str_replace('SELECT *' , 'SELECT `ProductHasPack`.id', $sql);
        $sql1 = "


        select s.id, s.name, count(*) as count from ProductHasPack php 
            LEFT JOIN SpecificationHasProduct shp ON php.productId = shp.productId
            LEFT JOIN Product p ON p.id = php.productId
            LEFT JOIN Specification s ON s.id=shp.specificationId WHERE php.id IN (
                $sql
            ) AND php.active=1
        GROUP BY s.id
        ";
        
        return Yii::$app->db->createCommand($sql1)->queryAll();
        
    }

    private function getFilterAttributeTypes($packQuery) {

        $attributeTypes = $this->getAttributeTypes();
        if (!$attributeTypes) return [];
        $baseIds = implode(",", $attributeTypes);
     
        $rawSql = $packQuery->createCommand()->getRawSql();
        $sql = str_replace('SELECT `ProductHasPack`.*', 'SELECT `ProductHasPack`.id', $rawSql);
        $sql = str_replace('SELECT *' , 'SELECT `ProductHasPack`.id', $sql);

        $q1 = " SELECT pa.productAttributeTypeId as patId, pat.name as name, pa.value as value, count(*) as count 
                FROM ProductAttribute pa 
                RIGHT JOIN ProductAttributeType pat ON pa.productAttributeTypeId = pat.id
                JOIN ProductHasPack php ON php.productId = pa.productId
                WHERE pa.value IS NOT NULL AND productAttributeTypeId 
                in ($baseIds)
                AND php.id in (
                    SELECT id
                    FROM (
                        $sql
                    ) as x
                ) group by productAttributeTypeId, pat.name, value
            ";
        $data = \Yii::$app->db->createCommand($q1)->queryAll();
        $values = [];
        $result = [];

        $tmp = [];
        $foundValues = [];
        foreach ($data as $d) {
           
            $tmp[$d['patId']]['id'] = $d['patId'];
            $tmp[$d['patId']]['name'] = $d['name'];

            $t['value'] = $d['value'];
            $t['count'] = $d['count'];
            if ($d['value']!=null) {
                $tmp[$d['patId']]['values'][]= $t;
                $foundValues[$d['patId']][] = $d['value'];
            }
        }

       
        $allTypes = $this->getBaseAttributeTypeValues($baseIds);
        foreach ($allTypes as $otherType) {

            if (!isset($tmp[$otherType['id']])) {
                $tmp[$otherType['id']]['id'] = $otherType['id'];
                $tmp[$otherType['id']]['name'] = $otherType['name'];
                $tmp[$otherType['id']]['values'] = [];
                $foundValues[$otherType['id']] = [];
            }

            
            if(isset($foundValues[$otherType['id']]) && !in_array($otherType['value'], $foundValues[$otherType['id']])) {
                $t['value'] = $otherType['value'];
                $t['count'] = 0;
                if ($otherType['value']!=null) {
                    $tmp[$otherType['id']]['values'][]= $t;
                }
            }
        }

        foreach ($tmp as $d) {
            $result[] = $d;
        }

        return $result;
    }

    private function getAttributeTypes() {
        $q = "SELECT id FROM ProductAttributeType where type in ('EOIL_PLASTICKEMAZIVA', 'PLASTICKEMAZIVA')";
        $data = \Yii::$app->db->createCommand($q)->queryColumn();
        return $data;
    }

    private function getPagination($packProvider) {
        return [
            "page"=>$packProvider->pagination->page+1,
            "pageCount"=>$packProvider->pagination->pageCount,
            "pageSize"=>$packProvider->pagination->pageSize,
            "itemCount"=>$packProvider->pagination->totalCount,
        ];
    }

    private function getBaseAttributeTypeValues($baseIds) {
        $q = "
        select pat.name as name, pat.id as id, pa.value as value   FROM ProductAttribute pa 
        JOIN ProductAttributeType pat ON pat.id=pa.productAttributeTypeId
        where pat.id in (1,3,5) and pa.productId in (
        SELECT productId FROM ProductHasPack where active=1 AND productId in (
        SELECT productId FROM ProductHasCategory WHERE categoryId in (3,13)
        )
        )
        group by pat.name, pat.id, pa.value;
        ";
        $data = \Yii::$app->db->createCommand($q)->queryAll();
        return $data;
    }
    

}
