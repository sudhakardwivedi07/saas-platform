<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\ClientPackage;
use Illuminate\Http\Request;

class ClientPackageController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware(['auth']);
        $this->pageTitle = 'My Plans';
    }

    // ── List ─────────────────────────────────────────────────────────────────

    public function index()
    {
        abort_unless(auth()->user()->can('view_plan'), 403);

        $this->plans = ClientPackage::forCompany(auth()->user()->company_id)
            ->orderBy('sort')
            ->orderBy('monthly_price')
            ->get();

        return view('plans.index', $this->data);
    }

    // ── Create form ──────────────────────────────────────────────────────────

    public function create()
    {
        abort_unless(auth()->user()->can('add_plan'), 403);
        return view('plans.create', $this->data);
    }

    // ── Store ────────────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('add_plan'), 403);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'monthly_price'    => 'required|numeric|min:0',
            'annual_price'     => 'nullable|numeric|min:0',
            'max_users'        => 'nullable|integer|min:1',
            'max_storage_gb'   => 'nullable|integer|min:1',
            'is_free'          => 'boolean',
            'is_recommended'   => 'boolean',
            'is_trial_allowed' => 'boolean',
            'trial_days'       => 'nullable|integer|min:1|max:365',
            'is_active'        => 'boolean',
            'sort'             => 'nullable|integer|min:0',
            'stripe_monthly_price_id' => 'nullable|string|max:255',
            'stripe_annual_price_id'  => 'nullable|string|max:255',
            'features'         => 'nullable|string',
        ]);

        // Parse comma-separated features into array
        $features = null;
        if (!empty($data['features'])) {
            $features = array_values(array_filter(
                array_map('trim', explode("\n", $data['features']))
            ));
        }

        ClientPackage::create([
            'company_id'              => auth()->user()->company_id,
            'created_by'              => auth()->id(),
            'name'                    => $data['name'],
            'description'             => $data['description'] ?? null,
            'monthly_price'           => $data['monthly_price'],
            'annual_price'            => $data['annual_price'] ?? null,
            'max_users'               => $data['max_users'] ?? null,
            'max_storage_gb'          => $data['max_storage_gb'] ?? null,
            'features'                => $features,
            'is_free'                 => (bool) ($data['is_free'] ?? false),
            'is_recommended'          => (bool) ($data['is_recommended'] ?? false),
            'is_trial_allowed'        => (bool) ($data['is_trial_allowed'] ?? false),
            'trial_days'              => $data['trial_days'] ?? 0,
            'is_active'               => (bool) ($data['is_active'] ?? true),
            'sort'                    => $data['sort'] ?? 0,
            'stripe_monthly_price_id' => $data['stripe_monthly_price_id'] ?? null,
            'stripe_annual_price_id'  => $data['stripe_annual_price_id'] ?? null,
        ]);

        return redirect()->route('plans.index')
            ->with('success', "Plan '{$data['name']}' created successfully.");
    }

    // ── Edit form ────────────────────────────────────────────────────────────

    public function edit(int $id)
    {
        abort_unless(auth()->user()->can('edit_plan'), 403);

        $this->plan = ClientPackage::forCompany(auth()->user()->company_id)
            ->findOrFail($id);

        return view('plans.edit', $this->data);
    }

    // ── Update ───────────────────────────────────────────────────────────────

    public function update(Request $request, int $id)
    {
        abort_unless(auth()->user()->can('edit_plan'), 403);

        $plan = ClientPackage::forCompany(auth()->user()->company_id)->findOrFail($id);

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'monthly_price'    => 'required|numeric|min:0',
            'annual_price'     => 'nullable|numeric|min:0',
            'max_users'        => 'nullable|integer|min:1',
            'max_storage_gb'   => 'nullable|integer|min:1',
            'is_free'          => 'boolean',
            'is_recommended'   => 'boolean',
            'is_trial_allowed' => 'boolean',
            'trial_days'       => 'nullable|integer|min:1|max:365',
            'is_active'        => 'boolean',
            'sort'             => 'nullable|integer|min:0',
            'stripe_monthly_price_id' => 'nullable|string|max:255',
            'stripe_annual_price_id'  => 'nullable|string|max:255',
            'features'         => 'nullable|string',
        ]);

        $features = null;
        if (!empty($data['features'])) {
            $features = array_values(array_filter(
                array_map('trim', explode("\n", $data['features']))
            ));
        }

        $plan->update([
            'name'                    => $data['name'],
            'description'             => $data['description'] ?? null,
            'monthly_price'           => $data['monthly_price'],
            'annual_price'            => $data['annual_price'] ?? null,
            'max_users'               => $data['max_users'] ?? null,
            'max_storage_gb'          => $data['max_storage_gb'] ?? null,
            'features'                => $features,
            'is_free'                 => (bool) ($data['is_free'] ?? false),
            'is_recommended'          => (bool) ($data['is_recommended'] ?? false),
            'is_trial_allowed'        => (bool) ($data['is_trial_allowed'] ?? false),
            'trial_days'              => $data['trial_days'] ?? 0,
            'is_active'               => (bool) ($data['is_active'] ?? true),
            'sort'                    => $data['sort'] ?? 0,
            'stripe_monthly_price_id' => $data['stripe_monthly_price_id'] ?? null,
            'stripe_annual_price_id'  => $data['stripe_annual_price_id'] ?? null,
        ]);

        return redirect()->route('plans.index')
            ->with('success', "Plan '{$plan->name}' updated successfully.");
    }

    // ── Delete ───────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        abort_unless(auth()->user()->can('delete_plan'), 403);

        $plan = ClientPackage::forCompany(auth()->user()->company_id)->findOrFail($id);
        $name = $plan->name;
        $plan->delete();

        return Reply::success("Plan '{$name}' deleted.");
    }

    // ── Toggle active status (AJAX) ───────────────────────────────────────────

    public function toggleStatus(int $id)
    {
        abort_unless(auth()->user()->can('edit_plan'), 403);

        $plan = ClientPackage::forCompany(auth()->user()->company_id)->findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);

        return Reply::success('Status updated.');
    }
}
