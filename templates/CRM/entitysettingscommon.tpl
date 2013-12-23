{foreach from=$entitySettings key=setting item=settingspec}
{assign var='rowid' value='entity-setting-'|cat:$setting}
<table>
  <tr id={$rowid}>
    <td class="label">{$form.$setting.label}</td>
    <td>{$form.$setting.html}
      {if $settingspec.description}
        <br>
        <span class="description">{ts}{$settingspec.description}{/ts}</span>
      {/if}
      {if $settingspec.help_text}
        {assign var='helpid' value='id-'|cat:$setting}
        {help id=$helpid}
      {/if}
    </td>
  </tr>
</table>
{/foreach}