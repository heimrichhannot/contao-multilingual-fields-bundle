<?php

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener\DataContainer;

use Contao\DataContainer;
use HeimrichHannot\MultilingualFieldsBundle\EventListener\Contao\LoadDataContainerListener;
use HeimrichHannot\UtilsBundle\Util\Utils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

class LanguageEditSwitchButtonCallback
{
    private Utils $utils;
    private RequestStack $requestStack;
    private TranslatorInterface $translator;

    public function __construct(
        Utils $utils,
        RequestStack $requestStack,
        TranslatorInterface $translator
    )
    {
        $this->utils = $utils;
        $this->requestStack = $requestStack;
        $this->translator = $translator;
    }

    public function __invoke(DataContainer $dc, string $label): string
    {
        $GLOBALS['TL_JAVASCRIPT']['contao-multilingual-fields-bundle'] =
            'bundles/heimrichhannotmultilingualfields/assets/contao-multilingual-fields-bundle.js';
        $GLOBALS['TL_CSS']['contao-multilingual-fields-bundle'] =
            'bundles/heimrichhannotmultilingualfields/assets/contao-multilingual-fields-bundle.css';

        $isEditMode = $this->requestStack->getCurrentRequest()->query->get(LoadDataContainerListener::EDIT_LANGUAGES_PARAM, false);
        $class = '';

        if ($isEditMode) {
            $href = $this->utils->url()->removeQueryStringParameterFromUrl(LoadDataContainerListener::EDIT_LANGUAGES_PARAM);
            $class = 'close';
        } else {
            $href = $this->utils->url()->addQueryStringParameterToUrl(LoadDataContainerListener::EDIT_LANGUAGES_PARAM . '=1');
        }

        $text = $this->translator->trans(
            'MSC.multilingualFieldsBundle.' . ($isEditMode ? 'mf_closeEditLanguages' : 'mf_editLanguages'),
            [],
            'contao_default'
        );

        return sprintf(
            '<div class="w50 widget" id="mf_language_edit_switch_button_widget"><a href="%s" class="%s" id="mf_language_edit_switch_button">%s</a></div>',
            $href,
            $class,
            $text
        );
    }
}