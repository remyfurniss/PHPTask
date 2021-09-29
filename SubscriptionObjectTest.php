<?php
    include_once 'SubscriptionObject.php';
    use PHPUnit\Framework\TestCase;

    class SubscriptionObjectTest extends TestCase{

        public function testSubscriptionObject(): void
        {
            $this->assertInstanceOf(
                SubscriptionObject::class,
                new SubscriptionObject('title','description',100,5)
            );
        }

    }

?>