<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ImportSupplierPricesController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($path)
    {   
        
        
        // print_r($result);

        $data = $this->parseCsvFile($path);

        $this->import($data);
    }


    private function import($data) {
        $spService = new \app\services\SupplierPackService();

        foreach ($data as $row) {
            $pp = \app\models\ProductPack::findByEid($row['eIdType'], $row['eId']);

            if (!$pp) {
                echo "skipping ".$row['eId'].' with type id '.$row['eIdType']." - could not find product pack \n";
            } else {
                foreach ($pp as $p) {
                    echo 'found '.$p->id.' for '.$row['eId'].' with type id '.$row['eIdType']."\n";
                
                    $result = $spService->updatePackSupplierPrice($p,$row['supplierId'],$row['price']);
                    $result2 = $spService->updatePackSupplier($p, $row['supplierId']);
                }

            }
            
        }
    }

    private function parseCsvFile($file) {

        $return = array();
        $handle = fopen($file, "r");
        if ($handle) {
            while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
               $tmp['eId'] = $data[0];
               $tmp['price'] = $this->sanitazePrice($data[1]);
               $tmp['supplierId'] = $data[2];
               $tmp['eIdType'] = $data[3];

               $return[] = $tmp;
            }
            fclose($handle);
            return $return;
        }
        return false;
    }

    private function sanitazePrice($price) {
        return str_replace(',', '.', $price);
    }

}
