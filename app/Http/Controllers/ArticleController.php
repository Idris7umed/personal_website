<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $articles = Article::query()
            ->when($request->key, function($q) use ($request) {
                $key = $request->key;
                $q->where('title', 'LIKE', "%{$key}%")
                  ->orWhere('description', 'LIKE', "%{$key}%");
            })
            ->with('user:id,name')
            ->orderBy('id', 'DESC')
            ->paginate(15);
            
        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.articles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:5120',
        ]);

        try {
            $article = Article::create([
                'user_id' => auth()->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'featured' => $request->featured == 1 ? 1 : 0,
                'slug' => uniqid()
            ]);
            
            $article->update([
                'slug' => $this->generate_slug($article->id . '-' . $article->title),
            ]); 
            if ($request->hasFile('image')) {
                $file = $this->store_file([
                    'source' => $request->image,
                    'validation' => "image",
                    'path_to_save' => '/uploads/articles/',
                    'type' => 'ARTICLE', 
                    'user_id' => auth()->user()->id,
                    'resize' => [500, 3000],
                    'small_path' => 'small/',
                    'visibility' => 'PUBLIC',
                    'file_system_type' => env('FILESYSTEM_DRIVER'),
                    'compress' => 'auto'
                ])['filename']; 
                
                $this->use_hub_file($file, $article->id, auth()->user()->id);
                $article->update(['image' => $file]);
            }
            
            // Clear articles cache
            Cache::forget('articles_list');
            
            Log::info('Article created', ['article_id' => $article->id]);
            
            emotify('success', 'تمت العملية بنجاح');
        } catch (\Exception $e) {
            Log::error('Article creation failed', ['error' => $e->getMessage()]);
            emotify('error', 'حدث خطأ أثناء إنشاء المقال');
        }
        
        return redirect()->route('admin.articles.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function show(Article $article)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function edit(Article $article)
    {
        return view('admin.articles.edit',compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'image' => 'nullable|image|max:5120',
        ]);

        try {
            $article->update([
                'title' => $request->title,
                'description' => $request->description,
                'link' => $request->link,
                'featured' => $request->featured == 1 ? 1 : 0,
            ]);
            
            if ($request->hasFile('image')) {
                $this->remove_hub_file($article->image);
                $file = $this->store_file([
                    'source' => $request->image,
                    'validation' => "image",
                    'path_to_save' => '/uploads/articles/',
                    'type' => 'ARTICLE', 
                    'user_id' => auth()->user()->id,
                    'resize' => [500, 3000],
                    'small_path' => 'small/',
                    'visibility' => 'PUBLIC',
                    'file_system_type' => env('FILESYSTEM_DRIVER'),
                    'compress' => 'auto'
                ])['filename']; 
                
                $this->use_hub_file($file, $article->id, auth()->user()->id);
                $article->update(['image' => $file]);
            }
            
            // Clear articles cache
            Cache::forget('articles_list');
            
            Log::info('Article updated', ['article_id' => $article->id]);
            
            emotify('success', 'تمت العملية بنجاح');
        } catch (\Exception $e) {
            Log::error('Article update failed', [
                'error' => $e->getMessage(),
                'article_id' => $article->id,
            ]);
            emotify('error', 'حدث خطأ أثناء تحديث المقال');
        }
        
        return redirect()->route('admin.articles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Article  $article
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        try {
            $articleId = $article->id;
            
            // Remove associated image
            if ($article->image) {
                $this->remove_hub_file($article->image);
            }
            
            $article->delete();
            
            // Clear articles cache
            Cache::forget('articles_list');
            
            Log::info('Article deleted', ['article_id' => $articleId]);
            
            emotify('success', 'تمت العملية بنجاح');
        } catch (\Exception $e) {
            Log::error('Article deletion failed', [
                'error' => $e->getMessage(),
                'article_id' => $article->id,
            ]);
            emotify('error', 'حدث خطأ أثناء حذف المقال');
        }
        
        return redirect()->route('admin.articles.index');
    }
}
