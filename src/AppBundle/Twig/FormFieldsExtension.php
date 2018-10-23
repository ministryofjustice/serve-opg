<?php
/**
 * Project: opg-digicop
 * Author: robertford
 * Date: 23/10/2018
 */

namespace AppBundle\Twig;

use Symfony\Component\Form\FormView;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

class FormFieldsExtension extends \Twig_Extension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('getHintList', array($this, 'getHintListFilter')),
        );
    }

    public function getHintListFilter($translationKey, $domain =null)
    {
        $hintListTextTrans = $this->translator->trans($translationKey, [], $domain);
        $hintListArray = array_filter(explode("\n", $hintListTextTrans));
        return $hintListArray;
    }

    public function getName()
    {
        return 'formfields_extension';
    }

}
