<?php

namespace app\controllers;

use yii\rest\ActiveController;
use \Yii;

class ProductController extends ActiveController
{

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

    	$product = \app\models\Product::find()->where(['id'=>$productId])->one();

    	$viscosity = $product->viscosity;

    	$result= [
			'id'=>$product->id,
    		'name'=>$product->name,
    		'fullName'=>$product->getFullName(true, true),
    		'active'=>$product->isActive(),
    		'categories'=>[],
    		'viscosity'=>$viscosity ? $viscosity->getAttributes() : false
    	];

        return $result;

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
        // var_dump($this->request->isPost);
        // die();
       if ($this->request->isPost) {
           return $this->saveAlternatives($productId);
       }

       if ($this->request->isGet) {
           return $this->getAlternatives($productId);
       }
    }

  
    private function saveAlternatives($productId) {
        $params = \Yii::$app->getRequest()->getBodyParams();

        $tr = Yii::$app->db->beginTransaction();

        $alternativeIds=array_keys($params['alternatives']);
        $alternativeIds[]=9999999;
        $alternativeIdsSql=implode(',', $alternativeIds);
        
        try {
            $q = "DELETE FROM product_alternative WHERE product_id=$productId and alternative_id not in ($alternativeIdsSql)";          
            \Yii::$app->db->createCommand($q)->execute();

            $q = "DELETE FROM product_alternative WHERE alternative_id=$productId and product_id not in ($alternativeIdsSql)";          
            \Yii::$app->db->createCommand($q)->execute();


            foreach ($params['alternatives'] as $alternativeId) {
                $q = "replace INTO product_alternative (product_id, alternative_id) values ($productId, $alternativeId)";
                \Yii::$app->db->createCommand($q)->execute($q);

                $q = "replace INTO product_alternative (product_id, alternative_id) values ($alternativeId, $productId)";
                \Yii::$app->db->createCommand($q)->execute($q);
            }
            $tr->commit();

        } catch (Exception $e) {
            //RollBACK
            $tr->rollBack();
            print_r($e);
            return ['error']['had to rollback'];
        }

        return $this->getAlternatives($productId);
    }

    private function getAlternatives($productId) {
        $ids = $this->getAlternativeIds($productId, true);
        $idsDirect = $this->getAlternativeIds($productId, false);

        $directIds = $idsDirect;
        $indirectIds = array_diff($ids, $idsDirect);

        $result = [];
        if (!$ids) return [];

        foreach ($directIds as $alternativeId) {
            $alternative = $this->actionDetail($alternativeId);
            $alternative['direct'] = true;
            $result[] = $alternative;
        }

        foreach ($indirectIds as $alternativeId) {
            $alternative = $this->actionDetail($alternativeId);
            $alternative['direct'] = false;
            $result[] = $alternative;
        }

        return $result;
    }

    private function getAlternativeIds($productId, $resucrsive = false, $except = null) {
        $exceptSql = "";
        $indirect = false;

        if ($except) {
            $indirect=true;
            $exceptSql = "AND alternative_id not in (".implode(",", $except).")";
        }

        $q = "SELECT distinct alternative_id FROM product_alternative WHERE product_id=$productId $exceptSql";
        
        $ids = \Yii::$app->db->createCommand($q)->queryColumn();
        
        if ($resucrsive) {
            foreach ($ids as $altId) {
                $ids = array_merge($ids, $this->getAlternativeIds($altId, true, array_merge($ids, [$productId])));
            }
        }
    
        return $ids;
    }

}