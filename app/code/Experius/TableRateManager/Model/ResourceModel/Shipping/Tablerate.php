<?php 
/**
 * A Magento 2 module named Experius/TableRateManager
 * Copyright (C) 2016 Derrick Heesbeen
 * 
 * This file included in Experius/TableRateManager is licensed under OSL 3.0
 * 
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Experius\TableRateManager\Model\ResourceModel\Shipping;
 
 
class Tablerate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {


	
	protected function _construct(){
		$this->_init('shipping_tablerate', 'pk');
	}
}
