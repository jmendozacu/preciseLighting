<?php
/**
​ * ​ ​ Webinse
​ *
​ * ​ ​ PHP​ ​ Version​ ​ 7.0.22
​ *
​ * ​ ​ @category     Webinse
​ * ​ ​ @package    ​ ​ Webinse_RegionManager
​ * ​ ​ @author       Webinse​ ​ Team​ ​ <info@webinse.com>
​ * ​ ​ @copyright  ​ ​ 2018 ​ Webinse​ ​ Ltd.​ ​ (https://www.webinse.com)
​ * ​ ​ @license    ​ ​ http://opensource.org/licenses/OSL-3.0​ ​ The​ ​ Open​ ​ Software​ ​ License​ ​ 3.0
​ */
/**
​ * ​ ​ Comment​ ​ for​ ​ file
​ *
​ * ​ ​ @category     Webinse
​ * ​ ​ @package    ​ ​ Webinse_RegionManager
​ * ​ ​ @author       Webinse​ ​ Team​ ​ <info@webinse.com>
​ * ​ ​ @copyright  ​ ​ 2018 ​ Webinse​ ​ Ltd.​ ​ (https://www.webinse.com)
​ * ​ ​ @license    ​ ​ http://opensource.org/licenses/OSL-3.0​ ​ The​ ​ Open​ ​ Software​ ​ License​ ​ 3.0
​ */
namespace Webinse\RegionManager\Model\ResourceModel\Zip;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Webinse\RegionManager\Api\Data\ZipInterface;

class Collection extends AbstractCollection
{
    protected $_idFieldName = ZipInterface::ID;

    protected $_eventPrefix = 'webinse_regionmanager_zip_collection';

    protected $_eventObject = 'cities_collection';

    protected function _construct()
    {
        $this->_init('Webinse\RegionManager\Model\Zip', 'Webinse\RegionManager\Model\ResourceModel\Zip');
    }

    /**
     * Overridden to use other label field by default.
     *
     * @param null $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = null, $labelField = 'title', $additional = [])
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }
}