<div id="activityical-activity-links">
  {ts}iCalendar{/ts}:
  <a class="activityical-activity-link" href="{$feed_url}">{ts}Feed{/ts}</a>
  {if $access_details}
    |
    <a href="{crmURL p='civicrm/activityical/details' q="reset=1&contact_id=`$contact_id`"}">{ts}Details{/ts}</a>
  {/if}
</div>