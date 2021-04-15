<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle;

use HeimrichHannot\MultilingualFieldsBundle\DependencyInjection\HeimrichHannotMultilingualFieldsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class HeimrichHannotMultilingualFieldsBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new HeimrichHannotMultilingualFieldsExtension();
    }
}
