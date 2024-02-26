[{assign var="sMollieErrorMessage" value=""}]
[{if $sMollieErrorMessage}]
    <div class="alert alert-info">[{$sMollieErrorMessage}]</div>
    <div class="spacer"></div>
[{/if}]
[{$smarty.block.parent}]