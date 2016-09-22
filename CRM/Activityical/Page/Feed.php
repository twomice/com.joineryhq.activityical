<?php

require_once 'CRM/Core/Page.php';

class CRM_Activityical_Page_Feed extends CRM_Core_Page {
  public function run() {

    $feed = new CRM_Activityical_Feed($contact_id);
    $output = $feed->getContents();

    // TODO: support caching; use cache time instaed of time();
    $time = time();
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s \G\M\T', $time));
    require_once 'CRM/Utils/ICalendar.php';
    CRM_Utils_ICalendar::send($output);
    exit;
  }
}
