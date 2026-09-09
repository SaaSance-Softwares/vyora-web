<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fulfillment_type',
        'color',
        'sort_order',
        'sms_template_id',
        'email_template_id',
        'whatsapp_template_id',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function smsTemplate()
    {
        return $this->belongsTo(SmsTemplate::class);
    }

    public function emailTemplate()
    {
        return $this->belongsTo(EmailTemplate::class);
    }

    public function whatsappTemplate()
    {
        return $this->belongsTo(WhatsappTemplate::class);
    }
}
