<?php
// app/Http/Controllers/ImageController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;

class ImageController extends Controller
{
    public function index()
    {
        return view('image-upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = time().'.'.$request->image->extension();  
        $request->image->move(public_path('images'), $imageName);

        // Save image path to the database
        $image = new Image();
        $image->image_path = $imageName;
        $image->save();

        return back()
            ->with('success','You have successfully uploaded an image.')
            ->with('image', $imageName);
    }
}

?>