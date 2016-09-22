{* HEADER *}
{if $is_other_contact}
<h2>{ts}Details for contact{/ts}: {$display_name}</h2>
{/if}

<div class="crm-section">
  {if $is_other_contact}
    <div>{ts 1=$display_name}The CiviCRM assigned activities feed for %1 is available at this URL{/ts}:</div>
  {else}
    <div>{ts}Your CiviCRM assigned activities feed is available at this URL{/ts}:</div>
  {/if}
  <div class="activityical-feed-url-display"><input type="text" value="{$feed_url}" id="feed_url" readonly="readonly"/></div>
</div>

{$form.contact_id.html}

{* FOOTER *}
<div class="header-dark" id="activityical-header-rebuild-url">{ts}Rebuild feed URL{/ts}</div>
<div class="view-content">
  <p>
  {ts}Anyone who knows your feed URL will be able to view your activities. If you think this URL is known by people who should not have that access, you can rebuild a new URL, so that any existing URL no longer works.{/ts}
  </p>
  <p>
  {ts}Note: This will cause the existing URL to stop working, so if you're already using the URL in your calendar software (Google Calendar, etc.), you'll need to update that software with the new URL.{/ts}
  </p>
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
