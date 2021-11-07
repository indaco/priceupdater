{*
* Copyright since 2007 PrestaShop SA and Contributors
* PrestaShop is an International Registered Trademark & Property of PrestaShop SA
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.md.
* It is also available through the world-wide-web at this URL:
* https://opensource.org/licenses/AFL-3.0
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* @author    indaco <github@mircoveltri.me>
* @copyright Since 2021 Mirco Veltri
* @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
*/
*}

{if $is_dry_run}
    {assign var="alert_type" value="alert-info"}
{else}
    {assign var="alert_type" value="alert-success"}
{/if}

<div class="panel">
    <h3><i class="icon icon-list-alt"></i>&nbsp;{l s=$report_name d='Modules.Priceupdater.GetContentReport'}</h3>

    <div class="alert {$alert_type|escape:'html':'UTF-8'}" role="alert">
    {foreach name=message key=messageId item=message from=$messages}
        {$message}
    {/foreach}
    </div>

    <br/>

    {if isset($report_data) and $report_data|count gt 1}
        <div class="table-responsive-lg">
            <table class="table table-striped table-bordered">
                <caption><h3>{$report_data|@count} {l s='products and product variants updated' d='Modules.Priceupdater.GetContentReport'}</h3></caption>
                <thead>
                <tr>
                    <th scope="col" style="font-weight: bold; width: 5%">#</th>
                    <th scope="col" style="font-weight: bold; width: 10%">{l s='Product ID' d='Modules.Priceupdater.GetContentReport'}</th>
                    <th scope="col" style="font-weight: bold; width: 40%">{l s='Product Name' d='Modules.Priceupdater.GetContentReport'}</th>
                    <th scope="col" style="font-weight: bold; width: 15%">{l s='EAN' d='Modules.Priceupdater.GetContentReport'}</th>
                    <th scope="col" style="font-weight: bold; width: 15%">{l s='Actual Price' d='Modules.Priceupdater.GetContentReport'}&nbsp;({$currency})</th>
                    <th scope="col" style="font-weight: bold; width: 15%">{l s='New Price' d='Modules.Priceupdater.GetContentReport'}&nbsp;({$currency})</th>
                    <th scope="col" style="font-weight: bold; width: 5%">{l s='Updated' d='Modules.Priceupdater.GetContentReport'}</th>
                </tr>

                </thead>
                <tbody>

                {foreach name=row key=myId item=row from=$report_data}
                    <tr>
                        <td>{$myId+1|escape:'html':'UTF-8'}</td>
                        <td>{$row["id_product"]|escape:'html':'UTF-8'}</td>
                        <td>{$row["name"]|escape:'html':'UTF-8'}</td>
                        <td>{$row["ean13"]|escape:'html':'UTF-8'}</td>
                        <td>{$row["actual_price"]|escape:'html':'UTF-8'}</td>
                        <td>{$row["new_price"]|escape:'html':'UTF-8'}</td>
                        <td style="text-align: center">
                            {if $row["updated"]|escape:'html':'UTF-8'}
                                <i class="icon icon-ok-sign" style="color: green"></i>
                            {else}
                                <i class="icon icon-remove-sign" style="color: red"></i>
                            {/if}

                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <div class="alert alert-warning" role="alert">
            <p>{l s='No records found!' d='Modules.Priceupdater.GetContentReport'}</p>
        </div>
    {/if}
</div>
