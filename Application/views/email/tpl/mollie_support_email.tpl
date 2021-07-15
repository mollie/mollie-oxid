[{assign var="shop"      value=$oEmailView->getShop()}]
[{assign var="oViewConf" value=$oEmailView->getViewConfig()}]

[{$enquiry}]<br/><br/>
<hr>
<b>Contact</b>
<table>
    <tr>
        <td>Name</td>
        <td>[{$contact_name}]</td>
    </tr>
    <tr>
        <td>Email</td>
        <td>[{$contact_email}]</td>
    </tr>
</table>
<hr>
<b>Shop information</b>
<table>
    <tr>
        <td>Company</td>
        <td>[{$shop->oxshops__oxcompany->value}]</td>
    </tr>
    <tr>
        <td>Shop URL</td>
        <td>[{$shop->oxshops__oxurl->value}]</td>
    </tr>
    <tr>
        <td>Shop version</td>
        <td>[{$shopversion}]</td>
    </tr>
    <tr>
        <td>Mollie module version</td>
        <td>[{$moduleversion}]</td>
    </tr>
</table>
