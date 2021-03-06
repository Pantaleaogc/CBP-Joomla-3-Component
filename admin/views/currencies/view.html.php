<?php
/*----------------------------------------------------------------------------------|  www.giz.de  |----/
	Deutsche Gesellschaft für International Zusammenarbeit (GIZ) Gmb 
/-------------------------------------------------------------------------------------------------------/

	@version		@update number 20 of this MVC
	@build			17th May, 2018
	@created		8th July, 2015
	@package		Cost Benefit Projection
	@subpackage		view.html.php
	@author			Llewellyn van der Merwe <http://www.vdm.io>	
	@owner			Deutsche Gesellschaft für International Zusammenarbeit (GIZ) Gmb
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	
/-------------------------------------------------------------------------------------------------------/
	Cost Benefit Projection Tool.
/------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * Costbenefitprojection View class for the Currencies
 */
class CostbenefitprojectionViewCurrencies extends JViewLegacy
{
	/**
	 * Currencies view display method
	 * @return void
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() !== 'modal')
		{
			// Include helper submenu
			CostbenefitprojectionHelper::addSubmenu('currencies');
		}

		// Assign data to the view
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->user = JFactory::getUser();
		$this->listOrder = $this->escape($this->state->get('list.ordering'));
		$this->listDirn = $this->escape($this->state->get('list.direction'));
		$this->saveOrder = $this->listOrder == 'ordering';
		// get global action permissions
		$this->canDo = CostbenefitprojectionHelper::getActions('currency');
		$this->canEdit = $this->canDo->get('currency.edit');
		$this->canState = $this->canDo->get('currency.edit.state');
		$this->canCreate = $this->canDo->get('currency.create');
		$this->canDelete = $this->canDo->get('currency.delete');
		$this->canBatch = $this->canDo->get('core.batch');

		// We don't need toolbar in the modal window.
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
			// load the batch html
			if ($this->canCreate && $this->canEdit && $this->canState)
			{
				$this->batchDisplay = JHtmlBatch_::render();
			}
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Display the template
		parent::display($tpl);

		// Set the document
		$this->setDocument();
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('COM_COSTBENEFITPROJECTION_CURRENCIES'), 'credit');
		JHtmlSidebar::setAction('index.php?option=com_costbenefitprojection&view=currencies');
		JFormHelper::addFieldPath(JPATH_COMPONENT . '/models/fields');

		if ($this->canCreate)
		{
			JToolBarHelper::addNew('currency.add');
		}

		// Only load if there are items
		if (CostbenefitprojectionHelper::checkArray($this->items))
		{
			if ($this->canEdit)
			{
				JToolBarHelper::editList('currency.edit');
			}

			if ($this->canState)
			{
				JToolBarHelper::publishList('currencies.publish');
				JToolBarHelper::unpublishList('currencies.unpublish');
				JToolBarHelper::archiveList('currencies.archive');

				if ($this->canDo->get('core.admin'))
				{
					JToolBarHelper::checkin('currencies.checkin');
				}
			}

			// Add a batch button
			if ($this->canBatch && $this->canCreate && $this->canEdit && $this->canState)
			{
				// Get the toolbar object instance
				$bar = JToolBar::getInstance('toolbar');
				// set the batch button name
				$title = JText::_('JTOOLBAR_BATCH');
				// Instantiate a new JLayoutFile instance and render the batch button
				$layout = new JLayoutFile('joomla.toolbar.batch');
				// add the button to the page
				$dhtml = $layout->render(array('title' => $title));
				$bar->appendButton('Custom', $dhtml, 'batch');
			} 

			if ($this->state->get('filter.published') == -2 && ($this->canState && $this->canDelete))
			{
				JToolbarHelper::deleteList('', 'currencies.delete', 'JTOOLBAR_EMPTY_TRASH');
			}
			elseif ($this->canState && $this->canDelete)
			{
				JToolbarHelper::trash('currencies.trash');
			}

			if ($this->canDo->get('core.export') && $this->canDo->get('currency.export'))
			{
				JToolBarHelper::custom('currencies.exportData', 'download', '', 'COM_COSTBENEFITPROJECTION_EXPORT_DATA', true);
			}
		} 

		if ($this->canDo->get('core.import') && $this->canDo->get('currency.import'))
		{
			JToolBarHelper::custom('currencies.importData', 'upload', '', 'COM_COSTBENEFITPROJECTION_IMPORT_DATA', false);
		}

		// set help url for this view if found
		$help_url = CostbenefitprojectionHelper::getHelpUrl('currencies');
		if (CostbenefitprojectionHelper::checkString($help_url))
		{
				JToolbarHelper::help('COM_COSTBENEFITPROJECTION_HELP_MANAGER', false, $help_url);
		}

		// add the options comp button
		if ($this->canDo->get('core.admin') || $this->canDo->get('core.options'))
		{
			JToolBarHelper::preferences('com_costbenefitprojection');
		}

		if ($this->canState)
		{
			JHtmlSidebar::addFilter(
				JText::_('JOPTION_SELECT_PUBLISHED'),
				'filter_published',
				JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
			);
			// only load if batch allowed
			if ($this->canBatch)
			{
				JHtmlBatch_::addListSelection(
					JText::_('COM_COSTBENEFITPROJECTION_KEEP_ORIGINAL_STATE'),
					'batch[published]',
					JHtml::_('select.options', JHtml::_('jgrid.publishedOptions', array('all' => false)), 'value', 'text', '', true)
				);
			}
		}

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_ACCESS'),
			'filter_access',
			JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'))
		);

		if ($this->canBatch && $this->canCreate && $this->canEdit)
		{
			JHtmlBatch_::addListSelection(
				JText::_('COM_COSTBENEFITPROJECTION_KEEP_ORIGINAL_ACCESS'),
				'batch[access]',
				JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text')
			);
		} 
	}

	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument()
	{
		if (!isset($this->document))
		{
			$this->document = JFactory::getDocument();
		}
		$this->document->setTitle(JText::_('COM_COSTBENEFITPROJECTION_CURRENCIES'));
		$this->document->addStyleSheet(JURI::root() . "administrator/components/com_costbenefitprojection/assets/css/currencies.css", (CostbenefitprojectionHelper::jVersion()->isCompatible('3.8.0')) ? array('version' => 'auto') : 'text/css');
	}

	/**
	 * Escapes a value for output in a view script.
	 *
	 * @param   mixed  $var  The output to escape.
	 *
	 * @return  mixed  The escaped value.
	 */
	public function escape($var)
	{
		if(strlen($var) > 50)
		{
			// use the helper htmlEscape method instead and shorten the string
			return CostbenefitprojectionHelper::htmlEscape($var, $this->_charset, true);
		}
		// use the helper htmlEscape method instead.
		return CostbenefitprojectionHelper::htmlEscape($var, $this->_charset);
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 */
	protected function getSortFields()
	{
		return array(
			'a.sorting' => JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => JText::_('JSTATUS'),
			'a.name' => JText::_('COM_COSTBENEFITPROJECTION_CURRENCY_NAME_LABEL'),
			'a.codethree' => JText::_('COM_COSTBENEFITPROJECTION_CURRENCY_CODETHREE_LABEL'),
			'a.numericcode' => JText::_('COM_COSTBENEFITPROJECTION_CURRENCY_NUMERICCODE_LABEL'),
			'a.decimalplace' => JText::_('COM_COSTBENEFITPROJECTION_CURRENCY_DECIMALPLACE_LABEL'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
