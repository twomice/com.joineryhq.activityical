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
  
  <div class="accordion" id="activityical-advancedoptions-accordion">
    <div class="crm-accordion-wrapper collapsed">
      <div class="crm-accordion-header">
        {ts}Advanced Options{/ts}
      </div>
      <div class="crm-accordion-body activityical-accordion-body">
        <p>
          {ts}Add any of the following to the above URL to affect the behavior of your feed{/ts}:
        </p>
        <ul>

          {foreach from=$advanced_options key=label item=text}
            <li><strong>{$label}</strong>: {$text}</li>
          {/foreach}
        </ul>
      </div>
    </div>
  </div>


  <div class="accordion" id="activityical-rebuildurl-accordion">
    <div class="crm-accordion-wrapper collapsed">
      <div class="crm-accordion-header">
        {ts}Rebuild Feed URL{/ts}
      </div>
      <div class="crm-accordion-body activityical-accordion-body">
        <p>
        {ts}Anyone who knows your feed URL will be able to view your activities. If you think this URL is known by people who should not have that access, you can rebuild a new URL, so that any existing URL no longer works.{/ts}
        </p>
        <p>
        {ts}Note: This will cause the existing URL to stop working, so if you're already using the URL in your calendar software (Google Calendar, etc.), you'll need to update that software with the new URL.{/ts}
        </p>
        {include file="CRM/common/formButtons.tpl" location="bottom"}
        <div class="clear"></div>
      </div>
    </div>
  </div>
</div>


