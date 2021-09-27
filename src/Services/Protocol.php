<?php

namespace Neoassist\ProtocolExport\Services;

use DateTime;
use Neoassist\ProtocolExport\Hook;
use Neoassist\ProtocolExport\ProtocolExport;

/**
 * Class Protocol
 * @package Neoassist\ProtocolExport\Services
 */
class Protocol extends Base
{
    /**
     * @param DateTime $start
     * @param DateTime $end
     * @param array $options
     * @param string $uri
     * @return object
     */
    public function getList(DateTime $start, DateTime $end, $options = [], $uri = null)
    {
        if ($uri) {
            return $this->get('GET', null, null, $uri);
        }

        $qBuilder = [
            'DateStartPeriodUpdate' => $start->format("Y-m-d\TH:i:s"),
            'DateEndPeriodUpdate' => $end->format("Y-m-d\TH:i:s"),
        ];

        if (is_array($options) && count($options)) {
            $qBuilder = array_merge($qBuilder, $options);
        }

        $path = "/API/Integration/ProtocolExport/List.json?" . http_build_query($qBuilder);

        return $this->get('GET', $path);
    }

    /**
     * @param array $reqs
     * @return array
     */
    public function getMessage($reqs)
    {
        $res = $this->multiple_threads_request($reqs);
        $output = [];
        foreach ($reqs as $protocolo => $url) {
            $data = $res[$url];
            if ($data) {
                $data = $data->result->rows;
            }

            $output[$protocolo] = $data;

            Hook::fire(ProtocolExport::EVENT_PROTOCOL_MESSAGE, [$protocolo, $output[$protocolo]]);
        }

        return $output;
    }
}
