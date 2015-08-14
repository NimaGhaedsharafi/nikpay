<?php

return [
    'OK'                                   => 'تراکنش موفقیت آمیز بود',
    'Canceled By User'                     => 'تراکنش توسط خریدار کنسل شده است.',
    'Invalid Amount'                       => 'مبلغ سند برگشتی، از مبلغ تراکنش اصلی بیشتر است.',
    'Invalid Transaction'                  => 'درخواست برگشت یک تراکنش رسیده است، در حالی که تراکنش اصلی پیدا نمی شود.',
    'Invalid Card Number'                  => 'شماره کارت اشتباه است.',
    'No Such Issuer'                       => 'چنین صادر کننده کارتی وجود ندارد.',
    'Expired Card Pick Up'                 => 'از تاریخ انقضای کارت گذشته است و کارت دیگر معتبر نیست.',
    'Allowable PIN Tries Exceeded Pick Up' => 'رمز کارت (PIN) ۳ مرتبه اشتباه وارد شده است در نتیجه کارت غیر فعال خواهد شد.',
    'Incorrect PIN'                        => 'خریدار رمز کارت (PIN) را اشتباه وارد کرده است.',
    'Exceeds Withdrawal Amount Limit'      => 'مبلغ بیش از سقف برداشت می باشد.',
    'Transaction Cannot Be Completed'      => 'تراکنش Authorize شده است (شماره PIN و PAN درست هستند) ولی امکان سند خوردن وجود ندارد.',
    'Response Received Too Late'           => 'تراکنش در شبکه بانکی Timeout خورده است.',
    'Suspected Fraud Pick Up'              => 'خریدار یا فیلد CVV2 و یا فیلد ExpDate را اشتباه وارد کرده است (یا اصلا وارد نکرده است).',
    'No Sufficient Funds'                  => 'موجودی حساب خریدار، کافی نیست.',
    'Issuer Down Slm'                      => 'سیستم بانک صادر کننده کارت خریدار، در وضعیت عملیاتی نیست.',
    'TME Error'                            => 'کلیه خطاهای دیگر بانکی باعث ایجاد چنین خطایی می گردد.',
];
 