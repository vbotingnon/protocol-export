<?php

namespace Neoassist\ProtocolExport;

/**
 * Class Config
 * @package Neoassist\ProtocolExport
 */
class Config
{

    /**
     * Subdomínio informado pelos consultores neoassist
     * [subdomínio].neoassist.com
     *
     * @var string
     */
    public $subdomain;

    /**
     * AppKey informado pelos consultores neoassist
     *
     * @var string
     */
    public $appkey;

    /**
     * AppSecret informado pelos consultores neoassist
     *
     * @var string
     */
    public $appsecret;

    /**
     * Habilita a paginação automática
     *
     * @var boolean
     */
    public $paginate = true;

    /**
     * Número de requesições paralelas (api)
     *
     * @var boolean
     */
    public $workers = 100;

    /**
     * @param string $appkey
     * @param string $appsecret
     * @param string $subdomain
     */
    public function __construct(
        $appkey = "",
        $appsecret = "",
        $subdomain = "",
        $paginate = true,
        $workers = 100
    ) {
        $this->subdomain = $subdomain;
        $this->appkey = $appkey;
        $this->appsecret = $appsecret;
        $this->paginate = $paginate;
        $this->workers = $workers;
    }
}
