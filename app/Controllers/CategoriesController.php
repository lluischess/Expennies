<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\RequestValidators\CreateCategoryRequestValidator;
use App\RequestValidators\UpdateCategoryRequestValidator;
use App\Services\CategoryService;
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
                                 private ResponseFormatter $responseFormatter)
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

        return $response->withHeader('Location', '/categories')->withStatus(302);
    }

    public function delete(Request $request, Response $response, array $args ) : Response
    {
        $this->categoryService->delete((int) $args['id']);

        //return $response->withHeader('Location', '/categories')->withStatus(302);
        return $response;
    }

    public function get(Request $request, Response $response, array $args ) : Response
    {
        $category = $this->categoryService->getById((int) $args['id']);

        if (!$category){
            return $response->withStatus(404);
        }

        $data = ['id' => $category->getId(), 'name' => $category->getName()];

        return $this->responseFormatter->asJson($response, $data);
    }

    public function update(Request $request, Response $response, array $args ) : Response
    {
        // Validacion de los datos de la request
        $data = $this->requestValidatorFactory->make(UpdateCategoryRequestValidator::class)->validate(
            $args + $request->getParsedBody()
        );

        $category = $this->categoryService->getById((int) $data['id']);

        if (!$category){
            return $response->withStatus(404);
        }

        $this->categoryService->update($category,$data['name']);

        return $response;
    }

    public function load(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();

        $categories = array_map(function (Category $category) {
            return [
                'id'        => $category->getId(),
                'name'      => $category->getName(),
                'createdAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
                'updatedAt' => $category->getCreatedAt()->format('m/d/Y g:i A'),
            ];
        }, $this->categoryService->getAll());

        return $this->responseFormatter->asJson(
            $response,
            [
                'data'            => $categories,
                'draw'            => (int) $params['draw'],
                'recordsTotal'    => count($categories),
                'recordsFiltered' => count($categories),
            ]
        );
    }
}