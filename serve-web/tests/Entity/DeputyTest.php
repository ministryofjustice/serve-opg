<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Deputy;
use App\TestHelpers\OrderTestHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

class DeputyTest extends TestCase
{
    public function testDeputyBlankFields()
    {
        $timeNow = new \DateTime('now');

        $deputy = new Deputy(OrderTestHelper::generateOrder('2016-01-01', '2016-01-02', '16472847', 'HW', $timeNow->format('Y-m-d')));

        $deputy->setDateOfBirth(null);
        $deputy->setEmailAddress('');
        $deputy->setAddressLine1('');
        $deputy->setAddressTown('');
        $deputy->setAddressPostcode('');

        $validator = Validation::createValidatorBuilder()->enableAnnotationMapping(true)->getValidator();
        $errors = $validator->validate($deputy);

        $this->assertEquals(6, count($errors));
    }
}
