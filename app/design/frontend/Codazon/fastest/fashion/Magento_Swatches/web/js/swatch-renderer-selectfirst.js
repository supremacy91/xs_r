/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery'
], function ($, _) {
    'use strict';
	$.widget('mage.AfterSwatchRenderer', {
		_init: function () {
			setTimeout(
				function () {
					$('.swatch-option').each(function() {
						$(this).first().trigger('click');
					});
				}, 3000
			);
		}
	});
	return $.mage.AfterSwatchRenderer;
});