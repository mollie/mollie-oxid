[{assign var="sMollieErrorMessage" value=$oViewConf->mollieGetErrorMessage()}]
[{if $sMollieErrorMessage}]
    <div class="alert alert-danger">[{$sMollieErrorMessage}]</div>
[{/if}]
[{$smarty.block.parent}]