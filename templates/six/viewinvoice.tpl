<!DOCTYPE html>
<html lang={$LANG.locale}>
<head>
    <meta charset="{$charset}" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$companyname} - {$pagetitle}</title>

    <link href="{$WEB_ROOT}/templates/{$template}/css/all.min.css" rel="stylesheet">
    <link href="{$WEB_ROOT}/templates/{$template}/css/invoice.css" rel="stylesheet">

</head>
<body>

    <div class="container-fluid invoice-container">

        {if $invalidInvoiceIdRequested}

            {include file="$template/includes/panel.tpl" type="danger" headerTitle=$LANG.error bodyContent=$LANG.invoiceserror bodyTextCenter=true}

        {else}

            <div class="row invoice-header">
                <div class="invoice-col">

                    {if $logo}
                        <p><a href="home"><img src="{$logo}" title="{$companyname}" /></a></p>
                    {else}
                        <h2>{$companyname}</h2>
                    {/if}
                    <h3>{$pagetitle}</h3>

                </div>
                <div class="invoice-col text-center">

                    <div class="invoice-status">
                        {if $status eq "Draft"}
                            <span class="draft">{$LANG.invoicesdraft}</span>
                        {elseif $status eq "Unpaid"}
                            <span class="unpaid">{$LANG.invoicesunpaid}</span>
                        {elseif $status eq "Paid"}
                            <span class="paid">{$LANG.invoicespaid}</span>
                        {elseif $status eq "Refunded"}
                            <span class="refunded">{$LANG.invoicesrefunded}</span>
                        {elseif $status eq "Cancelled"}
                            <span class="cancelled">{$LANG.invoicescancelled}</span>
                        {elseif $status eq "Collections"}
                            <span class="collections">{$LANG.invoicescollections}</span>
                        {elseif $status eq "Payment Pending"}
                            <span class="paid">{$LANG.invoicesPaymentPending}</span>
                        {/if}
                    </div>
                   
                    <div class="small-text">
                        {$LANG.invoicesdatecreated}: {$date}
                         {if $status eq "Unpaid" || $status eq "Draft"}
                            <br>
                            {$LANG.invoicesdatedue}: {$datedue}
                        {/if}
                    </div>
                    
                </div>
            </div>

            <hr>

            {if $paymentSuccessAwaitingNotification}
                {include file="$template/includes/panel.tpl" type="success" headerTitle=$LANG.success bodyContent=$LANG.invoicePaymentSuccessAwaitingNotify bodyTextCenter=true}
            {elseif $paymentSuccess}
                {include file="$template/includes/panel.tpl" type="success" headerTitle=$LANG.success bodyContent=$LANG.invoicepaymentsuccessconfirmation bodyTextCenter=true}
            {elseif $pendingReview}
                {include file="$template/includes/panel.tpl" type="info" headerTitle=$LANG.success bodyContent=$LANG.invoicepaymentpendingreview bodyTextCenter=true}
            {elseif $paymentFailed}
                {include file="$template/includes/panel.tpl" type="danger" headerTitle=$LANG.error bodyContent=$LANG.invoicepaymentfailedconfirmation bodyTextCenter=true}
            {elseif $offlineReview}
                {include file="$template/includes/panel.tpl" type="info" headerTitle=$LANG.success bodyContent=$LANG.invoiceofflinepaid bodyTextCenter=true}
            {/if}

            <div class="row">
                <div class="invoice-col right">
                    <strong>{$LANG.invoicespayto}</strong>
                    <address class="small-text">
                        {$payto}
                    </address>
                </div>
                <div class="invoice-col">
                    <strong>{$LANG.invoicesinvoicedto}</strong>
                    <address class="small-text">
                        <a href='clientarea.php?action=details'>Editar dados</a><br>
                        {if $clientsdetails.companyname}{$clientsdetails.companyname}<br />{/if}
                        {$clientsdetails.firstname} {$clientsdetails.lastname}<br />
                        {$clientsdetails.address1}, {$clientsdetails.address2}<br />
                        {$clientsdetails.city}, {$clientsdetails.state}, {$clientsdetails.postcode}<br />
                        {$clientsdetails.country}
                        {if $customfields}
                        <br /><br />
                        {foreach from=$customfields item=customfield}
                        {$customfield.fieldname}: {$customfield.value}<br />
                        {/foreach}
                        {/if}

                    </address>
                </div>
            </div>

            {if $manualapplycredit}
                <div class="panel panel-success">
                    <div class="panel-heading">
                        <h3 class="panel-title"><strong>{$LANG.invoiceaddcreditapply}</strong></h3>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="{$smarty.server.PHP_SELF}?id={$invoiceid}">
                            <input type="hidden" name="applycredit" value="true" />
                            {$LANG.invoiceaddcreditdesc1} <strong>{$totalcredit}</strong>. {$LANG.invoiceaddcreditdesc2}. {$LANG.invoiceaddcreditamount}:
                            <div class="row">
                                <div class="col-xs-8 col-xs-offset-2 col-sm-4 col-sm-offset-4">
                                    <div class="input-group">
                                        <input type="text" name="creditamount" value="{$creditamount}" class="form-control" />
                                        <span class="input-group-btn">
                                            <input type="submit" value="{$LANG.invoiceaddcreditapply}" class="btn btn-success" />
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            {/if}

            {if $notes}
                {include file="$template/includes/panel.tpl" type="info" headerTitle=$LANG.invoicesnotes bodyContent=$notes}
            {/if}

            <div class="panel panel-default" id="pagamento">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong>{$LANG.invoicelineitems}</strong></h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <td><strong>{$LANG.invoicesdescription}</strong></td>
                                    <td width="20%" class="text-center"><strong>{$LANG.invoicesamount}</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach from=$invoiceitems item=item}
                                    <tr>
                                        <td>{$item.description}{if $item.taxed eq "true"} *{/if}</td>
                                        <td class="text-center">{$item.amount}</td>
                                    </tr>
                                {/foreach}
                                <tr>
                                    <td class="total-row text-right"><strong>{$LANG.invoicessubtotal}</strong></td>
                                    <td class="total-row text-center">{$subtotal}</td>
                                </tr>
                                {if $taxrate}
                                    <tr>
                                        <td class="total-row text-right"><strong>{$taxrate}% {$taxname}</strong></td>
                                        <td class="total-row text-center">{$tax}</td>
                                    </tr>
                                {/if}
                                {if $taxrate2}
                                    <tr>
                                        <td class="total-row text-right"><strong>{$taxrate2}% {$taxname2}</strong></td>
                                        <td class="total-row text-center">{$tax2}</td>
                                    </tr>
                                {/if}
                                <tr>
                                    <td class="total-row text-right"><strong>{$LANG.invoicescredit}</strong></td>
                                    <td class="total-row text-center">{$credit}</td>
                                </tr>
                                <tr>
                                    <td class="total-row text-right"><strong>{$LANG.invoicestotal}</strong></td>
                                    <td class="total-row text-center">{$total}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div style="text-align: center;margin-bottom: 70px;">
                <div class="row">
                    <strong>{$LANG.paymentmethod}</strong><br>
                    <span class="small-text">
                        {if $status eq "Unpaid" && $allowchangegateway}
                            <form method="post" action="{$smarty.server.PHP_SELF}?id={$invoiceid}#pagamento" class="form-inline">
                                {$gatewaydropdown}
                            </form>
                        {else}
                            {$paymentmethod}
                        {/if}
                    </span>
                </div><br><br>
                {if $status eq "Unpaid" || $status eq "Draft"}
                    <div class="payment-btn-container" align="center">
                        {$paymentbutton}
                    </div>
                {/if}
            </div>

            {if $taxrate}
                <p>* {$LANG.invoicestaxindicator}</p>
            {/if}
            <div class="transactions-container small-text">
                <div class="table-responsive">
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <td class="text-center"><strong>{$LANG.invoicestransdate}</strong></td>
                                <td class="text-center"><strong>{$LANG.invoicestransgateway}</strong></td>
                                <td class="text-center"><strong>{$LANG.invoicestransid}</strong></td>
                                <td class="text-center"><strong>{$LANG.invoicestransamount}</strong></td>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$transactions item=transaction}
                                <tr>
                                    <td class="text-center">{$transaction.date}</td>
                                    <td class="text-center">{$transaction.gateway}</td>
                                    <td class="text-center">{$transaction.transid}</td>
                                    <td class="text-center">{$transaction.amount}</td>
                                </tr>
                            {foreachelse}
                                <tr>
                                    <td class="text-center" colspan="4">{$LANG.invoicestransnonefound}</td>
                                </tr>
                            {/foreach}
                            <tr>
                                <td class="text-right" colspan="3"><strong>{$LANG.invoicesbalance}</strong></td>
                                <td class="text-center">{$balance}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            
        {/if}
    </div>
</body>
</html>
