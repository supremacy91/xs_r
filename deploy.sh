#!/bin/bash
php bin/magento maintenance:enable && php bin/magento setup:upgrade && rm -fR var/view_preprocessed pub/static/* var/cache var/page_cache var/generation var/di && php bin/magento cache:flush && php bin/magento cache:clean && php bin/magento setup:di:compile && php bin/magento setup:static-content:deploy && php bin/magento cache:flush && php bin/magento maintenance:disable
