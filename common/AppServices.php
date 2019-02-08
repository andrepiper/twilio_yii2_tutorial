<?php

namespace app\common;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use Twilio\Rest\Client;
use Yii;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\CreditCard;
use PayPal\Exception\PaypalConnectionException;
use yii\helpers\Url;

class AppServices implements IAppServices
{
    /**
     * @var SDK keys and secrets
     */
    private $_paypalKey, $_paypalSecret, $_twilioSID, $_twilioToken;

    /**
     * @var runtime properties
     */
    private $_paypalContext;

    function __construct($paypalKey, $paypalSecret, $twilioSID, $twilioToken) {

        $this->_paypalKey = $paypalKey;
        $this->_paypalSecret = $paypalSecret;
        $this->_twilioSID = $twilioSID;
        $this->_twilioToken = $twilioToken;

        $this->_paypalContext = new ApiContext(
            new OAuthTokenCredential($this->_paypalKey, $this->_paypalSecret)
        );
    }

    /**
     * @param $params
     * @return AppResponse
     */
    public function generatePaymentUrl($params)
    {
        $response = new AppResponse();
        try
        {
            //make payment url
            $payer = new Payer();
            $payer->setPaymentMethod($params['method']);
            $orderList = [];

            foreach ($params['order']['items'] as $orderItem){
                $item = new Item();
                $item->setName($orderItem['name'])
                    ->setCurrency($orderItem['currency'])
                    ->setQuantity($orderItem['quantity'])
                    ->setPrice($orderItem['price']);
                $orderList[]=$item;
            }

            $itemList = new ItemList();
            $itemList->setItems($orderList);

            $details = new Details();
            $details->setShipping($params['order']['shippingCost'])
                ->setSubtotal($params['order']['subtotal']);

            $amount = new Amount();
            $amount->setCurrency($params['order']['currency'])
                ->setTotal($params['order']['total'])
                ->setDetails($details);

            $transaction = new Transaction();
            $transaction->setAmount($amount)
                ->setItemList($itemList)
                ->setDescription($params['order']['description'])
                ->setInvoiceNumber(uniqid());

            $redirectUrl = Url::to(['/'],true);
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl("$redirectUrl?success=true")
                ->setCancelUrl("$redirectUrl?success=false");

            $payment = new Payment();
            $payment->setIntent($params['intent'])
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions(array($transaction));

            //execute
            $payment->create($this->_paypalContext);
            if($payment != null && $payment->getApprovalLink())
            {
                $paymentUrl = \Yii::$app->controller->redirect($payment->getApprovalLink());

                $response->setMessage("Payment url success");
                $response->setSuccess(true);
                $response->setPayload($paymentUrl);
            }
            else
            {
                $response->setMessage("Payment url failed");
                $response->setSuccess(false);
                $response->setPayload("");
            }
        }catch (\Exception $exception)
        {
            $response->setMessage("Payment url failed, with error message :".$exception->getMessage());
            $response->setSuccess(false);
            $response->setPayload(array());
        }
        return $response;
    }

    public function makePayment($payload)
    {
        $response = new AppResponse();
        try
        {
            $paymentId = $payload['paymentId'];
            $payment = Payment::get($paymentId, $this->_paypalContext);
            $execution = new PaymentExecution();
            $execution->setPayerId($payload['payerId']);
            $transaction = new Transaction();
            $amount = new Amount();
            $details = new Details();
            $details->setShipping($payload['order']['shippingCost'])
                ->setSubtotal($payload['order']['subtotal']);
            $amount->setCurrency($payload['order']['currency']);
            $amount->setTotal($payload['order']['total']);
            $amount->setDetails($details);
            $transaction->setAmount($amount);
            $execution->addTransaction($transaction);

            $payment->execute($execution, $this->_paypalContext);
            $payment = Payment::get($paymentId, $this->_paypalContext);

            if($payment && $payment->getState() == "approved")
            {
                $response->setMessage("Payment was ".$payment->getState());
                $response->setSuccess(true);
                $response->setPayload($payment);
            }
            else
            {
                $response->setMessage("Payment was ".$payment->getState());
                $response->setSuccess(false);
                $response->setPayload("");
            }

        }catch (\Exception $exception)
        {
            $response->setMessage("Payment failed, with error message :".$exception->getMessage());
            $response->setSuccess(false);
            $response->setPayload(array());
        }
        return $response;
    }

    public function sendAlert($from,$to, $payload)
    {
        $response = new AppResponse();
        try
        {
            $client = new Client($this->_twilioSID, $this->_twilioToken);
            $client->messages->create(
                $to,
                array(
                    'from' => $from,
                    'body' => $payload
                )
            );

            $response->setMessage("Message sent to : ".to);
            $response->setSuccess(true);
            $response->setPayload($client);

        }catch (\Exception $exception)
        {
            $response->setMessage("Message failed, with error message :".$exception->getMessage());
            $response->setSuccess(false);
            $response->setPayload(array());
        }
        return $response;
    }
}
?>