<?php
/**
* @version $Id: proforma.php 446 2015-06-02 09:42:20Z michal $
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
$params = JComponentHelper::getParams('com_djcatalog2');

$logo = trim($params->get('cart_company_logo', ''));
/*$logoBase = false;
if ($logo != '' && JFile::exists(JPATH_ROOT.'/'.$logo)) {
	$imgType = pathinfo(JPATH_ROOT.'/'.$logo, PATHINFO_EXTENSION);
	$imgData = file_get_contents(JPATH_ROOT.'/'.$logo);
	$dataUri = 'data:image/' . $imgType . ';base64,' . base64_encode($imgData);
	$logoBase = '<img alt="logo" src="'.$dataUri.'" />';
}*/

?>

<br />

<table cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="left" width="40%"><?php 
	        /*if ($logoBase) {
	         echo $logoBase;
	         }*/
	        if ($logo) {
	        	echo '<img alt="logo" src="'.JUri::root(false).$logo.'" />';
	        }
        ?><h2><?php echo JText::_('COM_DJCATALOG2_PROFORMA_INVOICE') ?></h2></td>
        <td align="right">
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td width="180px">
                        <?php echo JText::_('COM_DJCATALOG2_ORDER_CREATED_DATE')?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('date', $data->created_date, 'd-m-Y'); ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_('COM_DJCATALOG2_ORDER_NUMBER')?>:
                    </td>
                    <td>
                        <?php echo $data->order_number; ?>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo JText::_('COM_DJCATALOG2_ORDER_PAYMENT_METHOD')?>:
                    </td>
                    <td>
                        <?php echo $data->payment_method; ?>
                    </td>
                </tr>
                <?php if ($data->payment_date && $data->payment_date != '0000-00-00 00:00:00') {?>
                <tr>
                    <td>
                        <?php echo JText::_('COM_DJCATALOG2_ORDER_PAYMENT_DATE')?>:
                    </td>
                    <td>
                        <?php echo JHTML::_('date', $data->payment_date, 'd-m-Y'); ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </td>
    </tr>
</table>
<br />
<br />
<table cellpadding="10" cellspacing="0" width="100%">
    <tr>
        <td width="50%" align="left" style="border-bottom: 1px solid #666666;"><h2><?php echo JText::_('COM_DJCATALOG2_INVOICE_SELLER') ?></h2>
             <?php echo $params->get('cart_invoice_header');?>
        </td>
        <td align="left"  style="border-bottom: 1px solid #666666;"><h2><?php echo JText::_('COM_DJCATALOG2_INVOICE_BUYER') ?></h2>
            <p><?php 
                if (!empty($data->companyname)) {
                    echo '<strong>'.$data->company.'</strong>';
                } else {
                    echo '<strong>'.$data->firstname.' '.$data->lastname.'</strong>';
                }
                ?>
                <br />
                <?php echo $data->postcode.', '.$data->city; ?>
                <br />
                <?php echo $data->address; ?>
                <br />
                <?php echo $data->country; ?>
                <?php 
                if (!empty($data->vat_id)) {
                    echo '<br />'.JText::_('COM_DJCATALOG2_UP_VATID').': '.$data->vat_id;
                }
                ?>
                </p>
        </td>
    </tr>
</table>
<br />
<br />
<br />
<table cellpadding="0" cellspacing="0">
<tr>
    <th width="5%" align="center" valign="top">
        <strong>#</strong>
    </th>
    <th width="30%" align="left" valign="top">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_SERVICE'); ?></strong>
    </th>
    <th width="7%" align="center" valign="top">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_QTY'); ?></strong>
    </th>
    <th width="7%" align="center" valign="top">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_UNIT'); ?></strong>
    </th>
    <th width="10%" align="center" valign="top">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_BASE_PRICE'); ?></strong>
    </th>
    <th width="10%" align="center" valign="top">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_TAX_RATE'); ?></strong>
    </th>
    <th width="10%" align="center" valign="top">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_BASE_COST'); ?></strong>
    </th>
    <th width="10%" align="center" valign="top">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_TAX'); ?></strong>
    </th>
    <th width="10%" align="center" valign="top">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_TOTAL'); ?></strong>
    </th>
</tr>
<tr>
    <td colspan="9" style="border-bottom: 1px solid #666666;"></td>
</tr>
<tr>
    <td colspan="9">&nbsp;</td>
</tr>
<?php
$no = 1;

if ($data->delivery_price > 0) {
	$delivery = new stdClass();
	$delivery->item_name = JText::_('COM_DJCATALOG2_INVOICE_DELIVERY').' - '.$data->delivery_method;
	$delivery->quantity = 1;
	$delivery->base_cost = $data->delivery_price;
	$delivery->cost = $data->delivery_price;
	$delivery->tax_rate = $data->delivery_tax_rate;
	$delivery->tax = $data->delivery_tax;
	$delivery->total = $data->delivery_total;
	
	$data->items[] = $delivery;
}

if ($data->payment_price > 0) {
	$payment = new stdClass();
	$payment->item_name = JText::_('COM_DJCATALOG2_INVOICE_PAYMENT').' - '.$data->payment_method;
	$payment->quantity = 1;
	$payment->base_cost = $data->payment_price;
	$payment->cost = $data->payment_price;
	$payment->tax_rate = $data->payment_tax_rate;
	$payment->tax = $data->payment_tax;
	$payment->total = $data->payment_total;

	$data->items[] = $payment;
}

foreach ($data->items as $item) {
    ?>
    <tr>
        <td align="center" style="padding: 10px 0;">
            <?php echo $no; ?>
        </td>
        <td align="left" style="padding: 10px 0;"><?php echo $item->item_name ?></td>
        <td align="center" style="padding: 10px 0;">
            <?php echo $item->quantity ?>
        </td>
        <td align="center" style="padding: 10px 0;">
            <?php echo JText::_('COM_DJCATALOG2_UNIT_PC'); ?>
        </td>
        <td align="center" style="padding: 10px 0;">
            <?php echo number_format($item->base_cost, 2, '.', ''); ?>
        </td>
        <td align="center" style="padding: 10px 0;">
            <?php 
            if ($item->tax == 0.0 && $item->tax_rate == 0.0) {
                echo '-';
            } else {
                echo number_format((100*$item->tax_rate), 2, '.', '').'%';
            }
            ?>
        </td>
        <td align="center" style="padding: 10px 0;">
            <?php echo number_format($item->cost, 2, '.', ''); ?>
        </td>
        <td align="center" style="padding: 10px 0;"> 
            <?php echo number_format($item->tax, 2, '.', ''); ?>
        </td>
        <td align="center"style="padding: 10px 0;" >
            <?php echo number_format($item->total, 2, '.', ''); ?>
        </td>
    </tr>
    <?php
    $no++;
}
?>
<tr>
    <td colspan="9">&nbsp;</td>
</tr>
<tr>
    <td colspan="6" align="right">
        <strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_TOTAL') ?>:</strong>
    </td>
    <td align="center">
        <strong><?php echo number_format($data->total, 2, '.', ''); ?></strong>
    </td>
    <td align="center">
        <strong><?php echo number_format($data->tax, 2, '.', ''); ?></strong>
    </td>
    <td align="center" style="border: 1px solid #666666">
        <strong><?php echo number_format($data->grand_total, 2, '.', ''); ?></strong>
    </td>
</tr>
<tr>
    <td colspan="6">&nbsp;</td>
    <td align="right" colspan="2">
        <strong><?php echo JText::_('COM_DJCATALOG2_ORDER_CURRENCY')?>&nbsp;&nbsp;</strong>
    </td>
    <td align="center" style="border: 1px solid #666666">
        <strong><?php echo strtoupper($data->currency); ?></strong>
    </td>
</tr>
<tr>
    <td colspan="9">&nbsp;</td>
</tr>
<?php ?>
<tr>
    <td colspan="4">&nbsp;</td>
    <td colspan="5"><?php 
            $tax_totals = array();
            foreach($data->items as $item) {
                $tax_rate = (string)(number_format((100*$item->tax_rate), 2, '.', '').'%');
                
                if ($item->tax == 0.0 && $item->tax_rate == 0.0) {
                    $tax_rate = '-';
                }
                
                if (!isset($tax_totals[$tax_rate])) {
                    $tax_totals[$tax_rate] = array('total'=>0.0, 'tax'=>0.0, 'grand_total'=>0.0);
                }
                $tax_totals[$tax_rate]['total'] += $item->cost;
                $tax_totals[$tax_rate]['tax'] += $item->tax;
                $tax_totals[$tax_rate]['grand_total'] += $item->total;
            }
            ?>
            <table cellpadding="2" cellspacing="0" width="100%" style="border: 1px solid #666666;">
                <tr>
                    <td><strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_TAX_RATE'); ?></strong></td>
                    <td align="center"><strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_BASE_COST'); ?></strong></td>
                    <td align="center"><strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_TAX'); ?></strong></td>
                    <td align="center"><strong><?php echo JText::_('COM_DJCATALOG2_INVOICE_TOTAL'); ?></strong></td>
                    <td><strong><?php echo JText::_('COM_DJCATALOG2_ORDER_CURRENCY')?></strong></td>
                </tr>
                <?php 
                foreach($tax_totals as $tax_rate=>$values) {
                    ?>
                    <tr>
                        <td valign="middle"><?php echo $tax_rate ?></td>
                        <td align="center"><?php echo number_format($values['total'], 2, '.', ''); ?></td>
                        <td align="center"><?php echo number_format($values['tax'], 2, '.', ''); ?></td>
                        <td align="center"><?php echo number_format($values['grand_total'], 2, '.', ''); ?></td>
                        <td align="center"><?php echo $data->currency ?></td>
                    </tr>
                <?php
                }
                ?>
            </table>
    </td>
</tr>
</table>
<br /><br />
