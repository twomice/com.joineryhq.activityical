<?php

require_once 'CRM/Core/Page.php';

class CRM_Activityical_Page_Feed extends CRM_Core_Page {

  public function run() {
    $contact_id = $_GET['cid'] ?? NULL;
    $feed = CRM_Activityical_Feed::getInstance($contact_id);
    $output = $feed->getContents();

    // TODO: support caching; use cache time instaed of time();
    $time = time();
    CRM_Utils_System::setHttpHeader('Last-Modified', gmdate('D, d M Y H:i:s \G\M\T', $time));
    require_once 'CRM/Utils/ICalendar.php';
    CRM_Utils_ICalendar::send($output, 'text/calendar', NULL, 'civicrm_activities.ical', 'attachment');
    exit;
  }

}
