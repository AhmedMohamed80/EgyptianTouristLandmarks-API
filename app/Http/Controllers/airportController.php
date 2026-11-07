<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\Airport;
use App\Models\Review;
use App\models\facilities_airports;
use App\models\Airlines;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;

class AirportController extends Controller
{
    public function display_all_item()
    {   
        try{
            $airports = Airport::with(['facilities_airports','Airlines','item.photos'])
            ->withAvg('reviews', 'rating_value')
            ->withCount('reviews')
            ->paginate(10);

            return response()->json($airports);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        } 
    }

    public function store_new_item(Request $request)
    {   
        try {
            $request->validate([
                'name'               => 'required|string|max:255',
                'description'        => 'nullable|string',
                'city'               => 'required|string',
                'location'           => 'required|string',
                'image'              => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'website_link'       => 'nullable|url',
                'phone'              => 'nullable|string',
                'photos.*'           => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
                'facilitie.*'        =>'nullable|string',
                'airlines_name.*'             => 'required|string|max:255',
                'airlines_terminal.*'         => 'nullable|string|max:255',
                'airlines_email.*'            => 'nullable|email|max:255',
                'airlines_phone.*'            => 'nullable|string|max:20',
                'airlines_website_link.*'     => 'nullable|url|max:255',
            ]);

            $item = new Item();
            $item->name = $request->name;
            $item->description = $request->description;
            $item->city = $request->city;
            $item->location = $request->location;
            if ($request->hasFile('image')) {
                $main = $request->file('image');
                $file_extension = $main->getClientOriginalExtension();
                $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                $path = public_path('image/Airport');
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $main->move($path, $file_name);
                $item->image_path = asset('image/Airport/' . $file_name);
            }
            $item->save();

            $airport = new Airport();
            $airport->Item_ID = $item->Item_ID;
            $airport->website_link = $request->website_link;
            $airport->phone = $request->phone;
            $airport->save();

            if ($request->has('facilitie')) {
                foreach ($request->facilitie as $facilityName) {
                    $facility = new facilities_airports();
                    $facility->airport_id = $airport->id;
                    $facility->facility_name = $facilityName;
                    $facility->save();
                }
            }

            if ($request->has('airlines_name')) {
                $airlineCount = count($request->airlines_name);
                for ($i = 0; $i < $airlineCount; $i++) {
                    Airlines::create([
                        'airport_id'   => $airport->id,
                        'name'         => $request->airlines_name[$i],
                        'terminal'     => $request->airlines_terminal[$i] ?? null,
                        'email'        => $request->airlines_email[$i] ?? null,
                        'phone'        => $request->airlines_phone[$i] ?? null,
                        'website_link' => $request->airlines_website_link[$i] ?? null,
                    ]);
                }
            }

            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $file_extension = $photo->getClientOriginalExtension();
                    $file_name = time() . rand(1000, 9999) . '.' . $file_extension;
                    
                    $path = public_path('image/Airport/photos');
                    
                    $photo->move($path, $file_name);
            
                    Photo::create([
                        'Item_ID' => $item->Item_ID,
                        'image_path' => asset("image/Airport/photos/$file_name"),
                    ]);
                }
            }
           
           
            $item->load([ 'airport','photos','airport.facilities_airports','airport.airlines']);

            return response()->json([
                'message' => 'airport created successfully',
                'item' => $item->load([ 'airport','photos','airport.facilities_airports','airport.airlines'])
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }    
    }

    public function show_specific_item($id)
    {
        try {
            $airport = Airport::with('facilities_airports','airlines','item.photos')->find($id);
    
            if (!$airport) {
                return response()->json(['message' => 'Airport not found'], 404);
            }
    
            if (!$airport->item) {
                return response()->json(['message' => 'Item not found'], 404);
            }
    
            $reviews = Review::with('user')->where('Item_ID', $airport->item->Item_ID)->get();
            $avgRating = $reviews->avg('rating_value'); 
            $totalReviews = $reviews->count(); 
    
            $airport->item->reviews = $reviews;
            $airport->item->average_rating = $avgRating;
            $airport->item->total_reviews = $totalReviews;
    
            return response()->json([
                'message' => 'successful',
                'airport' => $airport
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed: ' . $e->getMessage()], 500);
        }
    }
    

    public function update_an_item(Request $request, $airport_id)
    {
        try{
            $airport = Airport::where('id', $airport_id)->first();
        
            if (!$airport) {
                return response()->json(['message' => 'faild'], 404);
            }
        
            $item = Item::where('Item_ID', $airport->Item_ID)->first();
        
            if (!$item) {
                return response()->json(['message' => 'faild'], 404);
            }
        
            $item->update($request->only(['name', 'description', 'city', 'location']));
        
            if ($request->hasFile('image')) {
                if ($item->image_path) {
                    Storage::disk('public')->delete($item->image_path);
                }
                $imagePath = $request->file('image')->store('items', 'public');
                $item->image_path = $imagePath;
                $item->save();
            }

            if ($request->has('facilitie')) {
                $airport->facilities_airports()->delete();
                foreach ($request->facilitie as $facilitie) {
                    facilities_airports::create([
                        'airport_id' => $airport->id,
                        'facility_name' => $facilitie
                    ]);
                }
            }
        
            $airport->update([
                'website_link' => $request->has('website_link') ? $request->website_link : $airport->website_link,
                'phone' => $request->has('phone') ? $request->phone : $airport->phone,
            ]);

            $updated_item = Item::with(['airport', 'photos'])->where('Item_ID', $airport->Item_ID)->first();
        
            $airlines = Airlines::where('airport_id', $airport->id)->get()->values(); 
            foreach ($airlines as $index => $airline) {
                if ($request->has("airlines_name.$index")) {
                    $airline->name = $request->input("airlines_name.$index");
                }
                if ($request->has("airlines_terminal.$index")) {
                    $airline->terminal = $request->input("airlines_terminal.$index");
                }
                if ($request->has("airlines_email.$index")) {
                    $airline->email = $request->input("airlines_email.$index");
                }
                if ($request->has("airlines_phone.$index")) {
                    $airline->phone = $request->input("airlines_phone.$index");
                }
                if ($request->has("airlines_website_link.$index")) {
                    $airline->website_link = $request->input("airlines_website_link.$index");
                }
            
                $airline->save();
            } 
            
            return response()->json([
                'message' => 'airport updated successfully',
                'data' => Airport::with('airlines','facilities_airports','item.photos')->find($airport->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'faild :' . $e->getMessage()], 500);
        }
    }

    public function delete_an_item($id)
    {
        try {
            $airport = Airport::find($id);
            if (!$airport) {
                return response()->json(['message' => 'failed: airport not found'], 404);
            }
            $item = $airport->item; 
            if (!$item) {
                return response()->json(['message' => 'failed: Item not found'], 404);
            }
            foreach ($item->photos as $photo) {
                Storage::disk('public')->delete($photo->image_path);
                $photo->delete();
            }
            $airport->airlines()->delete();
            $airport->facilities_airports()->delete();
            $airport->delete();
            $item->delete();
            return response()->json(['message' => 'Delete successful']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'faild :' . $e->getMessage()], 500);
        }
    }
}
