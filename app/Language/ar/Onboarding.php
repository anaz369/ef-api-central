<?php

/**
 * Arabic language strings for UAE FTA Onboarding pages.
 * Used by lang('Onboarding.key') helper in views that support ?lang= switching.
 */
return [
    // Page titles
    'title_onboard'  => 'طلب الربط بشبكة بيبول للفوترة الإلكترونية',
    'title_reverify' => 'طلب إعادة التحقق / إلغاء الربط',

    // Section headings
    'section_applicant' => 'بيانات مقدم الطلب',
    'section_entity'    => 'بيانات المنشأة',

    // Applicant form labels
    'label_email'   => 'عنوان البريد الإلكتروني المسجل في إماراتاكس',
    'label_country' => 'رمز الدولة',
    'label_mobile'  => 'رقم الجوال المسجل',
    'label_tin'     => 'رقم التعريف الضريبي (TIN)',
    'optional'      => 'اختياري',

    // Action selection
    'select_action' => 'اختر الإجراء',
    'act_reverify'  => 'إعادة التحقق',
    'act_delink'    => 'إلغاء الربط',

    // Entity form labels
    'label_legal_type'      => 'النوع القانوني',
    'label_entity_name_en'  => 'الاسم القانوني للمنشأة بالإنجليزية',
    'label_entity_name_ar'  => 'الاسم القانوني للمنشأة بالعربية',
    'label_vat_trn'         => 'رقم التسجيل الضريبي للقيمة المضافة (VAT TRN)',
    'label_effective_date'  => 'تاريخ سريان التسجيل',
    'label_submission_date' => 'تاريخ التقديم',

    // Declaration & buttons
    'declaration'  => 'أُقرّ بأن جميع المعلومات المقدمة صحيحة ودقيقة وكاملة وفق أفضل ما لديّ من معرفة واعتقاد.',
    'btn_verify'   => 'التحقق من بيانات مقدم الطلب',
    'btn_submit'   => 'إرسال',

    // JS error messages
    'err_required'    => 'يرجى إدخال رقم التعريف الضريبي وعنوان البريد الإلكتروني.',
    'err_verify_fail' => 'فشل التحقق.',
    'err_connection'  => 'خطأ في الاتصال. يرجى المحاولة مرة أخرى.',

    // Processing overlay
    'proc_title'  => 'جارٍ إنشاء معرّف بيبول الخاص بك…',
    'proc_step1'  => 'جارٍ التحقق من البيانات مع سجل بيبول…',
    'proc_step2'  => 'جارٍ تسجيل المشارك في الشبكة…',
    'proc_step3'  => 'جارٍ إنشاء نقطة نهاية بيبول الخاصة بك…',
    'proc_step4'  => 'جارٍ إتمام التسجيل…',
    'proc_note'   => 'يرجى عدم إغلاق هذه النافذة أو تحديثها',

    // Success page
    'success_registered' => 'تم التسجيل بنجاح',
    'success_reverify'   => 'تمت إعادة التحقق بنجاح',
    'success_delink'     => 'تم إلغاء التسجيل بنجاح',
    'success_already'    => 'مسجّل مسبقاً',
    'msg_registered'     => 'تم تسجيل منشأتك في شبكة بيبول للفوترة الإلكترونية في الإمارات العربية المتحدة.',
    'msg_reverify'       => 'تم التحقق من تسجيلك في شبكة بيبول للفوترة الإلكترونية بنجاح.',
    'msg_delink'         => 'تم إلغاء تسجيل منشأتك من شبكة بيبول للفوترة الإلكترونية في الإمارات.',
    'msg_already'        => 'رقم التعريف الضريبي هذا مرتبط مسبقاً بشبكة بيبول.',
    'col_company'        => 'المنشأة',
    'col_vat_trn'        => 'رقم التسجيل الضريبي (VAT TRN)',
    'col_peppol_id'      => 'معرّف المشارك في بيبول',
    'col_access_point'   => 'نقطة الوصول',
    'col_date'           => 'التاريخ',
    'email_sent_to'      => 'تم إرسال بريد إلكتروني للتأكيد إلى',
    'whats_next'         => 'ما التالي؟',
    'whats_next_body'    => 'يمكن لمنشأتك الآن إرسال واستلام الفواتير الإلكترونية عبر شبكة بيبول. يمكن للشركاء التجاريين توجيه الفواتير إلى معرّف المشارك في بيبول أعلاه.',
    'for_support'        => 'للدعم:',

    // Footer
    'footer_tagline' => 'نقطة وصول بيبول معتمدة • الإمارات العربية المتحدة',
];
