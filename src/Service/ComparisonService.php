<?php

namespace App\Service;

class ComparisonService
{

    /**
     * Get all suppliers with price based on user input
     * 
     * @param array $payload
     * 
     * @return array
     */
    public function getCheaperSupplier(array $payload): array | string
    {
        $payloadParams =  $payload['params'];
        $products  = $this->getLoadedProducts();
        $suppliers = [];

        /**
         * iterate the every product entered by user
         * get the supplier with price for each product 
         */
        foreach ($payloadParams as $value) {
            $type = $value['type'];
            $unit = $value['unit'];
            $end =  count($products);
            $start = 0;
            $filterProducts = $this->filterProducts($products, $type, $unit, array(), $start, $end);
            $start =  count($filterProducts) - 1;
            $end = 0;
            $price = 0;
            $lastSupplier = $filterProducts[$start]['supplier'];
            $supplierWithPrice = $this->calculatePrice($filterProducts, array(), $lastSupplier, $start, $end, $unit, $unit, $price);
            $suppliers = $this->sumSupplierPrices($supplierWithPrice, $suppliers);
        }

        /** Sort the suppliers array asceding and first supplier treated as cheap supplier */
        if (count($suppliers)) {
            asort($suppliers);
            $supplierName = array_key_first($suppliers);
            $value = $suppliers[$supplierName];

            return $supplierName . ' is cheaper - ' . $value . ' value';
        }

        return $suppliers;
    }


    /** 
     * sum values of all suppliers
     * every supplier has value against with each product
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
     *  Calculate the price of each product by each cutomer
     *  will return the supplier with price
     *  ################# Logic #########################
     *   1. Divid the unit enter by user with product unit
     *   2. get absolute quotient and mulitply the quotient with single product price
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
    private function calculatePrice(
        array $filterProducts,
        array $supplierArr,
        string $supplier,
        int $start,
        int $end,
        int $unit,
        int $unChangedUnit,
        int $price
    ) {

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

    private function filterProducts(
        array $products,
        string $productType,
        int $unit,
        array $filteredProducts,
        int $start,
        int $end
    ): array {

        if ($start >= $end) {
            return $filteredProducts;
        }

        if ($products[$start]['type'] == $productType && $products[$start]['unit'] <= $unit) {
            array_push($filteredProducts, $products[$start]);
        }

        $start =  $start + 1;
        $filteredProducts = $this->filterProducts($products, $productType, $unit, $filteredProducts, $start, $end);

        return $filteredProducts;
    }

    /**
     * return the hardcoded data for operations
     * 
     * @return array $products
     */
    public function getLoadedProducts(): array
    {
        $produts = array(
            array("type" => "Dental Floss", "unit" => 1, "price" => 9, "supplier" => "supplierA"),
            array("type" => "Dental Floss", "unit" => 20, "price" => 160,  "supplier" => "supplierA"),
            array("type" => "Ibuprofen", "unit" => 1, "price" => 5,  "supplier" => "supplierA"),
            array("type" => "Ibuprofen", "unit" => 10, "price" => 48,  "supplier" => "supplierA"),
            array("type" => "Dental Floss", "unit" => 1, "price" => 8,  "supplier" => "supplierB"),
            array("type" => "Dental Floss", "unit" => 10, "price" => 71,  "supplier" => "supplierB"),
            array("type" => "Ibuprofen", "unit" => 1, "price" => 6,  "supplier" => "supplierB"),
            array("type" => "Ibuprofen", "unit" => 5, "price" => 25,  "supplier" => "supplierB"),
            array("type" => "Ibuprofen", "unit" => 100, "price" => 410,  "supplier" => "supplierB"),
        );

        return $produts;
    }
}
