<?php
include_once 'SubscriptionObject.php';

class ScraperFunctions
{

    private const MONTHLYSUBSCRIPTIONSQUERY = '//*[@id="subscriptions"]/div/div[4]/div';
    private const YEARLYSUBSCRIPTIONSQUERY = '//*[@id="subscriptions"]/div[4]/div';
    private const TITLEQUERY = 'div/div/div[1]/h3';
    private const DESCRIPTIONQUERY = 'div/div/div[2]/ul/li[1]/div';
    private const PRICEQUERY = 'div/div/div[2]/ul/li[2]/div/span';
    private const DISCOUNTQUERY = 'div/div/div[2]/ul/li[2]/div/p';

    /**
     * @param DOMNodeList $titleNodes
     * @return array
     *
     * Takes the title node list and changes to an array
     */
    private static function titleNodesToTitleArray(DOMNodeList $titleNodes): array
    {
        $titleArr = array();
        if ($titleNodes->length > 0) {
            foreach ($titleNodes as $titleNode) {
                array_push($titleArr, $titleNode->nodeValue);
            }
        }
        return $titleArr;
    }

    /**
     * @param DOMNodeList $descriptionNodes
     * @return array
     *
     * Takes the description node list and changes to an array
     */
    private static function descriptionNodesToDescriptionArray(DOMNodeList $descriptionNodes): array
    {
        $descriptionArr = array();
        if ($descriptionNodes->length > 0) {
            foreach ($descriptionNodes as $descriptionNode) {
                array_push($descriptionArr, $descriptionNode->nodeValue);
            }
        }
        return $descriptionArr;
    }

    /**
     * @param float $monthlyPrice
     * @return float
     *
     * Changes the monthly cost to an annual cost
     */
    private static function monthlyPriceToAnnual(float $monthlyPrice): float
    {
        return round($monthlyPrice * 12, 2);
    }

    /**
     * @param DOMNodeList $priceNodes
     * @param bool $isMonthly
     * @return array
     *
     * Takes the price nodes and changes to array
     */
    private static function priceNodesToPriceArray(DOMNodeList $priceNodes, bool $isMonthly): array
    {
        $priceArr = array();
        if ($priceNodes->length > 0) {
            foreach ($priceNodes as $priceNode) {
                //Remove pound and convert to float
                $priceValue = floatval(ltrim($priceNode->nodeValue, '£'));
                //If monthly convert to annual cost
                if ($isMonthly) {
                    array_push($priceArr, self::monthlyPriceToAnnual($priceValue));
                } else {
                    array_push($priceArr, $priceValue);
                }
            }
        }
        return $priceArr;
    }

    /**
     * @param DOMNodeList $discountNodes
     * @return array
     *
     * Takes the discount nodes and extracts the discount value (float)
     */
    private static function discountNodesToDiscountArray(DOMNodeList $discountNodes): array
    {
        $discountArr = array();
        if ($discountNodes->length > 0) {
            foreach ($discountNodes as $discountNode) {
                $discountValue = $discountNode->nodeValue;
                //remove characters before £
                $discountValue = strstr($discountValue, '£');
                //remove characters after first space
                $discountValue = strtok($discountValue, " ");
                //Remove £
                $discountValue = ltrim($discountValue, '£');
                array_push($discountArr, floatval($discountValue));
            }
        }
        return $discountArr;
    }

    /**
     * @param DOMXPath $videx_xpath
     * @param DOMNode $subscriptionNode
     * @return array
     *
     * Gets all the titles for the given DOMXPath and DOMNode
     */
    private static function getTitlesAsArray(DOMXPath $videx_xpath, DOMNode $subscriptionNode): array
    {
        $titleNodes = $videx_xpath->query(self::TITLEQUERY, $subscriptionNode);
        return self::titleNodesToTitleArray($titleNodes);
    }

    /**
     * @param DOMXPath $videx_xpath
     * @param DOMNode $subscriptionNode
     * @return array
     *
     * Gets all the descriptions for the given DOMXPath and DOMNode
     */
    private static function getDescriptionsAsArray(DOMXPath $videx_xpath, DOMNode $subscriptionNode): array
    {
        $descriptionNodes = $videx_xpath->query(self::DESCRIPTIONQUERY, $subscriptionNode);
        return self::descriptionNodesToDescriptionArray($descriptionNodes);
    }

    /**
     * @param DOMXPath $videx_xpath
     * @param DOMNode $subscriptionNode
     * @param bool $isMonthly
     * @return array
     *
     * Gets all the prices for the given DOMXPath, DOMNode, and if its a monthly subscription or not
     */
    private static function getPricesAsArray(DOMXPath $videx_xpath, DOMNode $subscriptionNode, bool $isMonthly): array
    {
        $priceNodes = $videx_xpath->query(self::PRICEQUERY, $subscriptionNode);
        return self::priceNodesToPriceArray($priceNodes, $isMonthly);
    }

    /**
     * @param DOMXPath $videx_xpath
     * @param DOMNode $subscriptionNode
     * @param bool $isMonthly
     * @param int $optionCount
     * @return array
     *
     * Gets all the discounts for the given DOMXPath, DOMNode, and if its a monthly subscription or not
     */
    private static function getDiscountsAsArray(DOMXPath $videx_xpath, DOMNode $subscriptionNode, bool $isMonthly, int $optionCount): array
    {
        $discountArr = array();
        //If monthly subscription there is no discount
        if ($isMonthly) {
            $discountArr = array_fill(0, $optionCount, null);
        } else {
            $discountNodes = $videx_xpath->query(self::DISCOUNTQUERY, $subscriptionNode);
            $discountArr = self::discountNodesToDiscountArray($discountNodes);
        }
        return $discountArr;
    }

    /**
     * @param array $titleArr
     * @param array $descriptionArr
     * @param array $priceArr
     * @param array $discountArr
     * @return array
     *
     * Combine all attribute arrays into an object array for each attribute
     */
    private static function createObjectArray(array $titleArr, array $descriptionArr, array $priceArr, array $discountArr): array
    {
        $subscriptionObjectArr = array();
        for ($i = 0; $i < count($titleArr); $i++) {
            $title = $titleArr[$i];
            $description = $descriptionArr[$i];
            $price = $priceArr[$i];
            $discount = $discountArr[$i];
            $subscriptionObject = new SubscriptionObject($title, $description, $price, $discount);
            array_push($subscriptionObjectArr, $subscriptionObject);
        }
        return $subscriptionObjectArr;
    }

    /**
     * @param DOMXPath $videx_xpath
     * @param DOMNode $subscriptionNode
     * @param bool $isMonthly
     * @return array
     *
     * Collects the Object array for all attribute arrays into an object array for each attribute
     */
    private static function getSubscriptionsAsObjectArray(DOMXPath $videx_xpath, DOMNode $subscriptionNode, bool $isMonthly): array
    {
        //GET TITLES
        $titleArr = self::getTitlesAsArray($videx_xpath, $subscriptionNode);

        //GET DESCRIPTIONS
        $descriptionArr = self::getDescriptionsAsArray($videx_xpath, $subscriptionNode);

        //GET PRICES
        $priceArr = self::getPricesAsArray($videx_xpath, $subscriptionNode, $isMonthly);

        //GET DISCOUNTS
        $discountArr = self::getDiscountsAsArray($videx_xpath, $subscriptionNode, $isMonthly, count($titleArr));

        return self::createObjectArray($titleArr, $descriptionArr, $priceArr, $discountArr);
    }

    /**
     * @param DOMXPath $videx_xpath
     * @return array
     *
     * Get the montlhy subscriptions as object array
     */
    private static function getMonthlySubscriptions(DOMXPath $videx_xpath): array
    {
        $monthlySubscriptionsNodes =
            $videx_xpath->query(self::MONTHLYSUBSCRIPTIONSQUERY);
        $monthlySubscriptionsNode = $monthlySubscriptionsNodes[0];
        return self::getSubscriptionsAsObjectArray($videx_xpath, $monthlySubscriptionsNode, true);
    }

    /**
     * @param DOMXPath $videx_xpath
     * @return array
     *
     * Get the yearly subscriptions as object array
     */
    private static function getYearlySubscriptions(DOMXPath $videx_xpath): array
    {
        $yearlySubscriptionsNodes =
            $videx_xpath->query(self::YEARLYSUBSCRIPTIONSQUERY);
        $yearlySubscriptionsNode = $yearlySubscriptionsNodes[0];
        return self::getSubscriptionsAsObjectArray($videx_xpath, $yearlySubscriptionsNode, false);
    }

    /**
     * @param DOMXPath $videx_xpath
     * @return array
     *
     * Sort the subscription objects and by price descending and convert to JSON
     */
    public static function getSubscriptionsAsJSONArray(DOMXPath $videx_xpath): array
    {
        $yearlySubscriptions = self::getYearlySubscriptions($videx_xpath);
        $monthlySubscriptions = self::getMonthlySubscriptions($videx_xpath);
        //mereg all subscriptions into one array
        $allSubscriptions = array_merge($yearlySubscriptions, $monthlySubscriptions);
        //Sort on the objects price in descending order
        usort($allSubscriptions, fn($x, $y) => $y->getPrice() <=> $x->getPrice());
        $encodedSubscriptions = array();
        foreach ($allSubscriptions as $subscription) {
            $encodedSubscription = json_encode($subscription, JSON_PRETTY_PRINT);
            array_push($encodedSubscriptions, $encodedSubscription);
        }
        return $encodedSubscriptions;
    }

}

?>
