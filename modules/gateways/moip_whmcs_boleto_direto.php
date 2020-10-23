<?php

    /*
        Gateway MOIP WHMCS Boleto Direto
        Desenvolvido por: Davi Souza
        Criado em: 2018
        Versao: v3.0
        Companhia: LINK NACIONAL
        Codificação: UTF-8
    */
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (class_exists('Moip\Moip')) {  

}else{
    include_once __DIR__.'/moip_whmcs_boleto_direto/sdk/vendor/autoload.php';
}

    use Moip\Moip;
    use Moip\Auth\BasicAuth;


/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function gatewaymodule_MetaData()
{
    return array(
        'DisplayName' => 'MOIP WHMCS Boleto Direto',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false,
    );
}
/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function moip_whmcs_boleto_direto_config(){

    //$url  = Capsule::table('tblconfiguration')->where('setting','='SystemURL'')->first();
    //$system_url = $url->value;

    $config = array(

        "FriendlyName" => array(
            "Type" => "System", 
            "Value"=>"MOIP Boleto Direto"),

        'moipAtivo' => array(
            'FriendlyName' => 'Moip Producao',
            'Type' => 'yesno',
            'Description' => 'Clique para habilitar MOIP em Produção (Desabilitar Moip Sandbox)'),

        'id_api_whmcs' => array(
            'FriendlyName' => 'API Identificador WHMCS',
            'Type' => 'text',
            'Size' => '40',
            'Default' => '',
            'Description' => 'Acesso WHMCS identificador, criar em Options -> Staff Management -> Manage API Credentials, Antes de criar precisa criar a regras (roles) the Billing e Client (marque todas opções).'),

        'secret_api_whmcs' => array(
            'FriendlyName' => 'API Secret Key WHMCS',
            'Type' => 'text',
            'Size' => '45',
            'Default' => '',
            'Description' => 'Acesso WHMCS secret, criar em Options -> Staff Management -> Manage API Credentials'),

        "token_moip" => array(
            "FriendlyName" => "Token Moip", 
            "Type" => "text", 
            "Size" => "80", 
            "Name" => "token_moip", 
            "Description" => "Informar o Token Moip"),

        "key_moip" => array(
            "FriendlyName" => "Chave Moip", 
            "Type" => "text", 
            "Size" => "80", 
            "Name" => "key_moip", 
            "Description" => "Informar a Chave Moip"),

        "pessoa_tipo" => array(
            "FriendlyName" => "Tipo pessoa",
            "Type" => "dropdown",
            "Options" =>moip_whmcs_boleto_direto_get_customfields(),
            "Description" => "Tipo de pessoa, valores: juridica, fisica ou estrangeiro"),

        'cpfMoip' => array(
            'FriendlyName' => 'CPF',
            'Type' => 'dropdown',
            'Options' =>moip_whmcs_boleto_direto_get_customfields(),
            'Description' => 'Campo personalizado de CPF, apenas os números'),

        'birthday' => array(
            'FriendlyName' => 'Birthday Date',
            'Type' => 'dropdown',
            'Options' =>moip_whmcs_boleto_direto_get_customfields(),
            'Description' => 'Data de aniversário no padrão dd/mm/aaaa'),

        'cnpj' => array(
            'FriendlyName' => 'CNPJ Data',
            'Type' => 'dropdown',
            'Options' =>moip_whmcs_boleto_direto_get_customfields(),
            'Description' => 'Campo personalizado de CNPJ, apenas os números'),

        'moipIDAdmin' => array(
            'FriendlyName' => 'MOIP ID CUSTOMER',
            'Type' => 'dropdown',
            'Options' =>moip_whmcs_boleto_direto_get_customfields(),
            'Description' => 'Create a custom field with name moipID and type the array number here'),

        "url_logo_boleto" => array(
            "FriendlyName" => "Logomarca Boleto",
            "Type" => "text", "Size" => "50",
            "Name" => "url_logo_boleto",
            "Description" => "Informe a URL com http:// Tamanho: 75x40"),

        "instrucao_1" => array(
            "FriendlyName" => "Instruções do Boleto 1",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Mensagem Personalizada no Boleto Linha 1"),

        "instrucao_2" => array(
            "FriendlyName" => "Instruções do Boleto 2",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Mensagem Personalizada no Boleto Linha 2"),

        "instrucao_3" => array(
            "FriendlyName" => "Instruções do Boleto 3",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Mensagem Personalizada no Boleto Linha 3"),

        "dias_corridos" => array(
            "FriendlyName" => "Dias para Vencimento",
            "Type" => "text",
            "Size" => "50",
            "Value" => "5",
            "Description" => "Após o vencimento, defina quantos dias corridos para um novo vencimento."),

        "texto_botao" => array(
            "FriendlyName" => "Texto do botão",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Descrição do botão dentro da fatura"),

        "callback_whmcs" => array(
            "FriendlyName" => "Habilitar Callback Automático",
            "Type" => "yesno",
            "Description" => "Habilitar retorno automático do MOIP através das preferências de notificações. <a href='https://www.linknacional.com.br/133/pagamento-direto-whmcs-moip-automatico/?pluginMoip=true'>Saiba Mais.</a>"),

        "key_whmcs" => array(
            "FriendlyName" => "Chave do Callback",
            "Type" => "text",
            "Size" => "50",
            "Description" => "Utilizado quando o callback estiver ativo, é um número aleatório, por exemplo: 5874698"),

        'moipNotification' => array(
            'FriendlyName' => 'MOIP notifications IDs',
            'Type' => 'dropdown',
            'Options' =>moip_whmcs_boleto_direto_get_notitification(),
            'Description' => 'Selecione uma notificação para não ter pagamentos confirmados em duplicidade.'),

        "whmcs_debugar" => array(
            "FriendlyName" => "Habilitar Debug",
            "Type" => "yesno",
            "Description" => "Habilitar parametros para debugar na fatura"),

        "callback_whmcs_debug" => array(
            "FriendlyName" => "Habilitar Debug do Callback Automático",
            "Type" => "yesno",
            "Description" => "Habilitar arquivo TXT para debugar os retornos automáticos, não recomendavel manter esse recurso habilitado. <a href='/modules/gateways/callback/moip_whmcs_boleto_direto_debug.txt' target='_blank'>Ver arquivo.</a>"),
    );

    return $config;
}
/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function moip_whmcs_boleto_direto_link($params){
    require __DIR__.'/moip_whmcs_boleto_direto/params.php';
    //echo "moipIDSalvo".$moipIDSalvo. "CPF".$cpf;
    ///SALVAR USUARIO NO MOIP VALIDAR DADOS DE CADASTRO.
     ///SALVAR USUARIO NO MOIP VALIDAR DADOS DE CADASTRO.
    $gerar=false;
    if(empty($moipIDSalvo)){
        $moipID = moip_whmcs_boleto_direto_salvarCustomer($params);// retorna o ID do CUSTOMER ou false
        if($whmcs_debugar == "on"){
            echo "moipID";
            print_r($moipID);
        }

        //print_r($moipIDSalvo);

        if ($moipID != false){
           $gerar = true;
        }
    }else{
        $gerar = true;
    }

    if($whmcs_debugar == "on"){
        echo "GERAR".$gerar;
        echo "moipIDSalvo";
        print_r($moipIDSalvo);
    }

    if($gerar){
         $pagamento = moip_whmcs_boleto_direto_pay($params, $moipID);
            $reflector = new \ReflectionClass($pagamento);
            $classProperty = $reflector->getProperty('data');
            $classProperty->setAccessible(true);
            $data = $classProperty->getValue($pagamento);

            $numeroBoleto = $data->fundingInstrument->boleto->lineCode;
            $numeroBoletoTratado = preg_replace("/[^\da-z]/i", "", $numeroBoleto);
            $urlBoletoGerado = $data->_links->payBoleto->printHref;

            if($whmcs_debugar == "on"){
                print_r($data);
            }
            //print_r($data->_links->payBoleto->printHref);

            if($pagamento != false && $numeroBoletoTratado != ""){
                $htmlOutput .= '<a class="btn btn-default hidden-print" target="_blank" onClick="window.print()" >';

                $htmlOutput .= '<i class="fa fa-print"></i>  '. $texto_botao .' </a><a href="'.$urlBoletoGerado.'" target="_blank" class="btn btn-default  hidden-print" download="download"><i class="fa fa-download"></i> Download</a><br><br>';
                $htmlOutput .= '<small>O prazo para confirmação de pagamento de boleto bancário é de até 48 horas úteis.</small>'.gerarCodigoBarra($numeroBoletoTratado)."<small>Código de barra</small>"; //////// GERAR CÓDIGO DE BARRA.
                $htmlOutput .="<h4>".$numeroBoleto."</h4><small>Ĺinha digitável</small>";
            }
    }else{
        $htmlOutput = "Erro ao gerar boleto, dados obrigatórios: CPF, data de nascimento, telefone e endereço completo. <a href='clientarea.php?action=details'>Ver meus dados</a>";
    }

    return $htmlOutput;
}

/*
* METODOS AUXILIARES
*/
/**
 * Conexão com o MOIP
 * @return object
*/
function moip_whmcs_boleto_direto_conectaMOIP($params){
    require __DIR__.'/moip_whmcs_boleto_direto/params.php';
     try{
        if($moipTeste){
            $moip = new Moip(new BasicAuth($token_moip, $key_moip), Moip::ENDPOINT_SANDBOX);
        }elseif($moipTeste == false){
            $moip = new Moip(new BasicAuth( $token_moip, $key_moip), Moip::ENDPOINT_PRODUCTION);
        }
        return $moip;
    } catch (\Moip\Exceptions\UnautorizedException $e) {
        echo $e->getMessage();
        return false;
    }    
}

/**
 * Get customFields list
 * @return array
*/
function moip_whmcs_boleto_direto_get_customfields() {
    $fields = mysql_query("SELECT id, fieldname FROM tblcustomfields WHERE type = 'client';");
    if (!$fields) {
        return array('0' => 'database error');
    }elseif (mysql_num_rows($fields) >= 1) {
        $dropFieldArray = array('0' => 'selecione um campo');
        while ($field = mysql_fetch_assoc($fields)) {
        // the dropdown field type renders a select menu of options
        $dropFieldArray[$field['id']] = $field['fieldname'];
        }
       return $dropFieldArray;
    } else {
        return array('0' => 'nothing to show');
    }
}

/**
 * Get notificationID list
 * @return array
*/
function moip_whmcs_boleto_direto_get_notitification() {

    /* FAZER ESSE AJUSTE EM UMA PROXIMO ATUALIZAÇÃO
    Buscar informações do banco de dados e criar o array do params para o methodo conecta moip:

    $moduleoptions = select_query('tbladdonmodules', 'setting,value', array('module' => 'modulename'));
    $opts = array();
    while($m = mysql_fetch_assoc($moduleoptions)){
    $opts[$m['key']] = $m['value'];
    }
    
    */

    //require __DIR__.'/moip_whmcs_boleto_direto/params.php';
    $gatewayModuleName = basename(__FILE__, '.php');
    $notificao = false;

    $fields = mysql_query("SELECT setting, value FROM tblpaymentgateways WHERE setting = 'moipAtivo' AND value = 'on';");
    if (!$fields) {
        //return array('0' => 'database error');
        echo "erro database";
    }elseif (mysql_num_rows($fields) >= 1) {
        //$dropFieldArray = array('0' => 'selecione um campo');
        $notificao = "true";
        $gatewayParams = getGatewayVariables($gatewayModuleName);
        require __DIR__.'/moip_whmcs_boleto_direto/params.php';
        //print_r($gatewayParams);
    }

    if($notificao){

        try{
            if($gatewayParams['moipAtivo'] == "on"){
                //echo "PRODUCAO";
                $moip = new Moip(new BasicAuth( $gatewayParams['token_moip'] , $gatewayParams['key_moip']), Moip::ENDPOINT_PRODUCTION);
                $notifications = $moip->notifications()->getList();

                $reflector1 = new \ReflectionClass($notifications);
                $classProperty = $reflector1->getProperty('data');
                $classProperty->setAccessible(true);
                $data1 = $classProperty->getValue($notifications);

                //print_r($notifications);

                //echo "MOIP NOTIFICAO VALOR".$gatewayParams["moipNotification"];
                //echo "SystemURl".;

                if ($gatewayParams["moipNotification"] == "0"){
                    //echo "CRIAR NOTIFICAÇÂO"; $systemUrl.
                    $notification = $moip->notifications()->addEvent('ORDER.PAID')->addEvent('ORDER.CREATED')->addEvent('PAYMENT.WAITING')->setTarget($gatewayParams['systemurl'].'/modules/gateways/callback/moip_whmcs_boleto_direto.php?key='.$gatewayParams['key_whmcs'])->create();

                    //print_r($notification);
                }

                /////// GARATINDO QUE TEM APENAS 1 notificação cadastrada
                $dropFieldArray = array('0' => 'Criar nova notificação');
                //echo "moipNOTIFICATION ".$gatewayParams["moipNotification"];
                foreach ($data1->notifications as $key => $value) {
                     //echo "key:".$key;
                     //echo "value".$value->id;
                    if ($gatewayParams["moipNotification"] != "0"){
                        if ($gatewayParams["moipNotification"] == $value->id){
                            $dropFieldArray[$value->id] = $value->id;
                        }else{
                             //echo 'deleta notificação'.$value->id;
                             $notification = $moip->notifications()->delete($value->id);
                        }
                    }else{
                        $dropFieldArray[$value->id] = $value->id;
                    }
                    //echo "valueFOra".$value->id;
                }
                return $dropFieldArray;

            }else{
                //echo "SANDBOX";
                $moip = new Moip(new BasicAuth( $gatewayParams['token_moip'] , $gatewayParams['key_moip']), Moip::ENDPOINT_SANDBOX);
            }
        } catch (\Moip\Exceptions\UnautorizedException $e) {
            echo $e->getMessage();
            return false;
        }  
    }else{
     return $dropFieldArray = array('0' => 'Callback automatico não ativado');
    }
}

/**
 * 
 * @return object or False
*/
function moip_whmcs_boleto_direto_pay($params, $moipID){
    //echo "pagando";
    require __DIR__.'/moip_whmcs_boleto_direto/params.php';
    $moip = moip_whmcs_boleto_direto_conectaMOIP($params);

    $customerMoip = moip_whmcs_boleto_direto_buscarCustomerMoip($params, $moipID);
    //print_r($customerMoip);

    $amountDot = $params['amount'];
    $totalTratado = str_replace(".", "", $amountDot);

    try{
        $order = $moip->orders()->setOwnId($invoiceID.":".uniqid())
            ->addItem($params['description'], 1, $invoiceID, (int)$totalTratado)
            ->setCustomer($customerMoip)
            ->create();
            //$payment = $order->payments()->setCreditCardSaved($moipCC, $moipCVC)->execute();


            $logo_uri = $logo_boleto;
            $expiration_date = new DateTime();
            $dueDateTratada =  date("Y-m-d", strtotime($params['dueDate']));
            if(date("Y-m-d") < $dueDateTratada){
                $dueDate = $dueDateTratada;

            }else{
                $dueDate = date("Y-m-d", mktime (0, 0, 0, date("m")  , date("d")+$params['dias_corridos'], date("Y")));
            }

            $instruction_lines = [$instrucao_1, $instrucao_2, $instrucao_3];

            $payment = $order->payments()  
                ->setBoleto($dueDate, $logo_uri, $instruction_lines)
                ->execute();
            //print_r($payment);


        return $payment;

        } catch (\Moip\Exceptions\UnautorizedException $e) {
            echo $e->getMessage();
            return false;
        }
}

/**
 * Check customer data and save customers info in MOIP
 * @return object
 */
function moip_whmcs_boleto_direto_salvarCustomer($params){
    require __DIR__.'/moip_whmcs_boleto_direto/params.php';

    $erro = false;

    if(empty($firstname) || empty($lastname)){
        $msg = "Favor preencher sou nome e sobrenome corretamente";
        $erro =  true;
    }elseif (empty($email)) {
         $msg .=  "<br>Favor preencher seu email corretamente";
         $erro =  true;
    }elseif (empty($birthday) || strlen($birthday) != 10) {
         $msg .= "<br>Favor preencher sua data de aniversário corretamente, deve estar no formato 01/12/2001";
         $erro =  true;
    }elseif (empty($phoneDD)|| empty($phoneSufixo)) {
         $msg .= "<br>Favor preencher seu telefone corretamente";
         $erro =  true;
    }elseif (empty($street) || empty($city) || empty($homeNumber) || empty($state) || empty($postcode)) {
         $msg .= "<br>Favor preencher seu endereço corretamente";
         $erro =  true;
    }
    $doc_tipo = "CPF";

    if($pessoa_tipo == "Pessoa Jurídica"){
        $doc_tipo = "CNPJ"; 
        $numeroDoc = $cnpj;
        if (empty($cnpj)) {
            $msg .= "<br>Favor preencher os dados do CNPJ corretamente.";
            $erro =  true;
        }
        if(empty($ClientCompanyName)){
            $msg .= "<br>Favor preencher o nome da empresa corretamente.";
            $erro =  true;
        }
    }
    if($pessoa_tipo == "Pessoa Física"){
        $doc_tipo = "CPF";
        $numeroDoc = $cpf;
        if (empty($cpf)) {
         $msg .= "<br>Favor preencher os dados do CPF corretamente.";
         $erro =  true;
        }
    }

    if($whmcs_debugar == "on"){
        echo "Nome: ". $firstname .  " Sobrenome: ". $lastname ." Email: " .$email ." Niver: ".$birthday . " Pessoa: ".$pessoa_tipo. "DOCTIPO: ".$doc_tipo. " cnpj: ". $cnpj. " cpf: ". $cpf. " DDD: ".$phoneDD. " Salvar Customer: ".   $phoneSufixo . $street . $homeNumber . $city . $state   ." sufixo " .$postcode .$params['clientdetails']['phonenumber'];
    }

    if($erro){
        echo "<div class='alert alert-danger'> $msg <br>Corrigir os erros de dados cadastrais antes de prosseguir com seu pagamento. <a href='clientarea.php?action=details'>Editar</a></div>";
        return false;
        die();
    }
    //echo "Data de aniversario:".$birthday. $firstname . $lastname;

    if($erro == false){
            $moip = moip_whmcs_boleto_direto_conectaMOIP($params);

            if($whmcs_debugar == "on"){
                echo "Conectar no moip";
                print_r($moip);
            }

        try {

            if($whmcs_debugar== "on"){ echo "::".$homeNumber; }

            $customer_moip = $moip->customers()->setOwnId(uniqid())
                ->setFullname($ClientCompanyName)
                ->setEmail($email)
                ->setBirthDate($birthday)
                ->setTaxDocument($numeroDoc, $doc_tipo)
                ->setPhone($phoneDD,$phoneSufixo,$country)
                ->addAddress('SHIPPING',
                    $street , $homeNumber   ,
                    'Bairro', $city, $state,$postcode, 8)
                ->create();
                // Acessando o ID CUSTOMER
                $reflector = new \ReflectionClass($customer_moip);
                $classProperty = $reflector->getProperty('data');
                $classProperty->setAccessible(true);
                $data = $classProperty->getValue($customer_moip);

                //// retorna o ID do CUSTOMER DO MOIP
                $moipID = $data->id;
                //echo "moipID".$moipID;
                $salvouMoipId = moip_whmcs_boleto_direto_salvarCartaoIntoCustomer($params, $userID, $moipID);

                if($whmcs_debugar == "on"){
                    echo "MOIP ID".$moipID;
                }

                if ($salvouMoipId) {
                    //echo "salvou";
                    return $moipID;
                }else{
                    //echo "não salvou";
                    return false;
                }

            } catch (\Moip\Exceptions\UnautorizedException $e) {
                echo $e->getMessage();
            }
    }
}

// Pesquisar cliente no MOIP pelo ID salvo no perfil do WHMCS
function moip_whmcs_boleto_direto_buscarCustomerMoip($params, $moipID){
    require __DIR__.'/moip_whmcs_boleto_direto/params.php';
    $moip = moip_whmcs_boleto_direto_conectaMOIP($params);

    try{
        $customerMoip = $moip->customers()->get($moipID);
        return $customerMoip;
    } catch (\Moip\Exceptions\UnautorizedException $e) {
        echo $e->getMessage();
        return false;
    }
}


/// Atualizando o cliente no WHMCS com o customer id do moip
function moip_whmcs_boleto_direto_salvarCartaoIntoCustomer($params, $userID, $moipID){
    require __DIR__.'/moip_whmcs_boleto_direto/params.php';

        $customfields = array($moipIDAdmin => $moipID);

        // Atualizando Cliente nos custom fields
        $postfields = array(
            'identifier' => $id_api_whmcs,
            'secret' => $secret_api_whmcs,
            'clientid' => $userID,
            'customfields' => base64_encode(serialize($customfields)),
            'action' => 'updateclient',
            'skipvalidation' => true,
            'responsetype' => 'json'
        );

        // Call the API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $systemUrl . 'includes/api.php');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postfields));
        $response = curl_exec($ch);
        if (curl_error($ch)) {
            die('Unable to connect: ' . curl_errno($ch) . ' - ' . curl_error($ch));
        }
        curl_close($ch);
        // Attempt to decode response as json
        $jsonData = json_decode($response, true);

        //print_r($jsonData);
        if($whmcs_debugar == "on"){
            echo "JSON";
            print_r($jsonData);
        }

        // CLIENTE ATUALIZADO
        if($jsonData['result'] == "success"){
            return true;
        }else{
            return false;
            //print_r($jsonData);
            echo "NAO FOI POSSIVEL ATUALIZAR CLIENTE COM OS DADOS";
        }
}


function gerarCodigoBarra($numero){
    $returnCodigo .= "<script src='assets/js/boleto.min.js'></script>";
    $returnCodigo .= "<div id='boleto'></div>";
$returnCodigo .= "<script>
  var number = '".$numero."';
  new Boleto(number).toSVG('#boleto');
</script>";

    return $returnCodigo;
}
?>