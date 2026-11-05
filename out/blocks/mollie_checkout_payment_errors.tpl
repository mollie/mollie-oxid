[{$smarty.block.parent}]

[{if $oView->getPaymentError() == -50}]
    <div class="[{if $oViewConf->getActiveTheme() == 'azure'}]status error[{else}]alert alert-danger[{/if}]">[{$oView->getPaymentErrorText()}]</div>
[{/if}]
