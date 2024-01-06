<?php

namespace App\Behat;

trait DebugTrait
{
    private $behatDebugDir = '/tmp/behat/';

    /**
     * @Then /^debug$/
     */
    public function debug($feature = null, $line = null): void
    {
        $filename = $feature . time() . '.html';

        $session = $this->getSession();
        $data = $session->getPage()->getContent();
        file_put_contents($this->behatDebugDir . $filename, $data);
        echo '- Url: ' . $session->getCurrentUrl() . "\n";
        echo "- View response at: https://localhost/behat/{$filename}\n";
    }

    /**
     * @Then I save the page as :name
     */
    public function iSaveThePageAs($name): void
    {
        $filename = $this->behatDebugDir . '/screenshot-' . $name . '.html';

        $data = $this->getSession()->getPage()->getContent();
        if (!file_put_contents($filename, $data)) {
            echo "Cannot write screenshot into $filename \n";
        }
    }

    /**
     * Call debug() when an exception is thrown after as step.
     *
     * @AfterStep
     */
    public function debugOnException(\Behat\Behat\Hook\Scope\AfterStepScope $scope): void
    {
        if (($result = $scope->getTestResult())
            && $result instanceof \Behat\Behat\Tester\Result\ExecutedStepResult
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
     */
    public function interrupt($code): void
    {
        die($code);
    }
}
