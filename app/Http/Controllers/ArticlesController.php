<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Request;


class ArticlesController extends Controller
{
    public function index()
    {
        $categories = Category::all(); // Récupérez toutes les catégories depuis la base de données
        $articles = Article::all();
        // $articles->each(function ($article) {
        //     $article->couleurs = json_decode($article->couleurs);
        // });

        return view('administration.pages.articles.index', compact('articles', 'categories'));
    }

    public function create()
    {
        $categories = Category::all(); // Récupérez toutes les catégories depuis la base de données
        return view('administration.pages.articles.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'categorie_id' => 'required|exists:categories,id',
            'nom' => 'required|string|max:255',
            'surnoms' => 'nullable|string',
            'prix' => 'required|numeric',
            'taille_format' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,png,jpeg|max:2048',
            'couleurs' => 'nullable|array',
            'second_mains' => 'required|boolean'

        ]);

        // Enregistrement de l'article
        $article = new Article();
        $article->categorie_id = $request->categorie_id;
        $article->nom = $request->nom;
        $article->surnoms = $request->surnoms;
        $article->prix = $request->prix;
        $article->taille_format = $request->taille_format;
        $article->description = $request->description;
        $article->second_mains = $request->second_mains;


        // Enregistrement des couleurs choisies
        // $article->couleurs = $request->couleurs ?? [];

        // Gestion des images
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/articles'), $fileName);
                $images[] = $fileName;
            }
            $article->images = $images;
        }

        $article->save();

        return redirect()->route('articles.create')->with('success', 'Article ajouté avec succès.');
    }

    public function edit($id)
    {
        // Récupérez l'article depuis la base de données
        $article = Article::findOrFail($id);
        

        // Récupérez toutes les catégories pour le formulaire de sélection
        $categories = Category::all();

        // Retournez la vue d'édition avec les données nécessaires
        return view('administration.pages.articles.edit', compact('article', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'categorie_id' => 'required|exists:categories,id',
            'nom' => 'required|string|max:255',
            'surnoms' => 'nullable|string',
            'prix' => 'required|numeric',
            // 'couleurs' => 'nullable|array',
            'taille_format' => 'nullable|string|max:255',
            // 'en_stock' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'second_mains' => 'required|boolean'

        ]);

        // Récupérez l'article à mettre à jour
        $article = Article::findOrFail($id);

        // Mettez à jour les champs de l'article avec les données du formulaire
        $article->categorie_id = $request->categorie_id;
        $article->nom = $request->nom;
        $article->surnoms = $request->surnoms;
        $article->prix = $request->prix;
        // $article->couleurs = json_encode($request->couleurs);
        $article->taille_format = $request->taille_format;
        // $article->en_stock = $request->en_stock;
        $article->description = $request->description;
        $article->second_mains = $request->has('second_mains') ? true : false; // Assurez-vous que la valeur est booléenne

        // Traitement des images
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $fileName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('images/articles'), $fileName);
                $imagePaths[] = $fileName;
            }
            $article->images = json_encode($imagePaths);
        }

        // Enregistrez les modifications dans la base de données
        $article->save();

        // Redirigez avec un message de succès
        return redirect()->route('articles.index')->with('success', 'Article mis à jour avec succès.');
    }

    public function destroy($id)
    {
        // Récupérez l'article à supprimer
        $article = Article::findOrFail($id);

        // Supprimez l'article de la base de données
        $article->delete();

        // Redirigez avec un message de succès
        return redirect()->route('articles.index')->with('success', 'Article supprimé avec succès.');
    }
}
