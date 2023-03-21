<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\RequestValidators\CreateCategoryRequestValidator;
use App\RequestValidators\UpdateCategoryRequestValidator;
use App\Services\CategoryService;
use App\Services\RequestService;
use App\Contracts\RequestValidatorFactoryInterface;
use App\Contracts\AuthInterface;
use App\ResponseFormatter;
use Slim\Views\Twig;
use App\Entity\Category;

class CategoriesController
{

    public function __construct( private Twig $twig,
                                 private AuthInterface $auth,
                                 private CategoryService $categoryService,
                                 private RequestValidatorFactoryInterface $requestValidatorFactory,
                                 private ResponseFormatter $responseFormatter,
                                 private RequestService $requestService)
    {
    }

    public function index(Request $request, Response $response): Response
    {
        return $this->twig->render($response, 'categories/index.twig');
    }

    public function store(Request $request, Response $response) : Response
    {
        // Validacion de los datos de la request
        $data = $this->requestValidatorFactory->make(CreateCategoryRequestValidator::class)->validate(
            $request->getParsedBody()
        );

        // Create new category record in the database
        $this->categoryService->create($data['name'], $request->getAttribute('user'));

        // Devolvemos la respuesta redirecciónando a categories
        return $response->withHeader('Location', '/categories')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args ) : Response
    {
        // Llamamos al this class categoryService que lo tenemos en el constructor para que utilice su funcion delete asignandole el id de la categoria
        $this->categoryService->delete((int) $args['id']);

        //return $response->withHeader('Location', '/categories')->withStatus(302);
        return $response;
    }

    public function get(Request $request, Response $response, array $args ) : Response
    {
        // Obtener la categoría por su ID utilizando el servicio 'categoryService'
        $category = $this->categoryService->getById((int) $args['id']);

        // Si la categoría no se encuentra, devolver una respuesta con estado 404
        if (!$category){
            return $response->withStatus(404);
        }

        // Crear un array asociativo con los datos de la categoría
        $data = ['id' => $category->getId(), 'name' => $category->getName()];

        // Devolver una respuesta JSON con los datos de la categoría
        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args ) : Response
    {
        // Validacion de los datos de la request
        $data = $this->requestValidatorFactory->make(UpdateCategoryRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        // Obtener la categoría por su ID utilizando el servicio 'categoryService'
        $category = $this->categoryService->getById((int) $data['id']);

        // Si la categoría no se encuentra, devolver una respuesta con estado 404
        if (!$category){
            return $response->withStatus(404);
        }

        // Enviamos los datos al update de categoriSercice para que actualice la categoria
        $this->categoryService->update($category,$data['name']);

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {

        // Obtener los parámetros de consulta para la tabla de datos a partir de la solicitud
        $params = $this->requestService->getDataTableQueryParameters($request);

        // Obtener las categorías paginadas usando los parámetros de consulta
        $categories = $this->categoryService->getPaginatedCategories($params);

        // Definir una función transformadora para convertir cada objeto de la categoría en un array asociativo
        $transformer = function (Category $category) {
            return [
                'id'        => $category->getId(),
                'name'      => $category->getName(),
                'createdAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
                'updatedAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
            ];
        };

        // Devolver una respuesta JSON con los datos de las categorías transformados y la información de paginación
        return $this->responseFormatter->asJson(
            $response,
            [
                'data'            => array_map($transformer, (array) $categories->getIterator()),
                'draw'            => $params->draw,
                'recordsTotal'    => count($categories),
                'recordsFiltered' => count($categories),
            ]
        );
    }
}