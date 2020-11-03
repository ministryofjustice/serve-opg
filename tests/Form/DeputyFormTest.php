<?php

use App\Entity\Deputy;
use App\Form\DeputyForm;
use App\TestHelpers\OrderTestHelper;
use Symfony\Component\Form\Test\TypeTestCase;

class DeputyFormTest extends TypeTestCase
{
    public function testSubmitValidData()
    {
        $timeNow = new DateTime('now');

        $formData = [
            'deputyType' => 'LAY',
            'forename' => 'John',
            'surname' => 'Doe',
            'dateOfBirth' => [
                'day' => '1',
                'month' => '1',
                'year' => '1990'
            ],
            'emailAddress' => 'email@example.org',
            'addressLine1' => '10 Downing Street',
            'addressTown' => 'London',
            'addressPostcode' => 'SW1A 2AA',
        ];

        $model = new Deputy(OrderTestHelper::generateOrder('2016-01-01', '2016-01-02', '16472847', 'HW', $timeNow->format('Y-m-d')));

        $form = $this->factory->create(DeputyForm::class, $model);

        $expected = new Deputy(OrderTestHelper::generateOrder('2016-01-01', '2016-01-02', '16472847', 'HW', $timeNow->format('Y-m-d')));
        $expected->setDeputyType('LAY');
        $expected->setForename('John');
        $expected->setSurname('Doe');
        $expected->setDateOfBirth(new DateTime('01-01-1990'));
        $expected->setEmailAddress('email@example.org');
        $expected->setAddressLine1('10 Downing Street');
        $expected->setAddressTown('London');
        $expected->setAddressPostcode('SW1A 2AA');

        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }
}
