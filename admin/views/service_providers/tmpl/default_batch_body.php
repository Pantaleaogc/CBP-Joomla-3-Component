<?php
/*----------------------------------------------------------------------------------|  www.giz.de  |----/
	Deutsche Gesellschaft für International Zusammenarbeit (GIZ) Gmb 
/-------------------------------------------------------------------------------------------------------/

	@version		@update number 35 of this MVC
	@build			22nd March, 2016
	@created		25th July, 2015
	@package		Cost Benefit Projection
	@subpackage		default_batch_body.php
	@author			Llewellyn van der Merwe <http://www.vdm.io>	
	@owner			Deutsche Gesellschaft für International Zusammenarbeit (GIZ) Gmb
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	
/-------------------------------------------------------------------------------------------------------/
	Cost Benefit Projection Tool.
/------------------------------------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die('Restricted access'); 

?>

<p><?php echo JText::_('COM_COSTBENEFITPROJECTION_SERVICE_PROVIDERS_BATCH_TIP'); ?></p>
<?php echo $this->batchDisplay; ?>