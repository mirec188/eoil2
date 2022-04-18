<?php

namespace app\controllers;

use yii\rest\ActiveController;
use Yii;
use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;
use app\models\Email;
use app\models\User;

class AuthController extends ActiveController

{
    public $modelClass = 'app\models\User';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
            'optional' => [
                'login',
            ],
        ];

        return $behaviors;
    }

     /**
     * @return \yii\web\Response
     */
    public function actionLogin($username, $password)
    {
            /** @var Jwt $jwt */
        $jwt = Yii::$app->jwt;
        $signer = $jwt->getSigner('HS256');
        $key = $jwt->getKey();
        $time = time();
        
        $userId = $this->getUserId($username, $password);
        if (!$userId) {
            throw new \yii\web\UnauthorizedHttpException('Invalid username or password');
        }

        // Adoption for lcobucci/jwt ^4.0 version
        $token = $jwt->getBuilder()
            ->issuedBy('http://api.eoil.sk')// Configures the issuer (iss claim)
            ->permittedFor('http://api.eoil.sk')// Configures the audience (aud claim)
            ->identifiedBy('4f1g23a12aa', true)// Configures the id (jti claim), replicating as a header item
            ->issuedAt($time)// Configures the time that the token was issue (iat claim)
            ->expiresAt($time + 3600)// Configures the expiration time of the token (exp claim)
            ->withClaim('uid', $userId)// Configures a new claim, called "uid"
            ->getToken($signer, $key); // Retrieves the generated token

        return $this->asJson([
            'token' => (string)$token,
        ]);
    }

    private function getUserId($username, $password) {
        $email = \app\models\Email::find()->where('email = :email', array('email' => $username))->one();
        
        if ($email instanceof Email && $email->user instanceof User) {
            
            $user = $email->user;
            
            if ($user->active == false) {
                return false;
            }
            
            if ($user->validatePassword($password)) {
                return $user->id;
            };
            
        }

        return null;
    }


	
}