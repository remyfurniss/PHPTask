<?php
    include_once 'ScraperFunctions.php';
    include_once 'SubscriptionObject.php';
    use PHPUnit\Framework\TestCase;

    class ScraperFunctionsTest extends TestCase
    {

        /**
         * @dataProvider subscriptionJSONProvider
         *
         * Test if a produced and expected JSON subscription are the same
         */
        public function testGetSubscriptionsAsJSON(string $producedSubscriptionJSON,
                                                   string $expectedSubscriptionJSON): void
        {
            $this->assertSame($expectedSubscriptionJSON,
                              $producedSubscriptionJSON);
        }

        /**
         * @return array[]
         *
         * The data provider of JSON subscriptions
         */
        public function subscriptionJSONProvider() : array
        {
            $html = file_get_contents('https://videx.comesconnected.com/');

            //create a new DOMDocument
            $videx_doc = new DOMDocument();

            //disable libxml errors
            libxml_use_internal_errors(TRUE);

                //load html into doc
                $videx_doc->loadHTML($html);

                //remove errors for html
                libxml_clear_errors();

                //Create new DOMX path for the doc
                $videx_xpat = new DOMXPath($videx_doc);

                //Get all the subscriptions as a JSON values in an array
                $subscriptions = ScraperFunctions::getSubscriptionsAsJSONArray($videx_xpat);

                //create the first test object
                $subObj0 = new SubscriptionObject("Option 300 Mins",
                    "300 minutes talk time per monthincluding 40 SMS(5p / minute and 4p / SMS thereafter)",
                        192,
                      null);

                return [[json_encode(json_decode($subscriptions[0])), json_encode($subObj0)]];
        }
    }
?>
