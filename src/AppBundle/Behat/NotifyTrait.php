<?php

namespace AppBundle\Behat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait NotifyTrait
{
    /**
     * @return string
     */
    private function getNotifyMockBaseUrl()
    {
        return getenv('DC_NOTIFY_MOCK_ENDPOINT');
    }

    /**
     * @return array
     */
    private function getNotifyMockSentMails()
    {
        return json_decode(file_get_contents($this->getNotifyMockBaseUrl() . '/mock-data'), 1);
    }

    /**
     * @Given I reset the email log
     */
    public function iResetTheEmailLog()
    {
        $stream = stream_context_create(['http' => ['method' => 'DELETE']]);
        file_get_contents($this->getNotifyMockBaseUrl() . '/mock-data', false, $stream);

        if (count($this->getNotifyMockSentMails()) > 0) {
            throw new \RuntimeException("error resetting email");
        }

    }


    /**
     * @Then there should be no email sent to :to
     */
    public function assertNoEmailShouldHaveBeenSent($to)
    {
        $messages = $this->getNotifyMockSentMails();
        foreach($messages as $message) {
            if ($message['email_address'] == $to) {
                throw new \RuntimeException("Found at least one mail sent to $to");
            }
        }
    }


    /**
     * @When I click on the link in the email sent to :to
     */
    public function IclikLinkEmaiSentTo($to)
    {
        $messages = $this->getNotifyMockSentMails();
        foreach($messages as $message) {
            if ($message['email_address'] == $to) {
                $this->visit($message['personalisation']['activationLink']);
                return;
            }
        }

        throw new \RuntimeException("No email sent to $to");
    }


}
