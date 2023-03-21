<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\SessionInterface;
use App\DataObjects\DataTableQueryParams;
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

    public function isXhr(ServerRequestInterface $request): bool
    {
        return $request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    public function getDataTableQueryParameters(ServerRequestInterface $request): DataTableQueryParams
    {
        // Obtener los parámetros de consulta de la solicitud
        $params = $request->getQueryParams();

        // Obtener la columna de ordenación y la dirección de ordenación
        $orderBy = $params['columns'][$params['order'][0]['column']]['data'];
        $orderDir = $params['order'][0]['dir'];

        // Crear y devolver una nueva instancia de DataTableQueryParams con los valores extraídos de la solicitud
        return new DataTableQueryParams(
            (int) $params['start'],      // Índice de inicio para la paginación
            (int) $params['length'],     // Cantidad de registros por página
            $orderBy,                    // Columna de ordenación
            $orderDir,                   // Dirección de ordenación (asc o desc)
            $params['search']['value'],  // Término de búsqueda
            (int) $params['draw']        // Contador para el control de sincronización entre cliente y servidor
        );

    }
}