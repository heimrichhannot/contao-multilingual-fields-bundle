<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle;

use HeimrichHannot\MultilingualFieldsBundle\DependencyInjection\HeimrichHannotMultilingualFieldsExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotMultilingualFieldsBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        return new HeimrichHannotMultilingualFieldsExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
