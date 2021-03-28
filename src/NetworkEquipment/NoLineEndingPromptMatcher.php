<?php

/**
 * This file is part of marbocub/network-equipment.
 *
 * Copyright (c) 2021 marbocub. <marbocub@gmail.com>
 *
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed 
 * with this source code.
 *
 * @license https://github.com/marbocub/network-equipment/blob/master/LICENSE
 * @link https://github.com/marbocub/network-equipment
 */

namespace Marbocub\NetworkEquipment;

use Graze\TelnetClient\PromptMatcher;

class NoLineEndingPromptMatcher extends PromptMatcher
{
    public function isMatch($prompt, $subject, $lineEnding = null)
    {
        return parent::isMatch($prompt, $subject . $lineEnding, $lineEnding);
    }
}