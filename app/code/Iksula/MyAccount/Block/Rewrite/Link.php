<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * "My Wish List" link
 */
namespace Iksula\MyAccount\Block\Rewrite;

/**
 * Class Link
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Link extends \Magento\Wishlist\Block\Link
{    
    public function getLabel()
    {
        return __('My Wishlist');
    }
}
