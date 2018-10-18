<?php

namespace AppBundle\Behat;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

trait MailHogTrait
{
    /**
     * @return array
     */
    private function getMailHogMessages()
    {
        return json_decode(file_get_contents(getenv('MAILHOG_API_ENDPOINT') . '/v1/messages'), 1);
    }

    /**
     * @Given I reset the email log
     */
    public function iResetTheEmailLog()
    {
        $stream = stream_context_create(['http' => ['method' => 'DELETE']]);
        echo file_get_contents(getenv('MAILHOG_API_ENDPOINT') . '/v1/messages', false, $stream);

        if (count($this->getMailHogMessages()) >0) {
            throw new \RuntimeException("error resetting email");
        }

    }


    /**
     * @Then no email should have been sent
     */
    public function assertNoEmailShouldHaveBeenSent()
    {
        $count = count($this->getMailHogMessages());
        if ($count >0) {
            throw new \RuntimeException("$count mails found. 0 expected");
        }
    }


    /**
     * @When I click on the link in the email sent to :to
     */
    public function IClikLinkEmaiSentTo($to)
    {
        $messages = $this->getMailHogMessages();
        foreach($messages as $message) {
            if ($message['To'][0]['Mailbox'].'@'.$message['To'][0]['Domain'] == $to) {
                $body =  $message['Content']['Body'];
                preg_match('#https?://[\/\w-]+#', $body,$links);
                if (empty($links)) {
                    throw new \RuntimeException("No link found in the email");
                }
                $this->visit($links[0]);
            }
        }

        throw new \RuntimeException("No email sent to $to");
    }

    /**
     * @Then there should be no email sent to :to
     */
    public function thereShouldBeNoEmailSentTo($to)
    {
        $messages = $this->getMailHogMessages();
        foreach($messages as $message) {
            if ($message['To'][0]['Mailbox'].'@'.$message['To'][0]['Domain'] == $to) {
                throw new \RuntimeException("Unexpected mail sent to $to");
            }
        }
    }

}
