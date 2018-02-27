<?php
namespace Codazon\AjaxLayeredNav\Plugin;
class NavigationStatePlugin
{
	public function afterGetActiveFilters($subject, $result)
    {
        $merged = $result;
		$final  = array();

		foreach ($merged as $current) {
		    if ( ! in_array($current, $final)) {
		        $final[] = $current;
		    }
		}
        return $final;
    }
}