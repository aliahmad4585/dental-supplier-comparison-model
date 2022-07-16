<?php

namespace App\Validator;

use App\Service\ComparisonService;

class ComparisonRequestValidator
{


     private $products;

     /**
      * @var App\Service\ComparisonService $comparisonService
      * 
      * */
     public function __construct(ComparisonService $comparisonService)
     {
          $this->products =  $comparisonService->getLoadedProducts();
     }
     /**
      * validate the json payload
      *
      * @param array $payload
      * 
      * @return array $errors
      */

     public function validate(array $payload): array
     {

          $errors =  [];

          if (!count($payload) && !count($payload['params'])) {
               $errors['params'] =  "payload must have key name 'params' with atleast one entry ";
               return $errors;
          }

          foreach ($payload['params'] as $value) {
               if (!isset($value['type']) || empty($value['type'])) {
                    $errors['type'] = "params object must contain the 'type' key with valid value";
                    break;
               }
               if (!in_array($value['type'], array_column($this->products, 'type'))) {
                    $errors['type'] = "type doesn't exist";
                    break;
               }
               if (!isset($value['unit']) || empty($value['unit']) || $value['unit'] <= 0) {
                    $errors['unit'] = "params object must contain the 'unit' key with valid value greater than 0";
                    break;
               }
          };
          return $errors;
     }
}
