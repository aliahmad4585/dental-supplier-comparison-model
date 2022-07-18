<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ComparisonService;
use Symfony\Component\HttpFoundation\Request;
use App\Validator\ComparisonRequestValidator;
use App\Exception\ComparisonServiceException;

class ComparisonController extends AbstractController
{

    /**
     * @param Symfony\Component\HttpFoundation\Request $request
     * @param App\Validator\ComparisonRequestValidator $validator
     * @param App\Service\ComparisonService $comparisonService
     * 
     * @return Symfony\Component\HttpFoundation\JsonResponse
     */

    #[Route('/api/comparison', name: 'api_comparison')]
    public function comparison(Request $request, ComparisonRequestValidator $validator, ComparisonService $comparisonService): JsonResponse
    {
        try {
            $payload =  $request->toArray();
            $errors = $validator->validate($payload);
            if (count($errors)) {
                return $this->json($errors);
            }

            $cheaperSupplier = $comparisonService->getCheaperSupplier($payload);
            return $this->json(["result" => $cheaperSupplier]);
        } catch ( ComparisonServiceException | \Throwable $th) {
            return $this->json(["error" => $th->getMessage()]);
        }
    }
}
