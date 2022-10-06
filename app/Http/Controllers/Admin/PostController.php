<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\Category;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::orderBy('created_at', 'DESC')->orderBy('created_at')->get();
        return view('admin.posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $post = new Post();
        $categories = Category::select('id', 'label')->get();
        return view('admin.posts.create', compact('post', 'categories'));
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
            'title' => 'required|string|min:3|unique:posts',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes: jpeg,jpg,png',
            'category_id' => 'nullable|exists:categories,id'
        ],
        [
            'title.required' => 'Il titolo é obbligatorio',
            'title.min' => 'Il titolo deve essere lungo almeno 5 caratteri',
            'title.unique' => "Esiste già un post con questo titolo",
            'category_id.exists' => 'Categoria inesistente',
            'image.image' => 'Il file selezionato non é di tipo immagine',
            'image.mimes' => 'Sono supprtati solo file immagine jpeg,jpg,png'
        ]);

        $data = $request->all();

        $post = new Post();
        $post->fill($data);
        
        $post->user_id = Auth::id();

         // Controllo se cè la chiave image nei data
         if(array_key_exists('image', $data)){
            // la inserisco nella cartella che voglio che andrà nel percorso 'storage/app/public/(post_img in questo caso)'e la trasformo in una variabile
           $image_url = Storage::put('post_img', $data['image']);
        //    assegno la varibile alla immagine del post
           $post->image = $image_url;
        }

        $post->save();
        
       

        return redirect()->route('admin.posts.show', $post)
        ->with('message', 'Il post è stato creato con successo')
        ->with('type', 'success');

    }

    /**
     * Display the specified resource.
     *
     * @param  Post $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Post $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        $categories = Category::select('id', 'label')->get();
        return view('admin.posts.edit', compact('post', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Post $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $data = $request->all();
        
        // Con la funzione update,sottobanco sta facendo fill and save insieme
        $post->update($data);

        return redirect()->route('admin.posts.show', $post)
        ->with('message', 'Il post è stato modificato con successo')
        ->with('type', 'success');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
        ->with('message', 'Il post è stato eliminato')
        ->with('type', 'danger');
    }
}
