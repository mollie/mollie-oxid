[{if !method_exists($oViewConf, 'mollieSuppressBasketModal') || $oViewConf->mollieSuppressBasketModal() === false }]
    [{$smarty.block.parent}]
[{/if}]