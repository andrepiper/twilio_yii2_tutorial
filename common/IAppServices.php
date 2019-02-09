<?php

namespace app\common;


interface IAppServices
{
    function generatePaymentUrl($params, $callbackUrl);

    function makePayment($payload);

    function sendAlert($from,$to, $payload);
}
