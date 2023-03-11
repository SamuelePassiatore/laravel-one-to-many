<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $types = Type::all();
        return view('admin.types.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $type = new Type();
        return view('admin.types.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|unique:types|max:20',
            'color' => 'nullable|string|size:7',
        ], [
            'lable.required' => "A type must have at least one label",
            'label.max' => "Type has a maximum of :max characters",
            'label.unique' => "Already exists a type with this name",
            'color.size' => "The color must be a hexadecimal code with a hash mark",
        ]);

        $data = $request->all();

        $type = new Type();

        $type->fill($data);

        $type->save();

        return to_route('admin.types.show', $type->id)
            ->with('message', "'$type->title' category has been successfully created")
            ->with('type', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Type $type)
    {
        return view('admin.types.show', compact('type'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Type $type)
    {
        return view('admin.types.edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Type $type)
    {
        $request->validate([
            'label' => ['required', 'string', Rule::unique('types')->ignore($type->id), 'max:20'],
            'color' => 'nullable|string|size:7',
        ], [
            'lable.required' => "A type must have at least one label",
            'label.max' => "Type has a maximum of :max characters",
            'label.unique' => "Already exists a type with this name",
            'color.size' => "The color must be a hexadecimal code with a hash mark",
        ]);

        $data = $request->all();

        $type->update($data);

        return to_route('admin.types.show', $type->id)
            ->with('message', "'$type->title' category has been successfully modified")
            ->with('type', 'success');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Type $type)
    {
        $type->delete();
        return to_route('admin.types.index')
            ->with('message', "'$type->title' type has been successfully deleted")
            ->with('type', 'success');
    }
}
