<?php
/**
 * Borrowed from CiviCRM's modifier.crmICalText.php and
 * CRM_Utils_ICalendar::formatText().
 */

/**
 * Format the given string as HTML in an ical suitable format. This function
 * doesn't add the FMTTYPE=text/html parameter or any other left-of-colon
 * string, but only formats the right-of-colon value.
 *
 * We don't use CRM_Utils_ICalendar::formatText() because it uses strip_tags(),
 * which defeats the purpose of an HTML string.
 *
 * @param string $str
 *
 * @return string
 *   formatted text
 */
function smarty_modifier_activityicalHtml($str) {
  $ret = $str;

  $ret = str_replace('\\', '\\\\', $ret);
  $ret = str_replace(',', '\,', $ret);
  $ret = str_replace(';', '\;', $ret);
  $ret = str_replace(array("\r\n", "\n", "\r"), "\\n ", $ret);
  $ret = implode("\n ", str_split($ret, 50));
  return $ret;
}
