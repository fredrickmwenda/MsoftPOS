<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Category;
use App\Models\CategoryDepartment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Traits\TenantInfo;
use App\Traits\CacheForget;
use Intervention\Image\Facades\Image;

class CategoryController extends Controller
{
    use CacheForget;
    use TenantInfo;

    /*
    |----------------------------------------------------------------------
    | HARD-DELETE SETTINGS
    |----------------------------------------------------------------------
    | $deleteProductsWithCategory
    |   true  => products under a deleted category are PERMANENTLY deleted
    |   false => products are detached (category_id = NULL) and kept
    |
    | $detachCategoriesOnDepartmentDelete
    |   true  => categories are detached from the department, then deleted
    |   false => deletion is blocked while categories are attached
    */
    protected $deleteProductsWithCategory = false;
    protected $detachCategoriesOnDepartmentDelete = true;

    /* ==================================================================
     |  CATEGORIES
     ================================================================== */

    public function index()
    {
        if (Auth::user()->hasPermissionTo('category')) {
            $lims_category_list   = DB::table('categories')->where('is_active', true)->orderBy('name')->get();
            $lims_department_list = DB::table('category_departments')->where('is_active', true)->orderBy('name')->get();

            return view('backend.category.create', compact('lims_category_list', 'lims_department_list'));
        }

        return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    public function categoryData(Request $request)
    {
        // Only real DB columns are sortable
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'department_id',
        ];

        $totalData     = Category::where('is_active', true)->count();
        $totalFiltered = $totalData;

        $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
        $start = $request->input('start', 0);

        $order = $columns[(int) $request->input('order.0.column', 1)] ?? 'name';
        $dir   = $request->input('order.0.dir', 'asc');

        $query = Category::with('department')->where('is_active', true);

        if (!empty($request->input('search.value'))) {
            $search = $request->input('search.value');
            $query->where('name', 'LIKE', "%{$search}%");
            $totalFiltered = Category::where('name', 'LIKE', "%{$search}%")
                ->where('is_active', true)->count();
        }

        $categories = $query->offset($start)->limit($limit)->orderBy($order, $dir)->get();

        $data = [];
        foreach ($categories as $key => $category) {
            $nestedData['id']  = $category->id;
            $nestedData['key'] = $key;

            if ($category->image)
                $nestedData['name'] = '<img src="' . url('images/category', $category->image) . '" height="80" width="80"> ' . $category->name;
            else
                $nestedData['name'] = '<img src="' . url('images/zummXD2dvAtI.png') . '" height="80" width="80"> ' . $category->name;

            $nestedData['department'] = $category->department ? $category->department->name : 'N/A';

            $nestedData['number_of_product'] = $category->product()->where('is_active', true)->count();
            $nestedData['stock_qty']         = $category->product()->where('is_active', true)->sum('qty');

            $total_price = $category->product()->where('is_active', true)->sum(DB::raw('price * qty'));
            $total_cost  = $category->product()->where('is_active', true)->sum(DB::raw('cost * qty'));

            if (config('currency_position') == 'prefix')
                $nestedData['stock_worth'] = config('currency') . ' ' . $total_price . ' / ' . config('currency') . ' ' . $total_cost;
            else
                $nestedData['stock_worth'] = $total_price . ' ' . config('currency') . ' / ' . $total_cost . ' ' . config('currency');

            $nestedData['options'] = '<div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                          <span class="caret"></span>
                          <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                            <li>
                                <button type="button" data-id="' . $category->id . '" class="open-EditCategoryDialog btn btn-link" data-toggle="modal" data-target="#editModal"><i class="dripicons-document-edit"></i> ' . trans("file.edit") . '</button>
                            </li>
                            <li class="divider"></li>' .
                \Form::open(["route" => ["category.destroy", $category->id], "method" => "DELETE"]) . '
                            <li>
                              <button type="submit" class="btn btn-link" onclick="return confirmHardDelete()"><i class="dripicons-trash"></i> ' . trans("file.delete") . '</button>
                            </li>' . \Form::close() . '
                        </ul>
                    </div>';

            $data[] = $nestedData;
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    public function store(Request $request)
    {
        $request->name = preg_replace('/\s+/', ' ', $request->name);

        $this->validate($request, [
            'name'  => [
                'max:255',
                Rule::unique('categories')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'image' => 'image|mimes:jpg,jpeg,png,gif',
            'icon'  => 'mimetypes:text/plain,image/png,image/jpeg,image/svg',
        ]);

        $image = $request->image;
        if ($image) {
            $ext       = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            } else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }
            $image->move('public/images/category', $imageName);
            Image::make('public/images/category/' . $imageName)->fit(300, 300)->save();
            $lims_category_data['image'] = $imageName;
        }

        $icon = $request->icon;
        if ($icon) {
            if (!file_exists('public/images/category/icons/')) {
                mkdir('public/images/category/icons/', 0755, true);
            }
            $ext      = pathinfo($icon->getClientOriginalName(), PATHINFO_EXTENSION);
            $iconName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $iconName = $iconName . '.' . $ext;
            } else {
                $iconName = $this->getTenantId() . '_' . $iconName . '.' . $ext;
            }
            $icon->move('public/images/category/icons/', $iconName);
            Image::make('public/images/category/icons/' . $iconName)->fit(100, 100)->save(); // FIXED path (was missing icons/)
            $lims_category_data['icon'] = $iconName;
        }

        $lims_category_data['name']          = $request->name;
        $lims_category_data['parent_id']     = $request->parent_id ?: null;
        $lims_category_data['department_id'] = $request->department_id ?: null;
        $lims_category_data['is_active']     = true;

        if (isset($request->is_sync_disable))
            $lims_category_data['is_sync_disable'] = $request->is_sync_disable;

        if (isset($request->slug)) {
            $lims_category_data['slug']              = Str::slug($request->name, '-');
            $lims_category_data['featured']          = $request->featured;
            $lims_category_data['page_title']        = $request->page_title;
            $lims_category_data['short_description'] = $request->short_description;
        }

        DB::table('categories')->insert($lims_category_data);
        $this->cacheForget('category_list');

        return redirect('category')->with('message', 'Category inserted successfully');
    }

    public function edit($id)
    {
        $lims_category_data = DB::table('categories')->where('id', $id)->first();

        if ($lims_category_data && $lims_category_data->parent_id) {
            $lims_parent_data = DB::table('categories')->where('id', $lims_category_data->parent_id)->first();
            $lims_category_data->parent = $lims_parent_data ? $lims_parent_data->name : '';
        }

        return response()->json($lims_category_data);
    }

    public function update(Request $request)
    {
        if (!env('USER_VERIFIED'))
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');

        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('categories')->ignore($request->category_id)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'image' => 'image|mimes:jpg,jpeg,png,gif',
            'icon'  => 'mimetypes:text/plain,image/png,image/jpeg,image/svg',
        ]);

        $lims_category_data = DB::table('categories')->where('id', $request->category_id)->first();

        $input = $request->except('image', 'icon', '_method', '_token', 'category_id');
        $input['department_id'] = $request->department_id ?? null;
        $input['parent_id']     = $request->parent_id ?? null;

        $image = $request->image;
        if ($image) {
            $this->fileDelete('images/category/', $lims_category_data->image);

            $ext       = pathinfo($image->getClientOriginalName(), PATHINFO_EXTENSION);
            $imageName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName = $imageName . '.' . $ext;
            } else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }
            $image->move('public/images/category', $imageName);
            Image::make('public/images/category/' . $imageName)->fit(100, 100)->save();
            $input['image'] = $imageName;
        }

        $icon = $request->icon;
        if ($icon) {
            if (!file_exists('public/images/category/icons/')) {
                mkdir('public/images/category/icons/', 0755, true);
            }
            $this->fileDelete('images/category/icons/', $lims_category_data->icon);

            $ext      = pathinfo($icon->getClientOriginalName(), PATHINFO_EXTENSION);
            $iconName = date("Ymdhis");
            if (!config('database.connections.saleprosaas_landlord')) {
                $iconName = $iconName . '.' . $ext;
            } else {
                $iconName = $this->getTenantId() . '_' . $iconName . '.' . $ext;
            }
            $icon->move('public/images/category/icons/', $iconName);
            Image::make('public/images/category/icons/' . $iconName)->fit(100, 100)->save();
            $input['icon'] = $iconName;
        }

        if (!isset($request->featured) && \Schema::hasColumn('categories', 'featured')) {
            $input['featured'] = 0;
        }
        if (!isset($input['is_sync_disable']) && \Schema::hasColumn('categories', 'is_sync_disable'))
            $input['is_sync_disable'] = null;

        DB::table('categories')->where('id', $request->category_id)->update($input);

        return redirect('category')->with('message', 'Category updated successfully');
    }

    public function import(Request $request)
    {
        $upload = $request->file('file');
        $ext = pathinfo($upload->getClientOriginalName(), PATHINFO_EXTENSION);
        if ($ext != 'csv')
            return redirect()->back()->with('not_permitted', 'Please upload a CSV file');

        $filename = $upload->getClientOriginalName();
        $filePath = $upload->getRealPath();
        $file = fopen($filePath, 'r');
        $header = fgetcsv($file);
        $escapedHeader = [];
        foreach ($header as $key => $value) {
            $lheader = strtolower($value);
            $escapedItem = preg_replace('/[^a-z]/', '', $lheader);
            array_push($escapedHeader, $escapedItem);
        }
        while ($columns = fgetcsv($file)) {
            if ($columns[0] == "")
                continue;
            foreach ($columns as $key => $value) {
                $value = preg_replace('/\D/', '', $value);
            }
            $data = array_combine($escapedHeader, $columns);
            $category = Category::firstOrNew(['name' => $data['name'], 'is_active' => true]);
            if ($data['parentcategory']) {
                $parent_category = Category::firstOrNew(['name' => $data['parentcategory'], 'is_active' => true]);
                $parent_id = $parent_category->id;
            } else
                $parent_id = null;

            $category->parent_id = $parent_id;
            $category->is_active = true;
            $category->save();
        }
        $this->cacheForget('category_list');
        return redirect('category')->with('message', 'Category imported successfully');
    }

    /* ---------------- HARD DELETE: CATEGORIES ---------------- */

    public function destroy($id)
    {
        if (!env('USER_VERIFIED'))
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');

        try {
            $this->hardDeleteCategory($id);
            $this->cacheForget('category_list');

            return redirect('category')->with('message', 'Category deleted permanently');
        } catch (\Exception $e) {
            return redirect()->back()->with('not_permitted', 'Cannot delete category: ' . $e->getMessage());
        }
    }

    public function deleteBySelection(Request $request)
    {
        $deleted = 0;
        $errors  = [];

        foreach ($request['categoryIdArray'] as $id) {
            try {
                $this->hardDeleteCategory($id);
                $deleted++;
            } catch (\Exception $e) {
                $errors[] = "Category #{$id}: " . $e->getMessage();
            }
        }

        $this->cacheForget('category_list');

        if (count($errors))
            return "Deleted {$deleted} category(ies). Errors: " . implode(' | ', $errors);

        return 'Category deleted permanently!';
    }

    /**
     * Permanently removes the category row, cleans up attached data
     * and deletes its image/icon files.
     */
    private function hardDeleteCategory($id)
    {
        DB::beginTransaction();
        try {
            $category = Category::findOrFail($id);

            // 1) Products
            if ($this->deleteProductsWithCategory) {
                Product::where('category_id', $id)->delete();
            } else {
                Product::where('category_id', $id)->update(['category_id' => null]);
            }

            // 2) Sub-categories -> promoted to top level
            Category::where('parent_id', $id)->update(['parent_id' => null]);

            // 3) Files
            $this->fileDelete('images/category/', $category->image);
            $this->fileDelete('images/category/icons/', $category->icon);

            // 4) Row
            $category->delete();
            // If your Category model uses the SoftDeletes trait use instead:
            // $category->forceDelete();

            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            throw new \Exception('it is still referenced by other records (foreign key constraint).');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function show($id)
    {
        if (Auth::user()->hasPermissionTo('category')) {
            $lims_category_data = Category::findOrFail($id);
            return view('backend.category.show', compact('lims_category_data'));
        }

        return redirect()->back()->with('not_permitted', 'Sorry! You are not allowed to access this module');
    }

    /* ==================================================================
     |  DEPARTMENTS
     ================================================================== */

    public function categoryDepartment()
    {
        return view('backend.category.department');
    }

    public function storeDepartment(Request $request)
    {
        $request->name = preg_replace('/\s+/', ' ', $request->name);

        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('category_departments')->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',
        ]);

        $department = new CategoryDepartment();
        $department->name = $request->name;
        $department->is_active = true;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $imageName = date("Ymdhis");

            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName .= '.' . $ext;
            } else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }

            $image->move('public/images/category', $imageName);
            Image::make('public/images/category/' . $imageName)->fit(300, 300)->save();

            $department->image = $imageName;
        }

        $department->save();

        return redirect()->back()->with('message', 'Department inserted successfully');
    }

    public function editDepartment($id)
    {
        $department = DB::table('category_departments')->where('id', $id)->first();
        return response()->json($department);
    }

    public function updateDepartment(Request $request)
    {
        $this->validate($request, [
            'name' => [
                'max:255',
                Rule::unique('category_departments')
                    ->ignore($request->department_id)
                    ->where(function ($query) {
                        return $query->where('is_active', 1);
                    }),
            ],
            'image' => 'nullable|image|mimes:jpg,jpeg,png,gif',
        ]);

        $department = CategoryDepartment::findOrFail($request->department_id);
        $department->name = preg_replace('/\s+/', ' ', $request->name);

        if ($request->hasFile('image')) {
            if ($department->image) {
                $this->fileDelete('images/category/', $department->image);
            }

            $image = $request->file('image');
            $ext = $image->getClientOriginalExtension();
            $imageName = date("Ymdhis");

            if (!config('database.connections.saleprosaas_landlord')) {
                $imageName .= '.' . $ext;
            } else {
                $imageName = $this->getTenantId() . '_' . $imageName . '.' . $ext;
            }

            $image->move('public/images/category', $imageName);
            Image::make('public/images/category/' . $imageName)->fit(100, 100)->save();

            $department->image = $imageName;
        }

        $department->save();

        return redirect()->back()->with('message', 'Department updated successfully');
    }

    public function departmentData(Request $request)
    {
        $columns = [
            0 => 'id',
            1 => 'name',
            2 => 'is_active',
            3 => 'options'
        ];

        $query = DB::table('category_departments')->where('is_active', true);

        $totalData     = $query->count();
        $totalFiltered = $totalData;

        $limit = $request->input('length') != -1 ? $request->input('length') : $totalData;
        $start = $request->input('start', 0);
        $order = $columns[$request->input('order.0.column', 1)] ?? 'name';
        $dir   = $request->input('order.0.dir', 'asc');

        if ($search = $request->input('search.value')) {
            $query->where('name', 'LIKE', "%{$search}%");
            $totalFiltered = $query->count();
        }

        $departments = $query->offset($start)->limit($limit)->orderBy($order, $dir)->get();

        $data = [];
        foreach ($departments as $key => $department) {
            $nestedData['key'] = $key + 1;
            $nestedData['name'] = '<img src="' .
                ($department->image ? url('images/category', $department->image) : url('images/zummXD2dvAtI.png')) .
                '" height="80" width="80"> ' . e($department->name);

            $nestedData['is_active'] = $department->is_active ? 'Active' : 'Inactive';

            $nestedData['options'] = '
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' . trans("file.action") . '
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu">
                        <li>
                            <button type="button" data-id="' . $department->id . '" class="open-EditCategoryDialog btn btn-link" data-toggle="modal" data-target="#editModal">
                                <i class="dripicons-document-edit"></i> ' . trans("file.edit") . '
                            </button>
                        </li>
                        <li class="divider"></li>' .
                \Form::open(["route" => ["category-department.destroy", $department->id], "method" => "DELETE"]) . '
                        <li>
                            <button type="submit" class="btn btn-link" onclick="return confirmHardDelete()">
                                <i class="dripicons-trash"></i> ' . trans("file.delete") . '
                            </button>
                        </li>' . \Form::close() . '
                    </ul>
                </div>';

            $data[] = $nestedData;
        }

        return response()->json([
            "draw"            => intval($request->input('draw')),
            "recordsTotal"    => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data"            => $data
        ]);
    }

    /* ---------------- HARD DELETE: DEPARTMENTS ---------------- */

    public function deleteDepartment($id)
    {
        if (!env('USER_VERIFIED'))
            return redirect()->back()->with('not_permitted', 'This feature is disabled for demo!');

        try {
            $this->hardDeleteDepartment($id);
            return redirect()->back()->with('message', 'Department deleted permanently');
        } catch (\Exception $e) {
            return redirect()->back()->with('not_permitted', 'Cannot delete department: ' . $e->getMessage());
        }
    }

    public function deleteDepartmentBySelection(Request $request)
    {
        $deleted = 0;
        $errors  = [];

        foreach ($request['departmentIdArray'] as $id) {
            try {
                $this->hardDeleteDepartment($id);
                $deleted++;
            } catch (\Exception $e) {
                $errors[] = "Department #{$id}: " . $e->getMessage();
            }
        }

        if (count($errors))
            return "Deleted {$deleted} department(s). Errors: " . implode(' | ', $errors);

        return 'Department deleted permanently!';
    }

    private function hardDeleteDepartment($id)
    {
        DB::beginTransaction();
        try {
            $department = CategoryDepartment::findOrFail($id);

            $attached = Category::where('department_id', $id)->count();

            if ($attached > 0 && !$this->detachCategoriesOnDepartmentDelete) {
                throw new \Exception("This department still has {$attached} category(ies) attached.");
            }

            // Detach categories so the FK constraint doesn't block the delete
            Category::where('department_id', $id)->update(['department_id' => null]);

            $this->fileDelete('images/category/', $department->image);

            $department->delete();
            // If your CategoryDepartment model uses SoftDeletes use instead:
            // $department->forceDelete();

            DB::commit();
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            throw new \Exception('it is still referenced by other records (foreign key constraint).');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
