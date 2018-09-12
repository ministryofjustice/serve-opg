<?php

namespace AppBundle\Behat;

use Behat\Gherkin\Node\TableNode;

trait RegionLinksTrait
{
    /**
     * Assert that the HTML element with class behat-<type>-<element> does not exist.
     *
     * @Then I should not see the :element :type
     */
    public function iShouldNotSeeTheBehatElement($element, $type)
    {
        $this->assertResponseStatus(200);

        $regionCss = self::behatElementToCssSelector($element, $type);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $regionCss);
        $count = count($linksElementsFound);
        if ($count > 0) {
            throw new \RuntimeException("$count  $regionCss element(s) found. None expected");
        }
    }

    /**
     * @Then I should not see :text text
     */
    public function iShouldNotSee($text)
    {
        $this->assertResponseStatus(200);

        $this->assertSession()->elementTextNotContains('css', 'body', $text);
    }

    /**
     * Assert that the HTML element with class behat-<type>-<element> exists.
     *
     * @Then I should see the :element :type
     */
    public function iShouldSeeTheBehatElement($element, $type)
    {
        $regionCss = self::behatElementToCssSelector($element, $type);
        $found = count($this->getSession()->getPage()->findAll('css', $regionCss));
        if ($found !== 1) {
            throw new \RuntimeException("One $regionCss class expected, $found found");
        }
    }

    /**
     * Assert that the HTML element with class behat-<type>-<element> exist N times.
     *
     * @Then I should see the :element :type exactly :n times
     */
    public function iShouldSeeTheBehatElementNTimes($element, $type, $n)
    {
        $regionCss = self::behatElementToCssSelector($element, $type);
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $regionCss);
        if (($c = count($linksElementsFound)) != $n) {
            throw new \RuntimeException("Found $c instances of $regionCss, $n expected");
        }
    }


    /**
     * @Then I should see :text in the :region region
     */
    public function iShouldSeeInTheRegion($text, $region)
    {
        // assert only one region is present
        $regionCss = self::behatElementToCssSelector($region, 'region');
        $found = count($this->getSession()->getPage()->findAll('css', $regionCss));
        if ($found !== 1) {
            throw new \RuntimeException("Can't assert text existing in region $region, $found found");
        }

        $this->assertSession()->elementTextContains('css', $regionCss, $text);
    }

    /**
     * @Then each text should be present in the corresponding region:
     */
    public function eachTextShouldBePresentCorrespondingRegion(TableNode $fields)
    {
        foreach ($fields->getRowsHash() as $text => $region) {
            $this->iShouldSeeInTheRegion($text, $region);
        }
    }

    /**
     * @Then I should see :text in :section section
     */
    public function iShouldSeeInSection($text, $section)
    {
        $this->assertSession()->elementTextContains('css', '#' . $section . '-section', $text);
    }

    /**
     * @Then I should not see :text in the :section section
     */
    public function iShouldNotSeeInTheSection($text, $section)
    {
        $this->assertResponseStatus(200);

        $this->assertSession()->elementTextNotContains('css', '#' . $section . '-section', $text);
    }

    /**
     * @Then I should see :text in :container
     */
    public function iShouldSeeInTheContainer($text, $container)
    {
        $this->assertSession()->elementTextContains('css', '#' . $container . ', .' . $container, $text);
    }

    /**
     * @Then the :selector element should be empty
     */
    public function theElementShouldBeEmpty($selector)
    {
        $this->assertSession()->elementExists('css', '#' . $selector);
        if (!empty($this->getSession()->getPage()->find('css', '#' . $selector)->getText())) {
            throw new \RuntimeException('Element Not Empty');
        }
    }

    /**
     * @Then I should not see :text in the :region region
     */
    public function iShouldNotSeeInTheRegion($text, $region)
    {
        $this->assertResponseStatus(200);

        $this->assertSession()->elementTextNotContains('css', self::behatElementToCssSelector($region, 'region'), $text);
    }

    public static function behatElementToCssSelector($element, $type)
    {
        return '.behat-' . $type . '-' . preg_replace('/\s+/', '-', $element);
    }

    /**
     * @Then I should see the cookie warning banner
     */
    public function seeCookieBanner()
    {
        $driver = $this->getSession()->getDriver();

        if (get_class($driver) != 'Behat\Mink\Driver\GoutteDriver') {
            $elementsFound = $this->getSession()->getPage()->findAll('css', '#global-cookie-message');
            if (count($elementsFound) === 0) {
                throw new \RuntimeException('Cookie banner not found');
            }

            foreach ($elementsFound as $node) {
                // Note: getText() will return an empty string when using Selenium2D. This
                // is ok since it will cause a failed step.
                if ($node->getText() != '' && $node->isVisible()) {
                    return;
                }
            }
        }
    }


    /**
     * @Then I should see :text in the page header
     */
    public function iShouldSeeInThePageHeader($text)
    {
        $this->assertSession()->elementTextContains('css', '.page-header', $text);
    }


    /**
     * Click on element with attribute [behat-link=:link].
     *
     * @When I click on ":link"
     */
    public function clickOnBehatLink($link)
    {
        // if multiple links are specified (comma-separated), click on all of them
        if (strpos($link, ',') !== false) {
            foreach (explode(',', $link) as $singleLink) {
                $this->clickOnBehatLink(trim($singleLink));
            }

            return;
        }

        // find link inside the region
        $linkSelector = self::behatElementToCssSelector($link, 'link');
        $linksElementsFound = $this->getSession()->getPage()->findAll('css', $linkSelector);
        $count = count($linksElementsFound);

        if (count($linksElementsFound) > 1) {
            throw new \RuntimeException("Found more than one $linkSelector element in the page ($count). Interrupted");
        }
        if (count($linksElementsFound) === 0) {
            throw new \RuntimeException("$linkSelector not found in page");
        }

        // click on the found link
        $linksElementsFound[0]->click();
    }


}
