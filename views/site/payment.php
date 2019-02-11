<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

use app\common\AppResponse;
use app\common\AppServices;

$this->title = 'Twilio + Paypal, Payment Alerts - Payment status';

?>


<?php if($payload->getSuccess()) { ?>

    <div class="container">
        <div class="container">
            <table id="cart" class="table table-hover table-condensed">
                <thead>
                <tr>
                    <th style="width:50%">Product</th>
                    <th style="width:10%"></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td data-th="Product">
                        <div class="row">
                            <div class="col-sm-2 hidden-xs"><img src="../web/img/rating.png" alt="..." class="img-responsive"></div>
                            <div class="col-sm-10">
                                <h4 class="nomargin text-success">Congratulations on buying this fantastic product</h4>
                                <h4 class="nomargin text-info">Receipt #: <?php echo $payload->getPayload()->getId(); ?></h4>
                                <?php if($sms->getSuccess()) { ?>
                                    <h5 class="nomargin text-info">Sms Alert sent</h5>
                                <?php }?>
                            </div>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

<?php }