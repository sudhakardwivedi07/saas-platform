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

    private function validationRules(int $id = 0): array
    {
        $nameRule = $id
            ? "required|string|min:2|max:100|unique:packages,name,{$id}|regex:/^[a-zA-Z0-9 .&\\-_]+$/"
            : 'required|string|min:2|max:100|unique:packages|regex:/^[a-zA-Z0-9 .&\\-_]+$/';

        return [
            'name'                   => $nameRule,
            'monthly_price'          => 'required|numeric|min:0',
            'billing_cycle'          => 'required|in:monthly,annual,both',
            'max_users'              => 'required|integer|min:1',
            'max_insights_per_month' => 'required|integer|min:1',
            'description'            => 'required|string|min:10|max:500',
            'features'               => 'required|array|min:1',
            'features.*'             => 'required|string|max:500',
            'is_active'              => 'required|in:0,1',
            'annual_price'           => 'nullable|numeric|min:0',
            'max_storage_gb'         => 'nullable|integer|min:1',
            'trial_days'             => 'nullable|integer|min:0',
        ];
    }

    private function validationMessages(): array
    {
        return [
            'name.required'                   => 'Plan Name is required.',
            'name.min'                        => 'Plan Name must be at least 2 characters.',
            'name.max'                        => 'Plan Name cannot exceed 100 characters.',
            'name.unique'                     => 'This plan name already exists.',
            'name.regex'                      => 'Plan Name contains invalid characters.',
            'monthly_price.required'          => 'Price per month is required.',
            'monthly_price.numeric'           => 'Please enter a valid price.',
            'monthly_price.min'               => 'Price cannot be negative.',
            'billing_cycle.required'          => 'Please select a billing cycle.',
            'max_users.required'              => 'Max Clients is required.',
            'max_users.integer'               => 'Please enter a valid client limit.',
            'max_users.min'                   => 'Max Clients must be greater than 0.',
            'max_insights_per_month.required' => 'Insights per month is required.',
            'max_insights_per_month.integer'  => 'Please enter a valid insights limit.',
            'max_insights_per_month.min'      => 'Insights per month must be greater than 0.',
            'description.required'            => 'Description is required.',
            'description.min'                 => 'Description must be at least 10 characters.',
            'description.max'                 => 'Description exceeds the allowed limit.',
            'features.required'               => 'Please add at least one feature.',
            'features.min'                    => 'Please add at least one feature.',
            'features.*.max'                  => 'Please enter valid feature details.',
            'is_active.required'              => 'Please select a status.',
        ];
    }

    public function store(Request $request)
    {
        $request->validate($this->validationRules(), $this->validationMessages());

        // Normalise features: accept array or comma-separated string
        $features = $request->has('features')
            ? (array) $request->features
            : array_filter(explode(',', $request->input('features_raw', '')));

        $package = Package::create([
            ...$request->only(
                'name', 'description', 'monthly_price', 'annual_price', 'billing_cycle',
                'stripe_monthly_price_id', 'stripe_annual_price_id',
                'max_users', 'max_storage_gb', 'max_insights_per_month', 'trial_days', 'sort'
            ),
            'features'         => array_values(array_filter(array_map('trim', $features))),
            'is_active'        => $request->boolean('is_active', true),
            'is_trial_allowed' => $request->boolean('is_trial_allowed', true),
            'is_recommended'   => $request->boolean('is_recommended', false),
            'is_free'          => $request->boolean('is_free', false),
        ]);

        return Reply::successWithData('Plan created successfully.', ['packageId' => $package->id]);
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

        $request->validate($this->validationRules($id), $this->validationMessages());

        $features = $request->has('features')
            ? (array) $request->features
            : array_filter(explode(',', $request->input('features_raw', '')));

        $package->update(array_merge(
            $request->only(
                'name', 'description', 'monthly_price', 'annual_price', 'billing_cycle',
                'stripe_monthly_price_id', 'stripe_annual_price_id',
                'max_users', 'max_storage_gb', 'max_insights_per_month', 'trial_days', 'sort'
            ),
            [
                'features'         => array_values(array_filter(array_map('trim', $features))),
                'is_active'        => $request->boolean('is_active', true),
                'is_trial_allowed' => $request->boolean('is_trial_allowed', true),
                'is_recommended'   => $request->boolean('is_recommended', false),
                'is_free'          => $request->boolean('is_free', false),
            ]
        ));

        return Reply::success('Plan saved successfully.');
    }

    public function toggleStatus(int $id)
    {
        $package = Package::findOrFail($id);
        $package->update(['is_active' => !$package->is_active]);
        return Reply::success('Package status updated.');
    }

    public function destroy(int $id)
    {
        $package = Package::findOrFail($id);
        abort_403($package->companies()->exists());
        $package->delete();
        return Reply::success('Plan deleted successfully.');
    }
}
