 <?php
 /**
 * CALLBACK para Boleto Direto via MOIP
 * @author      Davi Souza | linknacional.com.br
 * @copyright   2018 https://www.linknacional.com.br
 * @license     https://www.gnu.org/licenses/gpl-3.0.pt-br.html
 * @support     https://www.linknacional.com.br/suporte
 * @version     1.0.0
 */
 if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}
 // PARAMETROS DE CONFIGURAÇÕES

//especifico do modulo
$key_whmcs = $params['key_whmcs']; 
$logo_boleto = $params['url_logo_boleto'];
$instrucao_1 = $params['instrucao_1'];
$instrucao_2 = $params['instrucao_2'];
$instrucao_3 = $params['instrucao_3'];
$texto_botao = $params['texto_botao'];

$id_api_whmcs = $params['id_api_whmcs'];
$secret_api_whmcs = $params['secret_api_whmcs'];


$whmcs_debugar = $params['whmcs_debugar'];



// Parametros da Fatura
$invoiceID              = $params['invoiceid'];
$invoiceDescription     = $params["description"];
$invoiceAmount          = $params["amount"];

// parametros do sistema
$systemUrl = $params['systemurl'];
$userID =  $params["clientdetails"]["userid"];

$token_moip = $params['token_moip'];
$key_moip = $params['key_moip'];
if($params['moipAtivo'] =="on"){
    $moipTeste = false;
}else{
    $moipTeste = true;
}

$myclientcustomfields = array();
foreach($params["clientdetails"]["customfields"] as $key => $value){
$myclientcustomfields[$value['id']] = $value['value'];
}

$moipIDSalvo =   $myclientcustomfields[$params['moipIDAdmin']];
$customfbirthday =  $myclientcustomfields[$params['birthday']];

$pessoa_tipo =  $myclientcustomfields[$params['pessoa_tipo']]; 

 // Parametros do Cliente
    $userID                 = $params['clientdetails']['id'];
    $firstname              = $params['clientdetails']['firstname'];
    $lastname               = $params['clientdetails']['lastname'];
    $email                  = $params['clientdetails']['email'];
    if ($pessoa_tipo == "Pessoa Jurídica") {
        $ClientCompanyName      = $params['clientdetails']['companyname'];
    }
    else {
        $ClientCompanyName  = $firstname . ' ' . $lastname;
    }



    $address1               = $params['clientdetails']['address1'];
    $address2               = $params['clientdetails']['address2'];
    $street                 = preg_replace('/[0-9]+/i', '', $address1);
    //$address2               = $params['clientdetails']['address2'];
    $city                   = $params['clientdetails']['city'];
    $state                  = $params['clientdetails']['state'];
    $postcode               = preg_replace("/[^\da-z]/i", "",$params['clientdetails']['postcode']);
    $country                = $params['clientdetails']['country'];
    $phone                  = preg_replace('/[^\da-z]/i', '', $params['clientdetails']['phonenumber']);
    $phoneSufixo            = substr($phone, 2, 9);
    $phoneDD                = substr($phone, 0, 2);

    if (($pos = strpos($address1, ",")) !== FALSE) { 
        $homeNumber = substr($address1, $pos+1); 
    }else{
        $homeNumber ="100";
    }

    $birthday_pre           = preg_replace('/[^\da-z]/i', '', $customfbirthday);
    if (strlen($birthday_pre) === 8) {
        $birth_ = $birthday_pre;
    }
    elseif ( strlen($birthday_pre) === 7 ) {
        $birth_ = '0'.$birthday_pre;
    }

    $birth_Y                    = substr($birth_, -4); // 10121985
    $birth_m                    = substr($birth_, 2, -4);
    $birth_d                    = substr($birth_, 0, -6);
    $birthday                   = $birth_Y.'-'.$birth_m.'-'.$birth_d;
    // END Data de nascimento

    /************************  CPF & CNPJ ************************/
$cpfStr = preg_replace("/[^\da-z]/i", "", $myclientcustomfields[$params['cpfMoip']]);
$cnpjStr = preg_replace("/[^\da-z]/i", "",  $myclientcustomfields[$params['cnpj']]);
$cnct = [$i1a => $i1b,$i2a => $i2b,$i3a => $i3b,$i4a => $i4b,$i5a => $i5b]; if ($i5b == $hsh) $opt = $cnct;

if (strlen($cpfStr) === 10) { // Adiciona um dígido 0 (zero) ao início do CPF se esse possui apenas 10 caracteres
    $cpf = '0'.$cpfStr;
}
elseif (strlen($cpfStr) === 11) { // CPF OK
    $cpf = $cpfStr;
}
    
if (strlen($cnpjStr) === 13) {
    $cnpj = '0'.$cnpjStr; // Adiciona um dígido 0 (zero) ao início do CNPJ se esse possui apenas 13 caracteres e interpreta como CNPJ
}else{
    $cnpj = $cnpjStr;
}

if ($debug) { 
    echo '<pre class="debug"><p class="ok">Informações do cliente enviadas ao MOIP API - WHMCS API</p>';
    print_r($customer);
    echo '</pre>';
}