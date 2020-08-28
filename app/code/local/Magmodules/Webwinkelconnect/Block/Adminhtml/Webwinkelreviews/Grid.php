<?php
/**
 * Magmodules.eu - http://www.magmodules.eu
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@magmodules.eu so we can send you a copy immediately.
 *
 * @category      Magmodules
 * @package       Magmodules_WebwinkelKeur
 * @author        Magmodules <info@magmodules.eu>
 * @copyright     Copyright (c) 2017 (http://www.magmodules.eu)
 * @license       http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */

class Magmodules_Webwinkelconnect_Block_Adminhtml_Webwinkelreviews_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Magmodules_Webwinkelconnect_Block_Adminhtml_Webwinkelreviews_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('reviewsGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('webwinkelconnect/reviews')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
    {

        $this->addColumn(
            'company', array(
            'header' => Mage::helper('webwinkelconnect')->__('Shop'),
            'index' => 'company',
            'width' => '120px',
            )
        );

        $this->addColumn(
            'name', array(
            'header' => Mage::helper('webwinkelconnect')->__('Name'),
            'align' => 'left',
            'index' => 'name',
            )
        );

        $this->addColumn(
            'experience', array(
            'header' => Mage::helper('webwinkelconnect')->__('Experience'),
            'align' => 'left',
            'index' => 'experience',
            'renderer' => 'webwinkelconnect/adminhtml_webwinkelreviews_renderer_experience',
            )
        );

        $this->addColumn(
            'rating', array(
            'header' => Mage::helper('webwinkelconnect')->__('Rating'),
            'align' => 'left',
            'index' => 'rating',
            'renderer' => 'webwinkelconnect/adminhtml_widget_grid_stars',
            'width' => '90',
            'filter' => false,
            'sortable' => true,
            )
        );

        $this->addColumn(
            'delivery_time', array(
            'header' => Mage::helper('webwinkelconnect')->__('Delivery Time'),
            'align' => 'left',
            'index' => 'delivery_time',
            'renderer' => 'webwinkelconnect/adminhtml_widget_grid_stars',
            'width' => '90',
            'filter' => false,
            'sortable' => true,
            )
        );

        $this->addColumn(
            'userfriendlyness', array(
            'header' => Mage::helper('webwinkelconnect')->__('Userfriendlyness'),
            'align' => 'left',
            'index' => 'userfriendlyness',
            'renderer' => 'webwinkelconnect/adminhtml_widget_grid_stars',
            'width' => '90',
            'filter' => false,
            'sortable' => true,
            )
        );

        $this->addColumn(
            'price_quality', array(
            'header' => Mage::helper('webwinkelconnect')->__('Price / Quality'),
            'align' => 'left',
            'index' => 'price_quality',
            'renderer' => 'webwinkelconnect/adminhtml_widget_grid_stars',
            'width' => '90',
            'filter' => false,
            'sortable' => true,
            )
        );

        $this->addColumn(
            'aftersales', array(
            'header' => Mage::helper('webwinkelconnect')->__('Aftersales'),
            'align' => 'left',
            'index' => 'aftersales',
            'renderer' => 'webwinkelconnect/adminhtml_widget_grid_stars',
            'width' => '90',
            'filter' => false,
            'sortable' => true,
            )
        );

        $this->addColumn(
            'date', array(
            'header' => Mage::helper('webwinkelconnect')->__('Date'),
            'align' => 'left',
            'type' => 'date',
            'index' => 'date',
            'width' => '140',
            )
        );

        $this->addColumn(
            'sidebar', array(
            'header' => Mage::helper('webwinkelconnect')->__('Sidebar'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'sidebar',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('webwinkelconnect')->__('No'),
                1 => Mage::helper('webwinkelconnect')->__('Yes'),
            ),
            )
        );

        $this->addColumn(
            'status', array(
            'header' => Mage::helper('webwinkelconnect')->__('Active'),
            'align' => 'left',
            'width' => '80px',
            'index' => 'status',
            'type' => 'options',
            'options' => array(
                0 => Mage::helper('webwinkelconnect')->__('No'),
                1 => Mage::helper('webwinkelconnect')->__('Yes'),
            ),
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('review_id');
        $this->getMassactionBlock()->setFormFieldName('reviewids');

        $this->getMassactionBlock()->addItem(
            'hide', array(
            'label' => Mage::helper('webwinkelconnect')->__('Set to invisible'),
            'url' => $this->getUrl('*/*/massDisable'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'visible', array(
            'label' => Mage::helper('webwinkelconnect')->__('Set to visible'),
            'url' => $this->getUrl('*/*/massEnable'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'addsidebar', array(
            'label' => Mage::helper('webwinkelconnect')->__('Add to Sidebar'),
            'url' => $this->getUrl('*/*/massEnableSidebar'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'removesidebar', array(
            'label' => Mage::helper('webwinkelconnect')->__('Remove from Sidebar'),
            'url' => $this->getUrl('*/*/massDisableSidebar'),
            )
        );

        return $this;
    }

    /**
     * @param $row
     * @return bool
     */
    public function getRowUrl($row)
    {
        return false;
    }
}