@extends('layouts.app',[
  'page_title'=>"راسلني",
  'page_description'=>"صندوق المراسلة ".strip_tags($settings->contact_text)
])
@section('content')
<div class="col-12 p-0">
  <div style="width:650px;max-width: 100%;text-align: justify;" class="mx-auto p-3 font-2 optimize-fonts">
    {!!$settings->contact_text!!}
  </div>
  <div style="width:650px;max-width: 100%;text-align: justify;" class="mx-auto p-3 font-2 naskh">
    <form class="" method="POST" action="{{route('front.contact.store')}}">
    @csrf
      <div class="col-12 py-3">
        <div class="col-12">
          <label for="contact_name" class="form-label">الاسم</label>
          <input type="text" id="contact_name" name="contact_name" 
                 class="form-control @error('contact_name') is-invalid @enderror" 
                 placeholder="أدخل اسمك الكامل" 
                 required minlength="3" maxlength="255" 
                 value="{{old('contact_name')}}">
          @error('contact_name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-12 py-3">
        <div class="col-12">
          <label for="contact_email" class="form-label">البريد الإلكتروني</label>
          <input type="email" id="contact_email" name="contact_email" 
                 class="form-control @error('contact_email') is-invalid @enderror" 
                 placeholder="example@email.com" 
                 required 
                 value="{{old('contact_email')}}">
          @error('contact_email')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-12 py-3">
        <div class="col-12">
          <label for="contact_phone" class="form-label">رقم الهاتف</label>
          <input type="tel" id="contact_phone" name="contact_phone" 
                 class="form-control @error('contact_phone') is-invalid @enderror" 
                 placeholder="05xxxxxxxx" 
                 required minlength="8" maxlength="20" 
                 value="{{old('contact_phone')}}">
          @error('contact_phone')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-12 py-3">
        <div class="col-12">
          <label for="contact_message" class="form-label">الرسالة</label>
          <textarea id="contact_message" class="form-control @error('contact_message') is-invalid @enderror" 
                    name="contact_message" 
                    style="min-height:200px" 
                    placeholder="اكتب رسالتك هنا..." 
                    required minlength="3" maxlength="1000">{{old('contact_message')}}</textarea>
          <small class="form-text text-muted">الحد الأقصى 1000 حرف</small>
          @error('contact_message')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>
      <div class="col-12 py-3">
        <div class="col-12">
          <button class="btn btn-success" type="submit">
            <i class="fas fa-paper-plane me-2"></i>
            إرسال الرسالة
          </button>
        </div>
      </div>
    </form>
    </div>
</div>
@endsection