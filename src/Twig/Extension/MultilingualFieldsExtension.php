<?php

namespace HeimrichHannot\MultilingualFieldsBundle\Twig\Extension;

use HeimrichHannot\MultilingualFieldsBundle\Twig\Runtime\MultilingualFieldsRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MultilingualFieldsExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('mf_value', [MultilingualFieldsRuntime::class, 'mfFieldValue']),
        ];
    }
}
