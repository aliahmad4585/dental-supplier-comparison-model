<?php

namespace App\Service;

use App\Exception\ComparisonServiceException;

class ComparisonService
{

    /**
     * Get cheaper supplier
     * 
     * @param array $payload
     * 
     * @return array
     */
    public function getCheaperSupplier(array $payload): array | string
    {
        try {
            $payloadParams =  $payload['params'];
            $products  = $this->getLoadedProducts();
            $suppliers = [];

            /**
             * iterate the every product entered by user
             * get the supplier with price for each product enter by user
             */
            foreach ($payloadParams as $value) {
                /** get the products which match the criteria enter by user*/
                $type = $value['type'];
                $unit = $value['unit'];
                $end =  count($products);
                $start = 0;
                $filterProducts = $this->filterProducts($products, $type, $unit, array(), $start, $end);

                /** calculate the price of each product group by supplier */
                $start =  count($filterProducts) - 1;
                $end = 0;
                $price = 0;
                $lastSupplier = $filterProducts[$start]['supplier'];
                $supplierWithPrice = $this->calculatePrice($filterProducts, array(), $lastSupplier, $start, $end, $unit, $unit, $price);
                $suppliers = $this->sumSupplierPrices($supplierWithPrice, $suppliers);
            }

            /** Sort the suppliers in asceding order and first supplier treated as cheap supplier */
            if (count($suppliers)) {
                asort($suppliers);
                $supplierName = array_key_first($suppliers);
                $price = $suppliers[$supplierName];

                return ["Supplier" => $supplierName, "price" => $price];
            }

            return $suppliers;
        } catch (ComparisonServiceException $th) {
            throw new ComparisonServiceException("Get cheaper supplier exception: " . $th->getMessage());
        }
    }


    /** 
     * sum values of all suppliers
     * every supplier has price against each product
     * if user search with multiple products then add price of each product
     * to every supplier after calculation
     * 
     * @param array $supplierWithPrice
     * @param array $suppliers
     * 
     * @return array $suppliers
     */
    private function sumSupplierPrices(array $supplierWithPrice, array $suppliers): array
    {
        foreach ($supplierWithPrice as $key => $value) {
            if (key_exists($key, $suppliers)) {
                $suppliers[$key] =  $suppliers[$key] + $value;
                continue;
            }
            $suppliers[$key] = $value;
        }
        return $suppliers;
    }

    /**
     *  Calculate the price of each product by each supplier
     *  will return the supplier with price
     * 
     *  ################# Logic #########################
     *   1. Divid the unit enter by user with individual product unit
     *   2. get absolute quotient and multiply the quotient with single product price
     *   3. add the price which gets in above step in total price
     *   4. decrease the unit by using this formula ($unit - ($productUnit * $absQuotient))
     * 
     * @param array $filterProducts
     * @param array $supplierArr
     * @param string $supplier
     * @param int    $start
     * @param int    $end
     * @param int    $unit
     * @param int    $unChangedUnit
     * @param int    $price
     * 
     * @return array $supplierArr
     * 
     */
    private function calculatePrice(array $filterProducts, array $supplierArr, string $supplier, int $start, int $end, int $unit, int $unChangedUnit, int $price): array
    {
        try {
            // recursion termination condition
            if ($start == $end - 1) {
                $supplierArr[$supplier] = $price;
                return $supplierArr;
            }

            if ($filterProducts[$start]['supplier'] == $supplier) {

                $productUnit = $filterProducts[$start]['unit'];

                $quotient =  $unit / $productUnit;
                $quotientArr = explode('.', $quotient);
                $absQuotient = (int) $quotientArr[0];

                $productPrice =  $filterProducts[$start]['price'];
                $productPrice = $absQuotient * $productPrice;
                $price = $price + $productPrice;
                $unit = $unit - ($productUnit * $absQuotient);
            }

            $start = $start - 1;

            /** if the supplier changed then reset the values */
            if (isset($filterProducts[$start]) && $filterProducts[$start]['supplier'] != $supplier) {
                $supplierArr[$supplier] = $price;
                $unit =  $unChangedUnit;
                $price = 0;
                $supplier =  $filterProducts[$start]['supplier'];
            }

            $price =  $this->calculatePrice($filterProducts, $supplierArr, $supplier, $start, $end, $unit, $unChangedUnit, $price);
            return $price;
            //code...
        } catch (ComparisonServiceException $th) {
            throw new ComparisonServiceException("Calculate price exception: " . $th->getMessage());
        }
    }


    /**
     *  #################  Assumption for real world application   ###############
     * In real word, this part will move at the database level
     * We will fetch all the records from database where unit <= userEnteredUnits and product = userEnteredProduct
     * Data will be sorted by unit desc 
     *  
     * #################  Current functionality #############
     * This data will filtered the data from hardcoded arrays
     * Condition will be unit <= userEnteredUnits and product = userEnteredProduct
     * use recursion to filter the data
     * 
     * @param array  $products
     * @param string $productType
     * @param int    $unit
     * @param array  $filteredProducts
     * @param int    $start
     * @param int    $end
     * 
     * @return array $filteredProducts
     * 
     * */

    private function filterProducts(array $products, string $productType, int $unit, array $filteredProducts, int $start, int $end): array
    {

        try {

            if ($start >= $end) {
                return $filteredProducts;
            }

            if ($products[$start]['type'] == $productType && $products[$start]['unit'] <= $unit) {
                array_push($filteredProducts, $products[$start]);
            }

            $start =  $start + 1;
            $filteredProducts = $this->filterProducts($products, $productType, $unit, $filteredProducts, $start, $end);

            return $filteredProducts;
        } catch (ComparisonServiceException $th) {
            throw new ComparisonServiceException("Filter product exception: " . $th->getMessage());
        }
    }

    /**
     * return the hardcoded data for operations
     * 
     * @return array $products
     */
    public function getLoadedProducts(): array
    {
        $produts = array(
            array("type" => "Dental Floss", "unit" => 1, "price" => 9, "supplier" => "supplier A"),
            array("type" => "Dental Floss", "unit" => 20, "price" => 160,  "supplier" => "supplier A"),
            array("type" => "Ibuprofen", "unit" => 1, "price" => 5,  "supplier" => "supplier A"),
            array("type" => "Ibuprofen", "unit" => 10, "price" => 48,  "supplier" => "supplier A"),
            array("type" => "Dental Floss", "unit" => 1, "price" => 8,  "supplier" => "supplier B"),
            array("type" => "Dental Floss", "unit" => 10, "price" => 71,  "supplier" => "supplier B"),
            array("type" => "Ibuprofen", "unit" => 1, "price" => 6,  "supplier" => "supplier B"),
            array("type" => "Ibuprofen", "unit" => 5, "price" => 25,  "supplier" => "supplier B"),
            array("type" => "Ibuprofen", "unit" => 100, "price" => 410,  "supplier" => "supplier B"),
        );

        return $produts;
    }
}
