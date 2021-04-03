<?php

namespace app\controllers;

use yii\rest\ActiveController;
use \Yii;

class ProductController extends ActiveController
{
	protected function verbs() {
		$verbs = parent::verbs();
		$verbs =  [
			'index' => ['GET', 'POST', 'HEAD'],
			'view' => ['GET', 'HEAD'],
			'create' => ['POST'],
			'update' => ['PUT', 'PATCH']
		];
	  	return $verbs;
	}

	public $modelClass = 'app\models\Product';

	public function actions() {
	    $actions = parent::actions();
	    // unset($actions['create']);
	    return $actions;
	}


    public function behaviors()
    {
        // remove rateLimiter which requires an authenticated user to work
        $behaviors = parent::behaviors();
        unset($behaviors['rateLimiter']);
        return $behaviors;
    }

    public function actionDetail($productId) {
    	$product = \app\models\Product::find($productId)->one();
    	$viscosity = $product->viscosity;

    	return [
			'id'=>$product->id,
    		'name'=>$product->name,
    		'fullName'=>$product->getFullName(true, true),
    		'active'=>$product->isActive(),
    		'categories'=>[],
    		'viscosity'=>[
    			'id'=>$viscosity->id,
    			'name'=>$viscosity->name
    		]
    	];
    }

    public function actionFindAll($name = false, $fulltext = false, $viscosity = false, $categories = false, $limit = 5) {

        $suggests = array();
        $counter = 0;
        // $v = UrlManager::getSearchView();
        $term = trim($fulltext);
        $nameLikeCondition = '';
        $words = explode(' ', $term);
        foreach ($words as $n=>$word) {

            $word = trim($word);
            $word = strtolower(str_replace('-', '', $word));
            if ($word) {
                if ($n > 0) $nameLikeCondition .= ' AND ';
                $nameLikeCondition .= "(Producer.name LIKE '%$word%' OR REPLACE(Viscosity.name, '-', '') LIKE '%$word%' OR Product.nameOld LIKE '%$word%' OR Product.name LIKE '%$word%')";
            }
        }

        $productsQ = "
            SELECT
              Product.id,
              CONCAT(IFNULL(Producer.name, ''), ' ', Product.name, ' ', IFNULL(Viscosity.name, '')) as nm,
              CONCAT(IFNULL(Producer.name, ''), ' ', Product.nameOld, ' ', IFNULL(Viscosity.name, '')) as nmOld,
              Product.nameOld as nameOld
            FROM Product
            LEFT JOIN Producer ON Product.producerId = Producer.id
            LEFT JOIN Viscosity ON Product.viscosityId = Viscosity.id
            WHERE $nameLikeCondition
            GROUP BY Product.id
            LIMIT $limit 
        ";

        $productsR = Yii::$app->db->createCommand($productsQ)->queryAll();


        foreach ($productsR as $product) {

            if ($product['nameOld'] && \app\components\Helpers::stringContains(strtolower($term), strtolower($product['nmOld']))) {
                $suggests[] = array(
                    'id' => str_replace('  ', ' ', $product['id']),
                    'label' => str_replace('  ', ' ', $product['nmOld']),
                    'name'  =>  $product['nmOld'],
                    'desc'  => '(produkt)',
                    // 'href'  => UrlManager::createProductUrl(array('id'=>(isset($product['phpId'])) ? $product['phpId'] : 0, 'productName'=>$product['nm']))
                );
                $counter++;
            }

            $suggests[] = array(
                'id' => $product['id'],
                'label' => str_replace('  ', ' ', $product['nm']),
                'name'  =>  $product['nm'],
                'desc'  => '(produkt)',
                // 'href'  => UrlManager::createProductUrl(array('id'=>(isset($product['phpId'])) ? $product['phpId'] : 0, 'productName'=>$product['nm']))
            );
            $counter++;
        }

        return $suggests;
    }

    public function actionAlternatives($productId) {
    	$q = "SELECT * FROM product_alternative WHERE product_id=$productId";

    	$ids = \Yii::$app->db->createCommand($q)->queryAll();
    	if (!$ids) return [];

    	$result = [];
    	foreach ($ids as $alternativeId) {
    		$alternative = $this->actionDetail($alternativeId);
    		$result[] = $alternative;
    	}

    	return $result;

    }

}