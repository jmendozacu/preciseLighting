<?php
/**
 * {{Drc}}_{{Storepickup}} extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 *                     @category  {{Drc}}
 *                     @package   {{Drc}}_{{Storepickup}}
 *                     @copyright Copyright (c) {{2016}}
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Drc\Storepickup\Model\ResourceModel;

class Storelocator extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
    
        $this->date = $date;
        parent::__construct($context);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('drc_storepickup_storelocator', 'storelocator_id');
    }

    /**
     * Retrieves Storelocator Store Title from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    /*public function getStorelocatorStore_titleById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'store_title')
            ->where('storelocator_id = :storelocator_id');
        $binds = ['storelocator_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }*/
    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Drc\Storepickup\Model\Storelocator $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }
        return parent::_beforeSave($object);
    }
}
