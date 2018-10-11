
{*include custom data js file*}
  {include file="CRM/common/customData.tpl"}
<script type="text/javascript">
  CRM.$('.total_amount-section, #paymentDetails_Information').after(' <div class="crm-container"><div class="clear"></div><div id="customData"></div></div>');
  CRM.buildCustomData("{$customDataType}");
</script>
