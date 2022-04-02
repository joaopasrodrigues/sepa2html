
<?php 
    if  (isset($argc) && $argc==1) die ("usage: php index.php <SEPA BATCH Payment XML file>");
    else if (count ($_REQUEST) == 0) die (htmlentities("usage: http://<url>?<SEPA BATCH Payment XML file>"));
?>


<html>
    <head>
</head>
<body>

<?php 



if (isset($argv[1])) $file = $argv[1];
else 
    $file = array_keys($_REQUEST)[0];

    $file =str_ireplace("_xml", ".xml", $file);


$data = Array();

if (isset($file)) $xml =  simplexml_load_file(__DIR__. "/".$file);
else
    $xml = simplexml_load_string($myXMLData);



    if ($xml === false) {
    echo "Failed loading XML: ";
    foreach(libxml_get_errors() as $error) {
        echo "<br>", $error->message;
        die();

    }
} 





    $data = json_decode( xml2js($xml));

    

    ?>

    <h1>Comprovativos Registo a Registo</h1>

    <h2>Header do Ficheiro</h2>
    <hr/>
    <table>
        <tr><td>Identificação única da mensagem:</td><td><?php echo $data->Document[0]->CstmrCdtTrfInitn[0]->GrpHdr[0]->MsgId[0]->_ ?> </td></tr>
        <tr><td>Data/Hora criação da mensagem:</td><td><?php echo $data->Document[0]->CstmrCdtTrfInitn[0]->GrpHdr[0]->CreDtTm[0]->_ ?> </td></tr>
        <tr><td>Total de pagamentos:</td><td><?php echo $data->Document[0]->CstmrCdtTrfInitn[0]->GrpHdr[0]->NbOfTxs[0]->_ ?> </td></tr>
        <tr><td>Montante Total:</td><td><?php echo $data->Document[0]->CstmrCdtTrfInitn[0]->GrpHdr[0]->CtrlSum[0]->_ ?> </td></tr>
        <tr><td>Entidade:</td><td><?php echo $data->Document[0]->CstmrCdtTrfInitn[0]->GrpHdr[0]->InitgPty[0]->Nm[0]->_ ?> </td></tr>
        <tr><td>Identificação (NIF):</td><td><?php echo $data->Document[0]->CstmrCdtTrfInitn[0]->GrpHdr[0]->InitgPty[0]->Id[0] -> PrvtId[0]->Othr[0]->Id[0]->_ ?> </td></tr>
    </table>
    

   
    <h2> Informação do lote de Pagamentos</h2>
    <hr/>
    <table>
        <tr><td>Data de Liquidação:</td><td><?php   echo $data->Document[0]->CstmrCdtTrfInitn[0]->PmtInf[0]->ReqdExctnDt[0]->_ ?> </td></tr>
        <tr><td>Ordenante:</td><td><?php            echo $data->Document[0]->CstmrCdtTrfInitn[0]->PmtInf[0]->Dbtr[0]->Nm[0]->_  ?> </td></tr>
        <tr><td>IBAN do Ordenante:</td><td><?php echo$data->Document[0]->CstmrCdtTrfInitn[0]->PmtInf[0]->DbtrAcct[0]->Id[0]->IBAN[0]->_ ?> </td></tr>
        <tr><td>BIC SWIFT do Ordenante:</td><td><?php  $data->Document[0]->CstmrCdtTrfInitn[0]->PmtInf[0]->ReqdExctnDt[0]->_  ?> </td></tr>
        <tr><td>Caráter da Instrução:</td><td><?php  $data->Document[0]->CstmrCdtTrfInitn[0]->PmtInf[0]->ReqdExctnDt[0]->_ ?> </td></tr>
        <tr><td>Tipo de Serviço:</td><td><?php echo $data->Document[0]->CstmrCdtTrfInitn[0]->PmtInf[0]->PmtMtd[0]->_  ?> </td></tr>
        <tr><td>Código Motivo dos Pagamentos do Lote:</td><td><?php echo $data->Document[0]->CstmrCdtTrfInitn[0]->PmtInf[0]->PmtTpInf[0] -> CtgyPurp[0]-> Cd[0]->_   ?> </td></tr>
    </table>
    


    <h2>Registo de Movimento(s) Relativo(s) ao(s) Pagamento(s)</h2>
    <hr/>
    <table>
        <thead>
            <tr>
                <td>Beneficiário</td>
                <td>BIC SWIFT</td>
                <td>IBAN</td>
                <td>Montante (EUR)</td>
                <td>Estado Actual</td>
            </tr>
        </thead>


        <tbody>

<?php 
            foreach ($data->Document[0]->CstmrCdtTrfInitn[0]->PmtInf[0]->CdtTrfTxInf as $line)
            {
?>
            <tr>
                <td><?php echo  $line->Cdtr[0]->Nm[0]->_  ?> </td>
                <td><?php   $line->Cdtr[0]->Nm[0]->_  ?> </td>
                <td><?php echo  $line->CdtrAcct[0]->Id[0]->IBAN[0]->_  ?> </td>
                <td><?php echo  $line->Amt[0]->InstdAmt[0]->_ ?> </td>
                <td><?php echo isset( $line->CdtrAgt[0]->FinInstnId[0])? "NORMAL;LANCAMENTO EXECUTADO": "???"  ?> </td>
 

            </tr>

<?php
}
?>
        </tbody>
    </table>




    <br>

    <hr>
    <h3>Informação gerada de forma automatica a partir do ficheiro : <?php echo $file; ?></h3>

</body>

    <?php    

function xml2js($xmlnode) {
    $root = (func_num_args() > 1 ? false : true);
    $jsnode = array();

    if (!$root) {
        if (count($xmlnode->attributes()) > 0){
            $jsnode["$"] = array();
            foreach($xmlnode->attributes() as $key => $value)
                $jsnode["$"][$key] = (string)$value;
        }

        $textcontent = trim((string)$xmlnode);
        if (strlen($textcontent) > 0)
            $jsnode["_"] = $textcontent;

        foreach ($xmlnode->children() as $childxmlnode) {
            $childname = $childxmlnode->getName();
            if (!array_key_exists($childname, $jsnode))
                $jsnode[$childname] = array();
            array_push($jsnode[$childname], xml2js($childxmlnode, true));
        }
        return $jsnode;
    } else {
        $nodename = $xmlnode->getName();
        $jsnode[$nodename] = array();
        array_push($jsnode[$nodename], xml2js($xmlnode, true));
        return json_encode($jsnode);
    }
}   