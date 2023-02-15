<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestService
{
    public function __construct(private SessionInterface $session)
    {
    }

    public function getReferer(ServerRequestInterface $request): String
    {
       $referer = $request->getHeader('referer')[0] ?? '';

       if(!$referer){
           return $this->session->get('previousURL');
       }

       $refererHost = parse_url($referer,PHP_URL_HOST);

       if ($refererHost !== $request->getUri()->getHost()){
           $referer = $this->session->get('previousURL');
       }

       return $referer;

    }
}