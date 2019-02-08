<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

use app\common\AppResponse;
use app\common\AppServices;

$this->title = 'Twilio + Paypal, Payment Alerts';

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

<?php } else { ?>

    <form method="post" enctype="multipart/form-data">
<input id="form-token" type="hidden" name="<?=Yii::$app->request->csrfParam?>" value="<?=Yii::$app->request->csrfToken?>"/>
<div class="container">
    <div class="container">
        <table id="cart" class="table table-hover table-condensed">
            <thead>
            <tr>
                <th style="width:50%">Product</th>
                <th style="width:10%">Price</th>
                <th style="width:8%">Quantity</th>
                <th style="width:22%" class="text-center">Subtotal</th>
                <th style="width:10%"></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td data-th="Product">
                    <div class="row">
                        <div class="col-sm-2 hidden-xs"><img src="../web/img/rating.png" alt="..." class="img-responsive"></div>
                        <div class="col-sm-10">
                            <h4 class="nomargin">Super Awsome Product</h4>
                            <p>Super Awsome Product Description</p>
                        </div>
                    </div>
                </td>
                <td data-th="Price">$150.00</td>
                <td data-th="Quantity">
                    <input type="number" class="form-control text-center" value="1">
                </td>
                <td data-th="Subtotal" class="text-center">150.00</td>
                <td class="actions" data-th="">
                    <button class="btn btn-info btn-sm"><i class="fa fa-refresh"></i></button>
                    <button class="btn btn-danger btn-sm"><i class="fa fa-trash-o"></i></button>
                </td>
            </tr>
            </tbody>
            <tfoot>
            <tr class="visible-xs">
                <td class="text-center"><strong>Total 1.99</strong></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2" class="hidden-xs"></td>
                <td class="hidden-xs text-center"><strong>Total $150.00</strong></td>
                <td><input type="submit" class="btn btn-success btn-block" /></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
</form>

<?php } ?>