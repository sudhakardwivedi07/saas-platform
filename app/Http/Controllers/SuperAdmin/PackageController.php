<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helper\Reply;
use App\Http\Controllers\AccountBaseController;
use App\Models\SuperAdmin\Package;
use Illuminate\Http\Request;

class PackageController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth', 'super-admin']);
        $this->pageTitle = 'Packages';
    }

    public function index()
    {
        $this->packages = Package::withCount('companies')->orderBy('sort')->get();
        return view('super-admin.packages.index', $this->data);
    }

    public function create()
    {
        $this->pageTitle = 'New Package';
        return view('super-admin.packages.create', $this->data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255|unique:packages',
            'monthly_price'  => 'required|numeric|min:0',
            'annual_price'   => 'required|numeric|min:0',
            'max_users'      => 'nullable|integer|min:1',
            'max_storage_gb' => 'nullable|integer|min:1',
            'trial_days'     => 'required|integer|min:0',
        ]);

        // Normalise features: accept array or comma-separated string
        $features = $request->has('features')
            ? (array) $request->features
            : array_filter(explode(',', $request->input('features_raw', '')));

        $package = Package::create([
            ...$request->only(
                'name', 'description', 'monthly_price', 'annual_price',
                'stripe_monthly_price_id', 'stripe_annual_price_id',
                'max_users', 'max_storage_gb', 'trial_days', 'sort'
            ),
            'features'         => array_values(array_filter(array_map('trim', $features))),
            'is_active'        => $request->boolean('is_active', true),
            'is_trial_allowed' => $request->boolean('is_trial_allowed', true),
            'is_recommended'   => $request->boolean('is_recommended', false),
            'is_free'          => $request->boolean('is_free', false),
        ]);

        return Reply::successWithData('Package created.', ['packageId' => $package->id]);
    }

    public function show(int $id)
    {
        return redirect()->route('superadmin.packages.edit', $id);
    }

    public function edit(int $id)
    {
        $this->package   = Package::findOrFail($id);
        $this->pageTitle = 'Edit Package';
        return view('super-admin.packages.create', $this->data); // reuse same form view
    }

    public function update(Request $request, int $id)
    {
        $package = Package::findOrFail($id);

        $request->validate([
            'name'          => "required|string|max:255|unique:packages,name,{$id}",
            'monthly_price' => 'required|numeric|min:0',
            'annual_price'  => 'required|numeric|min:0',
        ]);

        $features = $request->has('features')
            ? (array) $request->features
            : array_filter(explode(',', $request->input('features_raw', '')));

        $package->update(array_merge(
            $request->only(
                'name', 'description', 'monthly_price', 'annual_price',
                'stripe_monthly_price_id', 'stripe_annual_price_id',
                'max_users', 'max_storage_gb', 'trial_days', 'sort'
            ),
            [
                'features'         => array_values(array_filter(array_map('trim', $features))),
                'is_active'        => $request->boolean('is_active', true),
                'is_trial_allowed' => $request->boolean('is_trial_allowed', true),
                'is_recommended'   => $request->boolean('is_recommended', false),
                'is_free'          => $request->boolean('is_free', false),
            ]
        ));

        return Reply::success('Package updated.');
    }

    public function destroy(int $id)
    {
        $package = Package::findOrFail($id);
        abort_403($package->companies()->exists());
        $package->delete();
        return Reply::success('Package deleted.');
    }
}
