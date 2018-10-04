<?php
/**
 * @package DJ-Catalog2
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 */
defined('_JEXEC') or die( 'Restricted access' );
jimport('joomla.application.component.controlleradmin');


class Djcatalog2ControllerPrices extends JControllerAdmin
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }
    
    public function &getModel($name = 'Item', $prefix = 'Djcatalog2Model')
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }
}