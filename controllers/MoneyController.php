<?php

namespace app\controllers;

use yii\rest\ActiveController;

class MoneyController extends ActiveController
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

	public function actions() {
	    $actions = parent::actions();
	    unset($actions['create']);
	    return $actions;
	}

    public $modelClass = 'app\models\Money';

    public function behaviors()
    {
        // remove rateLimiter which requires an authenticated user to work
        $behaviors = parent::behaviors();
        unset($behaviors['rateLimiter']);
        return $behaviors;
    }

    public function actionTurnovers($ledgerId, $closed=0) {
    	$turnovers = \Yii::$app->db->createCommand('SELECT * FROM Money WHERE closed='.$closed.' AND ledger_id = '.$ledgerId.' ORDER BY id asc')->queryAll();

    	// return $turnovers;
		foreach ($turnovers as $n=>$turnover) {
			if ($n==0) {
				$turnovers[0]['balance'] = $turnover['sum'];
			}
			else {
				$turnovers[$n]['balance'] = $turnovers[$n]['balance'] ?? 0;
				$turnovers[$n-1]['balance'] = $turnovers[$n-1]['balance'] ?? 0;

				$turnovers[$n]['balance'] += $turnovers[$n - 1]['balance'] + $turnover['sum'];
			}
		}

		$turnoversSorted = array();
		$c = count($turnovers);

		for($i = $c; $i--; $i>0) {
			$turnoversSorted[] = $turnovers[$i];
		}

		return $turnoversSorted;
    }

    public function actionCreate() {
    	$model = new \app\models\Money;
    	$model->load(\Yii::$app->getRequest()->getBodyParams(), '');
		$turnovers = $this->actionTurnovers($model->ledger_id, 0);

    	$model->beginner = $turnovers[0]['balance'];

	    if ($model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
			return $model->getAttributes();

        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }
    }


}