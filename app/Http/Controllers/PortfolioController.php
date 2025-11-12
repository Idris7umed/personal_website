<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Portfolio;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
class PortfolioController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $portfolios = Portfolio::query()
            ->when($request->key, function($q) use ($request) {
                $key = $request->key;
                $q->where('title', 'LIKE', "%{$key}%")
                  ->orWhere('description', 'LIKE', "%{$key}%");
            })
            ->with('user:id,name')
            ->orderBy('id', 'DESC')
            ->paginate(15);
            
        return view('admin.portfolios.index', compact('portfolios'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('admin.portfolios.create');
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
        ]);

        try {
            $portfolio = Portfolio::create([
                'user_id' => auth()->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'link' => $request->link,
                'slug' => uniqid()
            ]);
            
            $portfolio->update([
                'slug' => $this->generate_slug($portfolio->id . '-' . $portfolio->title),
            ]);
            
            // Clear portfolios cache
            Cache::forget('portfolios_list');
            
            Log::info('Portfolio created', ['portfolio_id' => $portfolio->id]); 

            $uploaded_files = json_decode($request["fileuploader-list-file-main"]);
            $attachments = [];
            foreach ($uploaded_files as $uploaded_file) {
                array_push($attachments, $uploaded_file->file);
            }
            if (isset($attachments[0])) {
                $this->use_hub_file($attachments[0], $portfolio->id, auth()->user()->id, 1);
            }

            $uploaded_files = json_decode($request["fileuploader-list-file"]);
            $attachments = [];
            foreach ($uploaded_files as $uploaded_file) {
                array_push($attachments, $uploaded_file->file);
            }
            foreach ($attachments as $attachment) {
                $this->use_hub_file($attachment, $portfolio->id, auth()->user()->id);
            }
            
            emotify('success', 'تمت العملية بنجاح');
        } catch (\Exception $e) {
            Log::error('Portfolio creation failed', ['error' => $e->getMessage()]);
            emotify('error', 'حدث خطأ أثناء إنشاء المشروع');
        }
        
        return redirect()->route('admin.portfolios.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Portfolio $portfolio)
    {
        return 0;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Portfolio $portfolio)
    {
        return view('admin.portfolios.edit',compact('portfolio'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Portfolio $portfolio)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        try {
            $portfolio->update([
                'user_id' => auth()->user()->id,
                'title' => $request->title,
                'description' => $request->description,
                'link' => $request->link 
            ]); 

            $uploaded_files = json_decode($request["fileuploader-list-file-main"]);
            $attachments = [];
            foreach ($uploaded_files as $uploaded_file) {
                array_push($attachments, $uploaded_file->file);
            }
            if (isset($attachments[0])) {
                $this->use_hub_file($attachments[0], $portfolio->id, auth()->user()->id, 1);
            }

            $uploaded_files = json_decode($request["fileuploader-list-file"]);
            $attachments = [];
            foreach ($uploaded_files as $uploaded_file) {
                array_push($attachments, $uploaded_file->file);
            }
            foreach ($attachments as $attachment) {
                $this->use_hub_file($attachment, $portfolio->id, auth()->user()->id);
            }
            
            // Clear portfolios cache
            Cache::forget('portfolios_list');
            
            Log::info('Portfolio updated', ['portfolio_id' => $portfolio->id]);
            
            emotify('success', 'تمت العملية بنجاح');
        } catch (\Exception $e) {
            Log::error('Portfolio update failed', [
                'error' => $e->getMessage(),
                'portfolio_id' => $portfolio->id,
            ]);
            emotify('error', 'حدث خطأ أثناء تحديث المشروع');
        }
        
        return redirect()->route('admin.portfolios.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Portfolio $portfolio)
    {
        try {
            $portfolioId = $portfolio->id;
            $portfolio->delete();
            
            // Clear portfolios cache
            Cache::forget('portfolios_list');
            
            Log::info('Portfolio deleted', ['portfolio_id' => $portfolioId]);
            
            emotify('success', 'تمت العملية بنجاح');
        } catch (\Exception $e) {
            Log::error('Portfolio deletion failed', [
                'error' => $e->getMessage(),
                'portfolio_id' => $portfolio->id,
            ]);
            emotify('error', 'حدث خطأ أثناء حذف المشروع');
        }
        
        return redirect()->route('admin.portfolios.index');
    }


    public function image_store(Request $request)
    {
        return $this->store_file([
            'source'=>$request->file,
            'validation'=>"image",
            'path_to_save'=>'/uploads/portfolios/',
            'type'=>'PORTFOLIO', 
            'user_id'=>\Auth::user()->id,
            'resize'=>[500,3000],
            'small_path'=>'small/',
            'visibility'=>'PUBLIC',
            'file_system_type'=>env('FILESYSTEM_DRIVER'),
            /*'watermark'=>true,*/
            'compress'=>'auto'
        ]);  
    }
    public function image_delete(Request $request)
    {
        return $this->remove_hub_file($request->name);
    }

}
