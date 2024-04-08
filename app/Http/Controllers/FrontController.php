<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\Product;

class FrontController extends Controller
{
    //

    public function index()
    {
        $products= Product::with('category')->orderBy('id','desc')->take(6)->get();
        $categories = Category::all();
        return view("front.index", [
            'products'=>$products,
            'categories'=> $categories,
        ]);
    }

    public function details(Product $product)
    {
        return view('front.details',[
            'product'=>$product,
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $request->input('keyword');
        $products = Product::where('name', 'ILIKE', '%'.$keyword.'%')->get();

        return view('front.search', [
            'keyword'=>$keyword,
            'products'=>$products,
        ]);
    }

    public function category(Category $category)
    {
        $products = Product::where('category_id', $category->id)->with('category')->get();
        return view('front.category', [
            'products'=>$products,
            'category'=> $category,
        ]);
    }
}
