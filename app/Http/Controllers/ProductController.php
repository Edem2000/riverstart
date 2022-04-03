<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $productsQuery = Product::query();
        if($request->has('name')){
            $name = '%' . $request['name'] . '%';
            $productsQuery->where('name', 'like', $name);
        }
        if($request->has('published')){
            $productsQuery->where('published', '=', intval($request['published']));
        }
        if($request->has('deleted')){
            $productsQuery->where('deleted', '=', intval($request['deleted']));
        }
        if($request->filled('min_price') && $request['min_price']!=''){
            $productsQuery->where('price', '>=', $request['min_price']);
        }
        if($request->filled('max_price') && $request['max_price']!=''){
            $productsQuery->where('price', '<=', $request['max_price']);
        }
        if($request->has('category')){
            $category_id = $request['category'];
            $productsQuery->whereHas('categories', function (Builder $query)  use($category_id) {
                $query->where('id', '=', $category_id);
            });
        }
        if($request->has('category_name')){
            $category_name = '%' . $request['category_name'] . '%';
            $productsQuery->whereHas('categories', function (Builder $query)  use($category_name) {
                $query->where('name', 'like', $category_name);
            });
        }
        $products = $productsQuery->get();
//        $json = $products->toJson();
//        dd($products);
        return response()->json($products, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = json_decode($request->getContent());
        $categories = $data->categories;
        if(count($categories) < 2 || count($categories) > 10){
            return response()->json('Not allowed categories quantity', 400);
        }
        if($data->name && $data->description && $data->price) {
            $product = Product::create([
                'name' => $data->name,
                'description' => $data->description,
                'price' => $data->price,
            ]);
            foreach ($categories as $category_id) {
                $category = Category::find($category_id);
                $product->categories()->attach($category);
            }
            return response()->json('Successfully created', 201);
        }
        else{
            return response()->json('Not enough data', 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $data = json_decode($request->getContent());
        if($data->categories){
            $categories = $data->categories;
            foreach ($categories as $category_id) {
                $category = Category::find($category_id);
                $product->categories()->attach($category);
            }
        }
        $product->update($request->all());
//        if($data->name){
//            $product->name = $data->name;
//        }
//        if($data->description){
//            $product->description = $data->description;
//        }
//        if($data->price){
//            $product->price = $data->price;
//        }
        $product->save();
        return response()->json('Successfully edited', 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        $product->deleted = 1;
        $product->save();
        return response()->json('Successfully deleted', 200);
    }
}
