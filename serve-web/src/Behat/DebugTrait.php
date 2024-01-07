<?php

namespace App\Behat;

use Behat\Behat\Hook\Scope\AfterStepScope;
use Behat\Behat\Tester\Result\ExecutedStepResult;

trait DebugTrait
{
    private $behatDebugDir = '/tmp/behat/';

    /**
     * @Then /^debug$/
     *
     * @param null|mixed $feature
     * @param null|mixed $line
     */
    public function debug($feature = null, $line = null): void
    {
        $filename = $feature.time().'.html';

        $session = $this->getSession();
        $data = $session->getPage()->getContent();
        file_put_contents($this->behatDebugDir.$filename, $data);
        echo '- Url: '.$session->getCurrentUrl()."\n";
        echo "- View response at: https://localhost/behat/{$filename}\n";
    }

    /**
     * @Then I save the page as :name
     */
    public function iSaveThePageAs(string $name): void
    {
        $filename = $this->behatDebugDir.'/screenshot-'.$name.'.html';

        $data = $this->getSession()->getPage()->getContent();
        if (!file_put_contents($filename, $data)) {
            echo "Cannot write screenshot into {$filename} \n";
        }
    }

    /**
     * Call debug() when an exception is thrown after as step.
     *
     * @AfterStep
     */
    public function debugOnException(AfterStepScope $scope): void
    {
        if (($result = $scope->getTestResult())
            && $result instanceof ExecutedStepResult
            && $result->hasException()
        ) {
            $feature = basename($scope->getFeature()->getFile());
            $line = $scope->getFeature()->getLine();
            $this->debug($feature, $line);
        }
    }

    /**
     * @Then die :code
     * @Then exit :code
     *
     * @param mixed $code
     */
    public function interrupt($code): never
    {
        exit($code);
    }
}
