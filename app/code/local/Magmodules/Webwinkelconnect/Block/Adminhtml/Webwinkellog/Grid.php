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

class Magmodules_Webwinkelconnect_Block_Adminhtml_Webwinkellog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    /**
     * Magmodules_Webwinkelconnect_Block_Adminhtml_Webwinkellog_Grid constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('webwinkellogGrid');
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('webwinkelconnect/log')->getCollection();
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
            'type', array(
            'header' => Mage::helper('webwinkelconnect')->__('Type'),
            'align' => 'left',
            'index' => 'type',
            'width' => '120',
            'type' => 'options',
            'options' => array(
                'reviews' => Mage::helper('webwinkelconnect')->__('Reviews'),
                'invitation' => Mage::helper('webwinkelconnect')->__('Invitation Call'),
                'review_delete' => Mage::helper('webwinkelconnect')->__('Review Delete'),
                'review_edit' => Mage::helper('webwinkelconnect')->__('Review Edit'),
            ),
            )
        );

        if (Mage::app()->getRequest()->getParam('showapiurl')) {
            $this->addColumn(
                'api_url', array(
                'header' => Mage::helper('webwinkelconnect')->__('Api URL'),
                'align' => 'left',
                'index' => 'api_url',
                'filter' => false,
                'sortable' => false,
                )
            );
        }

        $this->addColumn(
            'qty', array(
            'header' => Mage::helper('webwinkelconnect')->__('Description'),
            'align' => 'left',
            'index' => 'qty',
            'renderer' => 'webwinkelconnect/adminhtml_widget_grid_log',
            'filter' => false,
            'sortable' => false,
            )
        );

        $this->addColumn(
            'cron', array(
            'header' => Mage::helper('webwinkelconnect')->__('Cron'),
            'align' => 'left',
            'index' => 'cron',
            'width' => '120',
            'type' => 'options',
            'options' => array(
                '' => Mage::helper('webwinkelconnect')->__('Manual'),
                'stats' => Mage::helper('webwinkelconnect')->__('Stats Cron'),
                'reviews' => Mage::helper('webwinkelconnect')->__('Reviews Cron'),
                'orderupdate' => Mage::helper('webwinkelconnect')->__('Invitation'),
            ),
            )
        );

        $this->addColumn(
            'time', array(
            'header' => Mage::helper('webwinkelconnect')->__('Time'),
            'align' => 'left',
            'index' => 'time',
            'width' => '60',
            'renderer' => 'webwinkelconnect/adminhtml_widget_grid_seconds',
            )
        );

        $this->addColumn(
            'date', array(
            'header' => Mage::helper('webwinkelconnect')->__('Date'),
            'align' => 'left',
            'type' => 'datetime',
            'index' => 'date',
            'width' => '140',
            )
        );

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('logids');
        $this->getMassactionBlock()->addItem(
            'hide', array(
            'label' => Mage::helper('webwinkelconnect')->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
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