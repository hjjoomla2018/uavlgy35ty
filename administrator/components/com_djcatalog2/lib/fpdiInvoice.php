<?php
/**
* @version $Id: fpdiInvoice.php 443 2015-06-01 08:23:24Z michal $
* @package DJ-Catalog2
* @copyright Copyright (C) 2010 Blue Constant Media LTD, All rights reserved.
* @license http://www.gnu.org/licenses GNU/GPL
* @author url: http://design-joomla.eu
* @author email contact@design-joomla.eu
* @developer $Author: michal $ Michal Olczyk - michal.olczyk@design-joomla.eu
*
*
*** the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* DJ-Catalog2 is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with DJ-Catalog2. If not, see <http://www.gnu.org/licenses/>.
*
*/


// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPath::clean(JPATH_ROOT.'/libraries/tcpdf/tcpdf.php');
require_once JPath::clean(JPATH_ROOT.'/libraries/fpdi/fpdi.php');

class DJCatalog2InvoiceFPDI extends FPDI
{
	/**
	 * "Remembers" the template id of the imported page
	 */
	var $_tplIdx;
	
	var $_intCurPage = 1;
	var $_intFootNo = '';

	/**
	 * Draw an imported PDF logo on every page
	 */
	function Header()
	{
	}

	function Footer()
	{
		$invoice_no = (empty($this->_intFootNo)) ? '' : JText::_('COM_DJCATALOG2_ORDER_INVOICE_NUMBER').': '.$this->_intFootNo;
		$html = '<table cellpadding="0" cellspacing="0" width="100%"><tr><td width="50%" align="left" style="border-top:1px solid #666666;">'.$invoice_no.'</td><td align="right" style="border-top:1px solid #666666;">'.JText::_('COM_DJCATALOG2_PAGE_NO').': '.$this->_intCurPage++.'</td></tr></table>';
		$this->writeHTML($html, true, false, true, false, '');
	}
}