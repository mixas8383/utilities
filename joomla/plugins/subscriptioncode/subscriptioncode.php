<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.subscriptioncode
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;


class PlgSystemsubscriptioncode extends JPlugin
{
    public function onExtensionAfterSave($context, $table)
    {

        $componentParams = new \Joomla\Registry\Registry($table->params);
        $fieldName = $this->params->get('field_name','subscription_code');
        
        $newVersion = $componentParams->get($fieldName, '');

        if (!empty($newVersion))
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('*');
            $query->from('#__update_sites AS s');
            $query->join('INNER', $db->quoteName('#__update_sites_extensions') . ' AS c ON c.update_site_id = s.update_site_id');
            $query->where('c.extension_id = ' . (int) $table->extension_id);
            $db->setQuery($query);
            $res = $db->loadObjectList();
            
            if (!empty($res))
            {
                foreach ($res as $one)
                {
                    $query = $db->getQuery(true);
                    $fields = array(
                        $db->quoteName('extra_query') . ' = ' . $db->quote($newVersion)
                    );
                    $query->update($db->quoteName('#__update_sites'))->set($fields)->where('update_site_id = ' . (int) $one->update_site_id);
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
        if($newVersion == -1)
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);

            $query->select('*');
            $query->from('#__update_sites AS s');
            $query->join('INNER', $db->quoteName('#__update_sites_extensions') . ' AS c ON c.update_site_id = s.update_site_id');
            $query->where('c.extension_id = ' . (int) $table->extension_id);
            $db->setQuery($query);
            $res = $db->loadObjectList();
            
            if (!empty($res))
            {
                foreach ($res as $one)
                {
                    $query = $db->getQuery(true);
                    $fields = array(
                        $db->quoteName('extra_query') . ' = ""'  
                    );
                    $query->update($db->quoteName('#__update_sites'))->set($fields)->where('update_site_id = ' . (int) $one->update_site_id);
                    $db->setQuery($query);
                    $db->execute();
                }
            }
        }
    }
}
