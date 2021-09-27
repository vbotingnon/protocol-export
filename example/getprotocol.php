<?php

require "../vendor/autoload.php";

use Neoassist\ProtocolExport\Config;
use Neoassist\ProtocolExport\ProtocolExport;

/**
 * Subdomínio informado pelos consultores neoassist
 * [subdomínio].neoassist.com
 *
 * @var string
 */
$subdomain = "";

/**
 * AppKey informado pelos consultores neoassist
 *
 * @var string
 */
$appkey = "";

/**
 * AppSecret informado pelos consultores neoassist
 *
 * @var string
 */
$appsecret = "";

/**
 * Habilita a paginação automática
 *
 * @var boolean
 */
$paginate = true;

/**
 * Número de requesições paralelas (api)
 *
 * @var boolean
 */
$workers = 100;

$Config = new Config($appkey, $appsecret, $subdomain, $paginate, $workers);
$ProtocolExport = new ProtocolExport($Config);

$ProtocolExport->addHandler(ProtocolExport::EVENT_PAGE_PROTOCOL, function ($page) {
    // $page Corresponde ao payload de lista de protocolos (sem mensagens) por página;
    echo "\nEVENT_PAGE_PROTOCOL count " . $page->result->total;
});

$ProtocolExport->addHandler(ProtocolExport::EVENT_ALL_PAGES_PROTOCOL, function ($page) {
    // $page Corresponde ao payload de lista de protocolos (sem mensagens) com todas as paginas;
    echo "\nEVENT_ALL_PAGES_PROTOCOL count " . $page->result->total;
});

$ProtocolExport->addHandler(ProtocolExport::EVENT_FULL_PROTOCOL, function ($Protocol) {
    // $Protocol Corresponde ao payload de cada Protocolo com mensagens;
    echo "\nEVENT_FULL_PROTOCOL = " . $Protocol->Protocolo;
});

$ProtocolExport->addHandler(ProtocolExport::EVENT_ALL_PAGES_FULL_PROTOCOL, function ($page) {
    // $page Corresponde ao payload de lista de protocolos (com mensagens) com todas as paginas;
    echo "\nEVENT_ALL_PAGES_FULL_PROTOCOL count " . $page->result->total;
});

$ProtocolExport->addHandler(ProtocolExport::EVENT_PROTOCOL_MESSAGE, function ($result) {
    // $result[0] = $Protocol Corresponde ao número do Protocolo;
    // $result[1] =  $Messages Corresponde ao payload da lista de mensagens;

    echo "\nEVENT_PROTOCOL_MESSAGE " . $result[0] . " messages " . count($result[1]);
});

$ProtocolExport->addHandler(ProtocolExport::EVENT_PAGE_TAGS, function ($page) {
    // $page Corresponde ao payload de lista de tags (cada paginas);
    echo "\nEVENT_PAGE_TAGS count " . $page->result->total;
});

$ProtocolExport->addHandler(ProtocolExport::EVENT_ALL_PAGE_TAGS, function ($page) {
    // $page Corresponde ao payload de lista de tags (todas as paginas);
    echo "\nEVENT_ALL_PAGE_TAGS count " . $page->result->total;
});

$ProtocolExport->addHandler(ProtocolExport::EVENT_TAGS_PROTOCOL, function ($page) {
    // $page Corresponde ao payload de lista de tags por protocolo;
    echo "\nEVENT_TAGS_PROTOCOL count " . $page->result->total;
});

$data_start = "2021-09-24 10:00:00"; // String (Y-m-d H:i:s) ou Datetime início da pesquisa;
$data_end = "2021-09-24 10:01:00"; // String (Y-m-d H:i:s) ou Datetime final da pesquisa;

$options = [
    // lista de origens de acordo com a documentação e seu protocolo inicial
    'LastProtocolByOrigin' => [
        '1' => 0,
    ],

    // lista de origens de acordo com a documentação
    'FilterOrigins' => [1],
];

$time1 = time();

// Uso 1
// $rowsProtocolo Corresponde ao payload de lista de protocolos (sem mensagens) de todas as paginas;
# $rowsProtocolo = $ProtocolExport->getProtocolList($data_start, $data_end, $options);

// Uso 2
// $rowsFullProtocolo Corresponde ao payload de lista de protocolos (com mensagens) de todas as paginas;
$rowsFullProtocolo = $ProtocolExport->getFullProtocolList($data_start, $data_end, $options);

$time2 = time();

echo "\nProtocolos Processado em " . ($time2 - $time1) . " segundos";

$Protocolo = '';
$classArray = [];
foreach ($rowsFullProtocolo->result->rows as $protocol) {
    if ($protocol->ClassificacaoIDTag) {
        $ClassificacaoIDTag = json_decode($protocol->ClassificacaoIDTag);
        if (json_last_error() === JSON_ERROR_NONE) {
            $classArray = array_merge($classArray, $ClassificacaoIDTag);

            $Protocolo = $protocol->Protocolo;
        }
    }
}

$classArray = array_unique($classArray);
$LastClassificationTagID = 0;

if (count($classArray) === 0) {
    exit;
}

// $rowsFullProtocoloTags Corresponde ao payload de lista de tags de todas as paginas;
$rowsProtocoloTags = $ProtocolExport->getTags($classArray, $LastClassificationTagID);

// $rowsFullProtocoloTags Corresponde ao payload de lista de tags de um protocolo;
$rowsTagslProtocolo = $ProtocolExport->getTagsByProtocol($Protocolo);

$time3 = time();

echo "\nProtocolos Tags em " . ($time3 - $time2) . " segundos";
