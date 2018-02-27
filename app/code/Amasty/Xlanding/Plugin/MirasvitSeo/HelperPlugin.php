<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */

namespace Amasty\Xlanding\Plugin\MirasvitSeo;

class HelperPlugin
{
    /**
     * Prevent Meta Data replacement.
     *
     * @param \Mirasvit\Seo\Helper\Data $data
     * @param $isIgnored
     * @return bool
     */
    public function afterIsIgnoredActions(\Mirasvit\Seo\Helper\Data $data, $isIgnored)
    {
        if (!$isIgnored) {
            if ($data->getFullActionCode() == 'amasty_xlanding_page_view') {
                $isIgnored = true;
            }
        }
        return $isIgnored;
    }
}