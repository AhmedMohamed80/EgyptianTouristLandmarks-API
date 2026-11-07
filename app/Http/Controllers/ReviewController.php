<?php

namespace App\Http\Controllers;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Review;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function Display_all_Review()
    {
        return response()->json(Review::with(['user', 'item'])->get());
    }

    public function store_new_review(Request $request)
    {
        try{
            $request->validate([
                'id' => 'required|exists:users,id',
                'Item_ID' => 'required|exists:items,Item_ID',
                'date' => 'required|date',
                'rating_value' => 'required|integer|min:1|max:5',
            ]);

            $review = Review::create($request->all());
            $item = Item::where('Item_ID', $review->Item_ID)->first();


            return response()->json($review, 201);
        }catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
    public function show_specific_Review($id)
    {
        try {
            $review = Review::with(['user', 'item'])->where('Rev_ID', $id)->first();
    
            if (!$review) {
                return response()->json(['error' => 'التقييم غير موجود'], 404);
            }
    
            return response()->json($review);
        } catch (\Exception $e) {
            return response()->json(['error' => 'فشل الطلب: ' . $e->getMessage()], 500);
        }
    }
      
    public function update_an_Review(Request $request, $id)
    {
        $review = Review::where('Rev_ID', $id)->first();

        if (!$review) {
            return response()->json(['faild' => 'review not found'], 404);
        }
    
        $review->update([
            'content' => $request->has('content') ? $request->content : $review->content,
            'rating_value' => $request->has('rating_value') ? $request->rating_value : $review->rating_value,
        ]);
        return response()->json($review);
    }
    

    public function Delete_an_Review($id)
    {
        Review::destroy($id);
        return response()->json(['message' => 'Review deleted successfully']);
    }





    public function topRatedItems()
    {
        $topItems = DB::table('items')
        ->join('reviews', 'items.Item_ID', '=', 'reviews.Item_ID') // Use 'items.id' instead of 'items.Item_ID'
        ->select('items.Item_ID', 'items.name', DB::raw('AVG(reviews.rating_value) as avg_rating'))
        ->groupBy('items.Item_ID', 'items.name')
        ->orderByDesc('avg_rating')
        ->get();

    return response()->json($topItems);
    }

}

