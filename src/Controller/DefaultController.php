<?php

namespace App\Controller;

use App\Transpiler\CustomQueryState;
use App\Transpiler\SuggestionManager;
use App\Transpiler\Transpiler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('default/index.html.twig');
    }

    #[Route('/validate', name: 'validate')]
    public function validate(
        Request $request,
        Transpiler $transpiler,
        SuggestionManager $suggestionManager
    ): JsonResponse {
        $data = $request->toArray();
        $rawQuery = $data['query'] ?? '';
        $caretPos = $data['caretPos'] ?? 0;
        $queryState = new CustomQueryState($rawQuery, $caretPos);

        $transpiler->transpile($queryState);
        $suggestionManager->addSuggestions($queryState);

        return new JsonResponse($queryState->toArray());
    }

    #[Route('/projects', name: 'projects')]
    public function projects(Request $request, Transpiler $transpiler, SuggestionManager $suggestionManager): Response
    {
        $projectsList = [];
        $data = $request->toArray();
        $rawQuery = $data['query'] ?? '';
        $caretPos = $data['caretPos'] ?? 0;
        $queryState = new CustomQueryState($rawQuery, $caretPos);

        $sqlQueryPart = '';
        if (!empty($rawQuery)) {
            $sqlQueryPart = $transpiler->transpile($queryState);
        }

        if ($queryState->isValid()) {
            $sqlQuery = 'SELECT * FROM project ' . $sqlQueryPart;
            $entityManager = $this->getDoctrine()->getManager();
            $connection = $entityManager->getConnection();
            $stmt = $connection->prepare($sqlQuery);
            $result = $stmt->execute();
            $data = $result->fetchAll();
            if (!empty($data)) {
                $projectsList = $data;
            }
        }

        $suggestionManager->addSuggestions($queryState);

        return new JsonResponse([
            'queryState' => $queryState->toArray(),
            'projectsList' => $projectsList
        ]);
    }

}
