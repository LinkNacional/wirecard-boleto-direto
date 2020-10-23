<?php
    /*
        Gateway moipcc Callback
        Desenvolvido por Davi Souza ticket@linknacional.com.br em 22/05/2018
        Atualizado: 22/08/2018
        Versao: 2.1
        Empresa: LINK NACIONAL | MoIP Pagamentos
        ////////// CODIGOS DE ERROS DE CARTAO DE CREDITO MOIP https://suporte.petanjo.com/hc/pt-br/articles/115002963128-O-que-quer-dizer-os-c%C3%B3digos-numericos-do-cancelamento-do-pagamento-no-cart%C3%A3o-
    */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
use WHMCS\Database\Capsule;
// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// FUNCAO PARA SALVAR EM ARQUIVO debug.txt na pasta do callback para ajuda de verificações.
if (!function_exists('log_var')) {
    function log_var($var, $name='', $to_file=false){
        $gatewayParams['callback_whmcs_debug'];
        if ($to_file==true) {
            $txt = @fopen('moip_whmcs_boleto_direto_debug.txt','a');
            if ($txt){
                fwrite($txt, "-----------------------------------\n");
                fwrite($txt, $name."\n");
                fwrite($txt, print_r($var, true)."\n");
                fclose($txt);//
            }
        } else {
             echo '<pre><b>'.$name.'</b>'.
                  print_r($var,true).'</pre>';
        }
      }
}

if($gatewayParams['callback_whmcs_debug']=="on"){
log_var(date("d/m/Y H:i:s")."CHAMOU".$gatewayParams['key_whmcs']."Callback Status:".$gatewayParams['callback_whmcs']."|||",print_r($response, true), true);
}else{
    $txt = @fopen('moip_whmcs_boleto_direto_debug.txt','w+');
    fwrite($txt, $string);
    fclose($fp);
}


///// RETORNO VIA WEBHOOK DO MOIP
if($_GET['key'] == $gatewayParams['key_whmcs'] && $gatewayParams['callback_whmcs'] =="on"){

    //trata a resposta do MOIP, Pega o RAW data da requisição
    $json = file_get_contents('php://input');
    // Converte os dados recebidos
    $response = json_decode($json, true);
    if($gatewayParams['callback_whmcs_debug']=="on"){
        log_var(date("d/m/Y H:i:s")."ENTROU".$gatewayParams['key_whmcs'],print_r($response, true), true);
    }


    $data_hora = date("d/m/Y H:i:s");
    $success = false;
    $status = $response['resource']['order']['status'];

    $id_transacao = explode(":", $response['resource']['order']['ownId']);
    $invoiceId = $id_transacao[0];
    $transactionId = $response['resource']['order']['ownId'];
    $paymentAmount = 0;
    $paymentFee = 0;

    //$response[event]// => ORDER.PAID //$response['event'] == "ORDER.PAID"  ||

    if($response['event'] == "ORDER.NOT_PAID"){
        //log_var("NOT_PAID",print_r($response,true), true);
    }
    if($response['event'] == "ORDER.CREATED"){
        if($gatewayParams['callback_whmcs_debug']=="on"){
            log_var("ORDER.CREATED",print_r($response,true), true);
        }
    }
    if($response['event'] == "PAYMENT.WAITING"){
        if($gatewayParams['callback_whmcs_debug']=="on"){
            log_var("PAYMENT.WAITING",print_r($response,true), true);
        }
    }

    if ($status == "PAID"){

        //log_var("PAID". $status,print_r($response, true), true);
        //$status = $response['resource']['order']['status'];//'AUTHORIZED '.$data->status;
        /// DATA DO STATUS ATUALIZADO $response['resource']['payment']['updatedAt'];
        //$invoiceId = $_POST['invoiceid'];//$id_transacao[0];// ID DA INVOICE NO WHMCS
        //$id_transacao = explode(":", $response['resource']['order']['ownId']);
        //$invoiceId = $id_transacao[0];
        //$transactionId = $response['resource']['order']['payments'][0]['id'];

        $valor = $response['resource']['order']['payments'][0]['amount']['gross'];
        $real = substr($valor,0,-2);
        $cent = substr($valor,-2);
        $paymentAmount = $real.".".$cent;
      
        $valorFee = $response['resource']['order']['payments'][0]['amount']['fees'];
        $realFee = substr($valorFee,0,-2);
        $centFee = substr($valorFee,-2);
        $paymentFee = $realFee.".".$centFee;
    /*
        log_var("VARIAVEIS","sucesso".$success."status".$status."id trans".$transactionId. "invoice id".$invoiceId."" , true);

        log_var("VARIAVEIS DIRETO DO RESPONSE","sucesso".$response['resource']['order']['status']."status".$status."id trans".$response['resource']['order']['payments'][0]['id']. "invoice id".$response['resource']['order']['ownId']."VALOR TOTAL".$valor.$paymentAmount. "VALOR FEE". $paymentFee  , true);

        log_var("VAI","ID TRANSAÇAO EXPLODE".print_r($id_transacao, true)."PAYMENTS EXPLODE".print_r($response['resource']['order']['payments'], true). "ORDER EXPLODE".print_r($response['resource']['order'],true), true);

        log_var("ARRAY::",print_r($response, true) , true);
    */
    }
     /**
    * Validate Callback Invoice ID.
    *
    * Checks invoice ID is a valid invoice number. Note it will count an
    * invoice in any status as valid.
    *
    * Performs a die upon encountering an invalid Invoice ID.
    *
    * Returns a normalised invoice ID.
    *
    * @param int $invoiceId Invoice ID
    * @param string $gatewayName Gateway Name
    */
    //checkCbInvoiceID($invoiceId, $gatewayParams['name']);
    /**
    * Check Callback Transaction ID.
    *
    * Performs a check for any existing transactions with the same given
    * transaction number.
    *
    * Performs a die upon encountering a duplicate.
    *
    * @param string $transactionId Unique Transaction ID
    */

    checkCbTransID($transactionId);

    if($status == "WAITING"){
        //add_trans( $userID, $invoiceId, '1', $transactionId, $gatewayModuleName, "Aguardando");
        //logTransaction($gatewayParams["name"],$data,"Boleto foi impresso e ainda não foi pago"); # Save to Gateway Log: name, data array, status
        //log_var ("Status [".$status."] Transação Aguardando", "Boleto foi impresso e ainda não foi pago. Retorno de dados MoIP, Pedido: ".$invoiceId . "Sucess:".print_r($success,true)."Data: ".$data_hora, true);
        //logTransaction($gatewayParams["name"],date('d/m/Y'),"Aguardando"); 
        $success = false;
    }
    if($status == "PRE_AUTHORIZED"){
        //add_trans( $userID, $invoiceId, '1', $transactionId, $gatewayModuleName, "Pré autorizado");
        //logTransaction($gatewayParams["name"],$data,"Concluído");
        //log_var ("Status [".$status."] Transação Concluída", "valor pago pelo cliente e identificado pelo MoIP. ", "Retorno de dados MoIP, Pedido: ".$invoiceid."Data: ".$data_hora, true);
        $success = false;
    }//$status == "ORDER.PAID" || 
    if($status == "PAID"){
        checkCbTransID($transactionId);
        logTransaction($gatewayParams["name"],$response, $status);
        //add_trans( $userID, $invoiceId, '1', $transactionId, $gatewayModuleName, "Pagamento autorizado", $paymentAmount, $paymentFee);
        //log_var("PAID1", "NAME GATEWAY" .print_r($gatewayParams,true)."Data: ".$data_hora, true);
        //addInvoicePayment($invoiceId,$transactionId, $paymentAmount,$paymentFee,$gatewayParams["name"]);
        //log_var("AGOOO::",print_r($response, true), true);
        $success = true;
    }

    if($status == "CANCELLED" || $status == "ORDER.NOT_PAID"){
        //
        //logTransaction($gatewayParams["name"],$data,"Pagamento foi cancelado pelo pagador, instituição de pagamento, MoIP ou recebedor antes de ser concluído");
        //log_var ("Status [".$status."] Transação Cancelada", "Pagamento foi cancelado pelo pagador, instituição de pagamento, MoIP ou recebedor antes de ser concluído. Retorno de dados MoIP, Pedido: ".$invoiceid."Data: ".$data_hora, true);
        //logTransaction($gatewayParams["name"],date('d/m/Y'),"Cancelado"); 
        //add_trans( $userID, $invoiceId, '1', $transactionId, $gatewayModuleName, "Cancelado");
        $success = false;
    }
    if($status == "IN_ANALYSIS"){
        //add_trans( $userID, $invoiceId, '1', $transactionId, $gatewayModuleName, "Analisando pagamento");
        //logTransaction($gatewayParams["name"],$array,"Pagamento foi emitido, porém está em análise. Não existe garantia de que será concluído");
        //log_var ("Status [".$status."] Transação Analisando", "Pagamento foi emitido, porém está em análise. Não existe garantia de que será concluído. Pedido: ".$invoiceId."Data: ".$data_hora, true);
        $success = false;
    }
   
    if ($success) {

        log_var("SUCCESS", "NAME GATEWAY" .print_r($gatewayParams,true)."Data: ".$data_hora, true);
        /**
         * Add Invoice Payment.
         * Applies a payment transaction entry to the given invoice ID.
         *
         * @param int $invoiceId         Invoice ID
         * @param string $transactionId  Transaction ID
         * @param float $paymentAmount   Amount paid (defaults to full balance)
         * @param float $paymentFee      Payment fee (optional)
         * @param string $gatewayModule  Gateway module name
         */
        if($status == "PAID"){
            // Se o valor pago for maior que o valor da invoice, manter o valor da invoice, para pagamento parcelados. Obter o valor da Invoice
            $statusInvoice = Capsule::table('tblinvoices')->select('status')->where('id', '=', $invoiceId)->first();
            if($statusInvoice->status == "Unpaid" && $success == true){
                addInvoicePayment($invoiceId,$transactionId,$paymentAmount,$paymentFee,$gatewayParams["name"]);
                log_var ("Adicionado o Pagamento", "Status: " .$status." Data: ".$data_hora ."Valor Adicionado na Invoice:".$invoiceId, true);
                log_var("PAID1", "NAME GATEWAY" .print_r($gatewayParams,true)."Data: ".$data_hora, true);
                $status = false;
                $success = false;
            }
        }
    }
}
?>