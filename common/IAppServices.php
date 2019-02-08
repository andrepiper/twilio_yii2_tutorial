<?php

namespace app\common;


interface IAppServices
{
    function generatePaymentUrl($params);

    function makePayment($payload);

    function sendAlert($from,$to, $payload);
}