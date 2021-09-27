<?php

namespace Neoassist\ProtocolExport\Services;

use DateTime;
use Neoassist\ProtocolExport\Exception;
use Neoassist\ProtocolExport\Hook;
use Neoassist\ProtocolExport\ProtocolExport;

/**
 * Class Tag
 * @package Neoassist\ProtocolExport\Services
 */
class Tag extends Base
{

    /**
     * @param array $tagList
     * @param integer $LastClassificationTagID
     * @param array|null $body
     * @return object
     */
    public function getList(array $tagList = [], int $LastClassificationTagID = 0, array $body = null)
    {
        $path = "/API/Integration/ProtocolExport/Classification.json";

        if ($body === null) {
            $body = [
                'LastClassificationTagID' => $LastClassificationTagID,
                'ClassificationTagList' => $tagList,
            ];
        }

        return $this->get('POST', $path, json_encode($body));
    }

    /**
     * @param string $protocol
     * @return object
     */
    public function getListByProtocol(string $protocol)
    {
        $path = "/API/Integration/ProtocolExport/Classification/{$protocol}.json";

        return $this->get('GET', $path);
    }
}
