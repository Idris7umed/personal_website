<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'contact_name' => 'required|string|min:3|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|min:8|max:20',
            'contact_message' => 'required|string|min:3|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'contact_name.required' => 'الاسم مطلوب',
            'contact_name.min' => 'الاسم يجب أن يكون على الأقل 3 أحرف',
            'contact_email.required' => 'البريد الإلكتروني مطلوب',
            'contact_email.email' => 'يرجى إدخال بريد إلكتروني صحيح',
            'contact_phone.required' => 'رقم الهاتف مطلوب',
            'contact_message.required' => 'الرسالة مطلوبة',
            'contact_message.min' => 'الرسالة يجب أن تكون على الأقل 3 أحرف',
            'contact_message.max' => 'الرسالة يجب ألا تزيد عن 1000 حرف',
        ];
    }
}
