BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Joinery//NONSGML CiviCRM activities iCalendar feed//EN
METHOD:PUBLISH
{foreach from=$activities key=uid item=activity}
BEGIN:VEVENT
UID:activity-{$activity.id}-{$smarty.now|crmICalDate}@{$domain}
SUMMARY:{$activity.activity_subject|crmICalText}
{if $activity.description}
DESCRIPTION:{$activity.description|crmICalText}
{/if}
{if $activity.activity_type}
CATEGORIES:{$activity.activity_type|crmICalText}
{/if}
CALSCALE:GREGORIAN
DTSTAMP;VALUE=DATE-TIME:{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'|crmICalDate}
{if $activity.activity_date_time}
DTSTART;VALUE=DATE-TIME:{$activity.activity_date_time|crmICalDate}
{/if}
{if $activity.activity_duration}
DURATION:P{$activity.activity_duration}M
{else}
DTEND;VALUE=DATE-TIME:{$activity.activity_date_time|crmICalDate}
{/if}
{if $activity.activity_location}
LOCATION:{$activity.activity_location|crmICalText}
{/if}
{if $activity.contact_email}
ORGANIZER:MAILTO:{$activity.contact_email|crmICalText}
{/if}
URL:{$base_url}/civicrm/activity?action=view&context=activity&reset=1&cid={$activity.contact_id}&id={$activity.id}&atype={$activity.activity_type_id}
CONTACT;ALTREP={$base_url}/civicrm/contact/view?reset=1&cid={$activity.source_id}:{$activity.source_display_name}
END:VEVENT
{/foreach}
END:VCALENDAR
