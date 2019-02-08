<?php

namespace app\controllers;

use app\common\AppResponse;
use app\common\AppServices;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post']
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        /**
         * Init vars and get success from query param
         */
        $paypalKey = \Yii::$app->params['paypalKey'];
        $paypalSecret = \Yii::$app->params['paypalSecret'];
        $twilioSID = \Yii::$app->params['twilioSID'];
        $twilioToken = \Yii::$app->params['twilioToken'];
        $from = \Yii::$app->params['twilioFromNumber'];

        $success = Yii::$app->request->get('success');
        $response = new AppResponse();
        $services = new AppServices($paypalKey,$paypalSecret,$twilioSID,$twilioToken);

        /**
         * If a post operation was done
         */
        if (Yii::$app->request->post())
        {
            $paypalPayment = [
                'method'=>'paypal',
                'intent'=>'sale',
                'order'=>[
                    'description'=>'Payment description',
                    'subtotal'=>150,
                    'shippingCost'=>0,
                    'total'=>150,
                    'currency'=>'USD',
                    'items'=>[
                        [
                            'name'=>'Super Awesome Product',
                            'price'=>150,
                            'quantity'=>1,
                            'currency'=>'USD'
                        ]
                    ]

                ]
            ];

            $response = $services->generatePaymentUrl($paypalPayment);

            if($response->getSuccess())
            {
                return $this->redirect($response->getPayload()->getHeaders()->get("location"));
            }
        }
        /*
         * Paypal callback
         */
        else if($success && $success == 'true')
        {
            $paymentId = Yii::$app->request->get('paymentId');
            $token = Yii::$app->request->get('token');
            $payerId = Yii::$app->request->get('PayerID');

            $payload = [
                'paymentId'=>$paymentId,
                'success'=>$success,
                'token'=>$token,
                'payerId'=>$payerId,
                'order'=>[
                    'description'=>'Payment description',
                    'subtotal'=>150,
                    'shippingCost'=>0,
                    'total'=>150,
                    'currency'=>'USD',
                    'items'=>[
                        [
                            'name'=>'Super Awesome Product',
                            'price'=>150,
                            'quantity'=>1,
                            'currency'=>'USD'
                        ]
                    ]

                ]
            ];

            $response = $services->makePayment($payload);
            if($response->getSuccess())
            {
                $smsPayload = $services->sendAlert($from,"+1251", "PayPal order completed. Receipt #: ".$response->getPayload()->getId()." Total $:".$payload['order']['total'].' USD');
                return $this->render('index',['payload'=>$response, 'sms'=>$smsPayload]);
            }
        }
        /**
         * load index action normally
         */
        return $this->render('index', ['payload'=>$response]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
