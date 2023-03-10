<?php

namespace App\Http\Controllers\Admin;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter');
        $search = $request->query('search');

        $selected = $filter ? $filter : 'all';

        $query = Project::orderBy('updated_at', 'DESC');

        if ($filter) {
            $value = $filter === 'private' ? 0 : 1;
            $query->where('is_public', $value);
        }

        if ($search) {
            $query->where('title', 'LIKE', "%$search%");
        }

        $projects = $query->paginate(10);
        return view('admin.projects.index', compact('projects', 'selected', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $project = new Project();
        $types = Type::orderBy('label')->get();
        return view('admin.projects.create', compact('project', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|unique:projects',
            'image' => 'nullable|image',
            'description' => 'string',
            'url' => 'nullable|url|unique:projects',
            'type_id' => 'nullable|exists:types_id'
        ], [
            'title.unique' => "The title '$request->title' has already been taken.",
            'type_id' => "Type of project not valid"
        ]);

        $data = $request->all();

        $project = new Project();

        $data['slug'] = Str::slug($data['title']);

        if (Arr::exists($data, 'image')) {
            $img_url = Storage::put('projects', $data['image']);
            $data['image'] = $img_url;
        }

        $project->fill($data);

        // $project->slug = Str::slug($project->title, '-');

        $project->is_public = Arr::exists($data, 'is_public');

        $project->save();

        return to_route('admin.projects.show', $project->id)
            ->with('message', "'$project->title' project has been successfully created")
            ->with('type', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('admin.projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $types = Type::orderBy('label')->get();
        return view('admin.projects.edit', compact('project', 'types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'title' => ['required', 'string', Rule::unique('projects')->ignore($project->id)],
            'image' => 'nullable|image',
            'description' => 'string',
            'url' => ['nullable', 'url', Rule::unique('projects')->ignore($project->id)],
            'type_id' => 'nullable|exists:types_id'
        ], [
            'title.unique' => "The title '$request->title' has already been taken.",
            'type_id' => "Type of project not valid"
        ]);

        $data = $request->all();
        $data['slug'] = Str::slug($data['title']);

        if (Arr::exists($data, 'image')) {
            if ($project->image) Storage::delete($project->image);
            $img_url = Storage::put('projects', $data['image']);
            $data['image'] = $img_url;
        }

        $data['is_public'] = Arr::exists($data, 'is_public');

        $project->update($data);

        return to_route('admin.projects.show', $project->id)
            ->with('type', 'success')
            ->with('message', "'$project->title' project has been successfully modified");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return to_route('admin.projects.index')
            ->with('message', "'$project->title' project has been successfully deleted")
            ->with('type', 'success');
    }

    /**
     * Display a listing of the trashed resource.
     */
    public function trash()
    {
        $projects = Project::onlyTrashed()->paginate(10);
        return view('admin.projects.trash.index', compact('projects'));
    }

    /**
     * Restores a single resource from trash to active records.
     */
    public function restore(int $id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);

        $project->restore();

        return to_route('admin.projects.index')->with('message', "'$project->title' has been successfully restored.")->with('type', 'success');
    }

    /**
     * Permanently remove the specified resource from storage.
     */
    public function drop(int $id)
    {
        $project = Project::onlyTrashed()->findOrFail($id);
        if ($project->image) Storage::delete($project->image);
        $project->forceDelete();

        return to_route('admin.projects.trash.index')
            ->with('message', "'$project->title' has been deleted permanently")
            ->with('type', 'success');
    }

    public function dropAll()
    {

        $num_projects = Project::onlyTrashed()->count();


        Project::onlyTrashed()->forceDelete();


        return to_route('admin.projects.trash.index')
            ->with('message', "$num_projects projects successfully removed")
            ->with('type', 'success');
    }

    public function toggle(Project $project)
    {
        $project->is_public = !$project->is_public;
        $action = $project->is_public ? 'published' : 'drafted';
        $type = $project->is_public ? 'success' : 'info';
        $project->save();
        return redirect()->back()
            ->with('message', "'$project->title' project has been successfully $action")
            ->with('type', $type);
    }
}
