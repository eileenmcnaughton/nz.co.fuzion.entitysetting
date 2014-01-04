nz.co.fuzion.entitysetting
==========================

Helper extension to manage settings for multiple entities

This extension does not do anything on it's own. Rather it supports other extensions by storing settings related
to entities without the requirement to create a table per extension.

These settings can be accessed using api calls per the examples here
https://github.com/eileenmcnaughton/nz.co.fuzion.entitysetting/tree/master/api/v3/examples

Optionally it will also add settings to backend configuration pages. Note that it does this would over-riding the tpl
using a rather convoluted process. Each configurable page has a configured extra.hlp & extra.tpl file. Settings
to be added to the page are added to this. The alter content hook is used to move the setting to the correct place.
This involved loading the DOM object which does on some forms introduce a noticeable delay. This is mitigated by:
1) it is only on backend forms
2) it only occurs on forms with configured settings
3) it is anticipated that in the near-ish future civicrm will improve its form layer & the over-haul will include improved hooks
- so this extension should be able to be updated to take advantage of an improved hook without change to the modules that depend on it
4) this doesn't really belong as an extension anyway so it should be obsolete in 4.5 or 4.6 - but hopefully the syntax  will be
consistent for extension writers

However, it should be noted that any invalid html will result in error messages (which will be displayed or not
depending on your site's settings). These might look like
Warning: DOMDocument::loadHTML(): htmlParseEntityRef: expecting ';' in Entity, line: 38
There are several fixes on the html which have been committed, submitted or logged
for 4.4. If you do hit these errors, however, you should follow up with patches / issues in CiviCRM not in this extension

To use this extension within another extension you need to
1) declare your settings. This should be done in a file page YourExtensionRoot/settings/uniquefilename.entity_setting.php
see below for specification for the declaration

2) register your path - use a function like below

3) empty your cache (civicrm_cache)... whenever you change your declaration

**********Path registration function *******************
/**
 * Implementation of entity setting hook_civicrm_alterEntitySettingsFolders
 * declare folders with entity settings
 */

function hook_civicrm_alterEntitySettingsFolders(&$folders) {
  static $configured = FALSE;
  if ($configured) return;
  $configured = TRUE;

  $extRoot = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
  $extDir = $extRoot . 'settings';
  if(!in_array($extDir, $folders)){
    $folders[] = $extDir;
  }
}

Setting Spec
--------------

This example declares 2 settings

<?php

return array (
  array(
    'key' => 'nz.co.fuzion.frontendpageoptions',
    'entity' => 'event',
    'name' => 'event_thankyou_redirect',
    'type' => 'String',
    'html_type' => 'text',
    'add' => '1.0',
    'title' => 'Thank You page Redirect',
    'description' => 'Page to redirect to instead of the normal Thank You page',
    'help_text' => 'Please enter the full or relative url including http for full',
    'add_to_setting_form' => TRUE,
    'form_child_of_parents_parents_parent' => 'thankyou_title',
  ),
  array(
    'key' => 'nz.co.fuzion.frontendpageoptions',
    'entity' => 'event',
    'name' => 'event_cidzero_relationship_type_id',
    'type' => 'Integer',
    'html_type' => 'select',
    'options_callback' => array(
      'class' => 'CRM_Contact_BAO_Relationship',
      'method' => 'getContactRelationshipType',
      'arguments' => array(NULL, NULL, NULL, NULL, TRUE),
    ),
    'add' => '1.0',
    'title' => 'Relationship for On Behalf Forms',
    'description' => 'Relationship type to create on related registrations',
    'help_text' => 'When cid=0 is in the url the registration is for someone else. The relationship will be created if the contact is new',
    'add_to_setting_form' => TRUE,
    'form_child_of_parents_parents_parent' => 'expiration_time',
    'required' => FALSE,
  ),
);

-- key - unique key of your extension
-- entity - name of entity
-- name - name of setting
-- type - see standard CiviCRM schema xml for types
-- add (optional) - which version of your extension did you add it in - no actual application - just following civicrm protocol
-- add_to_setting_form (boolean) - if you select this the setting will be added to the setting form (if supported
     current support for action_schedule, event, relationship_type, contribution_page). The following settings
     are only used if this is set (although it is good practice to define description & help either way)
-- title - this will be the label for the input field
-- description - this will show below the input box
-- help_text - can be expanded from the question mark (needs https://github.com/civicrm/civicrm-core/pull/2243)
-- html_type - describes type of input box
-- options_callback - array containing class, method & arguments to get options for select boxes
-- required - is input box required
-- form_child_of_id - id of the parent element for your input row (should be a table)
-- form_child_of_parent - - ah but it turned out not all tables had an id - so if you want to start a level down
     & crawl up one .. use this
-- form_child_of_parents_parent - but then it turned out not all trs had an id - so down one more & crawl up 2
-- form_child_of_parents_parents_parent  OK - this is pretty bad. We started to hit the situation of <table><tr><td><input id='blah'>
     and yes - if I really felt like the above would cover all the permutations I would rationalise & refactor but I'm still getting the
     feel of all the variations as to what does & doesn't get ids on it. A later release may alter these (if you are using this
     extension make yourself heard because it will affect how much backward compatility is maintained in any change)
