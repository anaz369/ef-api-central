<?php
// PEPPOL BIS 3.0 / PINT AE — Invoice line template
return <<<XML

    <cac:InvoiceLine>
        <cbc:ID>SET_LINE_ID</cbc:ID>
        <cbc:InvoicedQuantity unitCode="SET_UOM">SET_QUANTITY</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="SET_CURRENCY">SET_LINE_EXTENSION_AMOUNT</cbc:LineExtensionAmount>
        SET_LINE_ALLOWANCES
        <cac:Item>
            <cbc:Description>SET_ITEM_DESC</cbc:Description>
            <cbc:Name>SET_ITEM_NAME</cbc:Name>
            <cac:ClassifiedTaxCategory>
                <cbc:ID>SET_TAX_CATEGORY</cbc:ID>
                <cbc:Percent>SET_TAX_PERCENT</cbc:Percent>
                SET_EXEMPTION_REASON
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:ClassifiedTaxCategory>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="SET_CURRENCY">SET_UNIT_PRICE</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="SET_UOM">1</cbc:BaseQuantity>
            <cac:AllowanceCharge>
                <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
                <cbc:Amount currencyID="SET_CURRENCY">0.00</cbc:Amount>
                <cbc:BaseAmount currencyID="SET_CURRENCY">SET_GROSS_PRICE</cbc:BaseAmount>
            </cac:AllowanceCharge>
        </cac:Price>
        SET_ITEM_PRICE_EXT
    </cac:InvoiceLine>
XML;
