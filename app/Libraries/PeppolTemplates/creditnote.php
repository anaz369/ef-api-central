<?php
// PEPPOL BIS 3.0 / PINT AE — Credit Note template
// Uses <CreditNote> root element and <CreditedQuantity> instead of <InvoicedQuantity>
return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2">

    <cbc:CustomizationID>SET_CUSTOMIZATION_ID</cbc:CustomizationID>
    <cbc:ProfileID>urn:peppol:bis:billing</cbc:ProfileID>
    <cbc:ProfileExecutionID>SET_PROFILE_EXECUTION_ID</cbc:ProfileExecutionID>
    <cbc:ID>SET_INVOICE_NUMBER</cbc:ID>
    <cbc:UUID>SET_UUID</cbc:UUID>
    <cbc:IssueDate>SET_ISSUE_DATE</cbc:IssueDate>
    SET_DUE_DATE
    <cbc:CreditNoteTypeCode>SET_INVOICE_TYPE_CODE</cbc:CreditNoteTypeCode>
    <cbc:DocumentCurrencyCode>SET_CURRENCY</cbc:DocumentCurrencyCode>
    SET_BILLING_REFERENCE

    <cac:AccountingSupplierParty>
        <cac:Party>
            <cbc:EndpointID schemeID="SET_SELLER_SCHEME">SET_SELLER_VAT</cbc:EndpointID>
            <cac:PartyName>
                <cbc:Name>SET_SELLER_NAME</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:StreetName>SET_SELLER_STREET</cbc:StreetName>
                <cbc:CityName>SET_SELLER_CITY</cbc:CityName>
                <cbc:PostalZone>SET_SELLER_POSTAL</cbc:PostalZone>
                <cbc:CountrySubentity>SET_SELLER_STATE</cbc:CountrySubentity>
                <cac:Country>
                    <cbc:IdentificationCode>SET_SELLER_COUNTRY</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>SET_SELLER_VAT</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>SET_SELLER_NAME</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>

    <cac:AccountingCustomerParty>
        <cac:Party>
            <cbc:EndpointID schemeID="SET_BUYER_SCHEME">SET_BUYER_VAT</cbc:EndpointID>
            <cac:PartyName>
                <cbc:Name>SET_BUYER_NAME</cbc:Name>
            </cac:PartyName>
            <cac:PostalAddress>
                <cbc:StreetName>SET_BUYER_STREET</cbc:StreetName>
                <cbc:CityName>SET_BUYER_CITY</cbc:CityName>
                <cbc:PostalZone>SET_BUYER_POSTAL</cbc:PostalZone>
                <cbc:CountrySubentity>SET_BUYER_STATE</cbc:CountrySubentity>
                <cac:Country>
                    <cbc:IdentificationCode>SET_BUYER_COUNTRY</cbc:IdentificationCode>
                </cac:Country>
            </cac:PostalAddress>
            <cac:PartyTaxScheme>
                <cbc:CompanyID>SET_BUYER_VAT</cbc:CompanyID>
                <cac:TaxScheme>
                    <cbc:ID>VAT</cbc:ID>
                </cac:TaxScheme>
            </cac:PartyTaxScheme>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>SET_BUYER_NAME</cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>

    <cac:PaymentMeans>
        <cbc:PaymentMeansCode>SET_PAYMENT_CODE</cbc:PaymentMeansCode>
        SET_PAYMENT_ACCOUNT
    </cac:PaymentMeans>

    SET_TAX_TOTALS

    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="SET_CURRENCY">SET_LINE_EXTENSION_TOTAL</cbc:LineExtensionAmount>
        <cbc:TaxExclusiveAmount currencyID="SET_CURRENCY">SET_TAX_EXCLUSIVE_TOTAL</cbc:TaxExclusiveAmount>
        <cbc:TaxInclusiveAmount currencyID="SET_CURRENCY">SET_TAX_INCLUSIVE_TOTAL</cbc:TaxInclusiveAmount>
        <cbc:PayableAmount currencyID="SET_CURRENCY">SET_PAYABLE_AMOUNT</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>

    SET_INVOICE_LINES

</CreditNote>
XML;
