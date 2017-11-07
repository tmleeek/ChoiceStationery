<?php

class Brandammo_Pronav_Block_Catalog_Widget_Abstract
	extends Mage_Catalog_Block_Widget_Link
{
	// Href set to true to allow widget to be shown when URL cannot be prepared
	public function getHref()
	{
		return TRUE;
	}
}