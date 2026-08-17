<?php

/**
 * English language strings for UAE FTA Onboarding pages.
 * Used by lang('Onboarding.key') helper in views that support ?lang= switching.
 */
return [
    // Page titles
    'title_onboard'  => 'Linking Request',
    'title_reverify' => 'Reverifying / Delinking Request',

    // Section headings
    'section_applicant' => 'Applicant Details',
    'section_entity'    => 'Entity Details',

    // Applicant form labels
    'label_email'   => 'Registered EmaraTax E-mail Address',
    'label_country' => 'Country Code',
    'label_mobile'  => 'Registered Mobile Number',
    'label_tin'     => 'TIN (Tax Identifier Number)',
    'optional'      => 'Optional',

    // Action selection
    'select_action' => 'Select the action',
    'act_reverify'  => 'Reverify',
    'act_delink'    => 'Delinking',

    // Entity form labels
    'label_legal_type'     => 'Legal Type',
    'label_entity_name_en' => 'Entity Legal Name in English',
    'label_entity_name_ar' => 'Entity Legal Name in Arabic',
    'label_vat_trn'        => 'VAT TRN',
    'label_effective_date' => 'Effective Date of Registration',
    'label_submission_date' => 'Date of Submission',

    // Declaration & buttons
    'declaration'  => 'I declare that all information provided is true, accurate and complete to the best of my knowledge and belief.',
    'btn_verify'   => 'Verify Applicant Details',
    'btn_submit'   => 'Submit',

    // JS error messages
    'err_required'   => 'Please enter your TIN and Email address.',
    'err_verify_fail' => 'Verification failed.',
    'err_connection'  => 'Connection error. Please try again.',

    // Processing overlay
    'proc_title'  => 'Generating your PEPPOL ID…',
    'proc_step1'  => 'Verifying details with PEPPOL registry…',
    'proc_step2'  => 'Registering participant on the network…',
    'proc_step3'  => 'Generating your PEPPOL endpoint…',
    'proc_step4'  => 'Finalising registration…',
    'proc_note'   => 'Please do not close or refresh this window',

    // Success page
    'success_registered'   => 'Registration Successful',
    'success_reverify'     => 'Re-verification Successful',
    'success_delink'       => 'Deregistration Successful',
    'success_already'      => 'Already Registered',
    'msg_registered'       => 'Your business is now registered on the UAE PEPPOL e-Invoicing network.',
    'msg_reverify'         => 'Your PEPPOL e-Invoicing registration has been successfully re-verified.',
    'msg_delink'           => 'Your business has been deregistered from the UAE PEPPOL e-Invoicing network.',
    'msg_already'          => 'This TIN is already linked to the PEPPOL network.',
    'col_company'          => 'Company',
    'col_vat_trn'          => 'VAT TRN',
    'col_peppol_id'        => 'PEPPOL Participant ID',
    'col_access_point'     => 'Access Point',
    'col_date'             => 'Date',
    'email_sent_to'        => 'A confirmation email has been sent to',
    'whats_next'           => "What's next?",
    'whats_next_body'      => 'Your business can now send and receive e-invoices via the PEPPOL network. Trading partners can address invoices to your PEPPOL Participant ID above.',
    'for_support'          => 'For support:',

    // Footer
    'footer_tagline' => 'Accredited PEPPOL Access Point • UAE',
];
