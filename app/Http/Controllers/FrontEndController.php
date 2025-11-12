<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ContactFormRequest;
use App\Models\Portfolio;
use App\Models\Faq;
use App\Models\Article;
use App\Models\Client;
use App\Models\Color;
use App\Models\Contact;
use App\Models\Profile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FrontEndController extends Controller
{
    public function index(Request $request){
        return view('front.index');
    }
    public function portfolios(Request $request){
        $portfolios = Cache::remember('portfolios_list', 1800, function () {
            return Portfolio::orderBy('id', 'DESC')->get();
        });
        return view('front.portfolios', compact('portfolios'));
    }
    
    public function portfolio_show(Request $request, Portfolio $portfolio){
        return view('front.portfolio-show', compact('portfolio'));
    }
    
    public function client_show(Request $request, Client $client){
        return view('front.client-show', compact('client'));
    }
    
    public function article_show(Request $request, Article $article){
        $article->increment('views');
        return view('front.article-show', compact('article'));
    }
    
    public function clients(Request $request){
        $clients = Cache::remember('clients_list', 1800, function () {
            return Client::orderBy('id', 'DESC')->get();
        });
        return view('front.clients', compact('clients'));
    }
    
    public function articles(Request $request){
        $articles = Cache::remember('articles_list', 1800, function () {
            return Article::orderBy('id', 'DESC')->get();
        });
        return view('front.articles', compact('articles'));
    }
    public function contact_post(ContactFormRequest $request){
        try {
            $contact = Contact::create($request->validated());
            
            $this->notify_me("لديك رسالة جديدة من ".$request->contact_name, route('admin.contact.index'));
            
            Log::info('Contact form submitted', [
                'contact_id' => $contact->id,
                'contact_name' => $request->contact_name,
            ]);
            
            emotify('success','تم إرسال رسالتك بنجاح');
        } catch (\Exception $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated(),
            ]);
            
            emotify('error','حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة مرة أخرى');
        }
        
        return redirect()->back();
    }
    public function donate(Request $request){
        return view('front.donate');
    }
    public function contact(Request $request){
        return view('front.contact');
    }
    public function hire(Request $request){
        return view('front.hire');
    }
}
