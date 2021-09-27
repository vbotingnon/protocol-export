<?php

namespace Neoassist\ProtocolExport;

use DateTime;
use Neoassist\ProtocolExport\Services\Protocol as ProtocolService;
use Neoassist\ProtocolExport\Services\Tag as TagService;

/**
 * Class ProtocolExport
 * @package Neoassist\ProtocolExport
 */
class ProtocolExport
{

    const EVENT_PAGE_PROTOCOL = 'EVENT_PAGE_PROTOCOL';
    const EVENT_ALL_PAGES_PROTOCOL = 'EVENT_ALL_PAGES_PROTOCOL';
    const EVENT_FULL_PROTOCOL = 'EVENT_FULL_PROTOCOL';
    const EVENT_ALL_PAGES_FULL_PROTOCOL = 'EVENT_ALL_PAGES_FULL_PROTOCOL';
    const EVENT_PROTOCOL_MESSAGE = 'EVENT_PROTOCOL_MESSAGE';
    const EVENT_PAGE_TAGS = 'EVENT_PAGE_TAGS';
    const EVENT_ALL_PAGE_TAGS = 'EVENT_ALL_PAGE_TAGS';
    const EVENT_TAGS_PROTOCOL = 'EVENT_TAGS_PROTOCOL';

    const EVENTS = [
        'EVENT_PAGE_PROTOCOL',
        'EVENT_ALL_PAGES_PROTOCOL',
        'EVENT_FULL_PROTOCOL',
        'EVENT_ALL_PAGES_FULL_PROTOCOL',
        'EVENT_PROTOCOL_MESSAGE',
        'EVENT_PAGE_TAGS',
        'EVENT_ALL_PAGE_TAGS',
        'EVENT_TAGS_PROTOCOL',
    ];

    /**
     * @var Config
     */
    protected $Config;

    /**
     * @var ProtocolService
     */
    protected $ProtocolService;

    /**
     * @var TagService
     */
    protected $TagService;

    /**
     * @var string
     */
    protected $path = null;

    /**
     * @var object
     */
    protected $results = null;

    /**
     * @var array
     */
    protected $bodyTag = null;

    /**
     * @var object
     */
    protected $resultsTag = null;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->Config = $config;
        $this->ProtocolService = new ProtocolService($config);
        $this->TagService = new TagService($config);
    }

    /**
     * Undocumented function
     *
     * @param string $event_name
     * @param callable $callback_fn
     * @return void
     * @throws Exception
     */
    public function addHandler(string $event_name, callable $callback_fn)
    {
        if (in_array($event_name, self::EVENTS) === false) {
            throw new Exception("invalid name event handler");
        }

        Hook::add($event_name, $callback_fn);
    }

    /**
     * @param DateTime|string $start
     * @param DateTime|string $end
     * @param array|null $end
     * @return object
     * @throws Exception
     */
    public function getFullProtocolList($start, $end, $options = null)
    {
        $protocols = $this->getProtocolList($start, $end, $options);

        if ($protocols && $protocols->result->total) {
            $reqs = [];
            $messages = [];
            foreach ($protocols->result->rows as $row) {
                $reqs[$row->Protocolo] = "/API/Integration/ProtocolExport/Message/{$row->Origem}/{$row->Protocolo}.json";
                if (count($reqs) >= $this->Config->workers) {
                    $messages = array_merge($messages, $this->ProtocolService->getMessage($reqs));
                    $reqs = [];
                }
            }

            if (count($reqs) > 0) {
                $messages = array_merge($messages, $this->ProtocolService->getMessage($reqs));
            }

            foreach ($protocols->result->rows as &$row) {
                $row->Messages = $messages[$row->Protocolo] ?? [];

                Hook::fire(ProtocolExport::EVENT_FULL_PROTOCOL, $row);
            }

            Hook::fire(ProtocolExport::EVENT_ALL_PAGES_FULL_PROTOCOL, $protocols);

            return $protocols;
        }

        return [];
    }

    /**
     * @param DateTime|string $start
     * @param DateTime|string $end
     * @param array|null $end
     * @return object
     * @throws Exception
     */
    public function getProtocolList($start, $end, $options = null)
    {
        if (!($start instanceof DateTime)) {
            $start = DateTime::createFromFormat("Y-m-d H:i:s", $start);
        }

        if (!($end instanceof DateTime)) {
            $end = DateTime::createFromFormat("Y-m-d H:i:s", $end);
        }

        if (!$start) {
            throw new Exception("invalid date start");
        }

        if (!$start) {
            throw new Exception("invalid date end");
        }

        $page = $this->ProtocolService->getList($start, $end, $options, $this->path);

        if ($this->results) {
            $this->results->result->rows = array_merge($this->results->result->rows, $page->result->rows);
            $this->results->result->total += $page->result->total;
        } else {
            $this->results = $page;
        }

        Hook::fire(self::EVENT_PAGE_PROTOCOL, $page);

        if ($this->Config->paginate && $page->result->next_page) {
            $this->path = $page->result->next_page;

            return $this->getProtocolList($start, $end, $options);
        }

        $this->path = null;

        $result = $this->results;

        $this->results = null;

        Hook::fire(self::EVENT_ALL_PAGES_PROTOCOL, $result);

        return $result;
    }

    /**
     * @param array $tagList
     * @param int $LastClassificationTagID
     * @return objetc
     */
    public function getTags(array $tagList, int $LastClassificationTagID = 0)
    {
        if (count($tagList) === 0) {
            throw new Exception("empty array");
        }

        $page = $this->TagService->getList($tagList, $LastClassificationTagID, $this->bodyTag);

        if ($this->resultsTag) {
            $this->resultsTag->result->rows = array_merge($this->results->result->rows, $page->result->rows);
            $this->resultsTag->result->total += $page->result->total;
        } else {
            $this->resultsTag = $page;
        }

        Hook::fire(self::EVENT_PAGE_TAGS, $page);

        if ($this->Config->paginate && $page->result->next_page) {

            $this->bodyTag = $page->result->next_page_params;

            return $this->getTags($tagList, $LastClassificationTagID);
        }

        $this->bodyTag = null;

        $result = $this->resultsTag;

        $this->resultsTag = null;

        Hook::fire(self::EVENT_ALL_PAGE_TAGS, $result);

        return $result;
    }

    /**
     * @param string $protocol
     * @return objetc
     */
    public function getTagsByProtocol(string $protocol)
    {
        if (!$protocol) {
            throw new Exception("empty protocol");
        }

        $result = $this->TagService->getListByProtocol($protocol);

        Hook::fire(self::EVENT_TAGS_PROTOCOL, $result);

        return $result;
    }
}
