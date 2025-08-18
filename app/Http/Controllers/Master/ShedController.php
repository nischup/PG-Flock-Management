<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Shed;

class ShedController extends Controller
{
    public function index()
    {
        $sheds = Shed::orderBy('id', 'desc')->get();

        return Inertia::render('library/shed/List', [
            'sheds' => $sheds,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        Shed::create($request->all());

        return redirect()->route('shed.index')->with('success', 'Shed deleted successfully!');
    }

    public function update(Request $request, Shed $shed)
    {
        $request->validate([
            'name'   => 'required|string|max:200',
            'status' => 'required|in:0,1',
        ]);

        $shed->update($request->all());

       return redirect()->route('shed.index')->with('success', 'Shed deleted successfully!');
    }

    public function destroy(Shed $shed)
    {
        $shed->delete();

        return redirect()->route('shed.index')->with('success', 'Shed deleted successfully!');
    }
}
