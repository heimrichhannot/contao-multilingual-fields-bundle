<?php

/*
 * Copyright (c) 2021 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\MultilingualFieldsBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;

class LoadDataContainerListener
{
    private static $run = false;

    /**
     * @Hook("loadDataContainer", priority=-256)
     */
    public function __invoke($table)
    {
        // only run once
        if (static::$run) {
            return;
        }

        static::$run = true;
    }
}
