<?php

namespace App\Http\Controllers\Panel;

use App\Entities\Donations\Models\DonationPerk;
use App\Http\WebController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DonationPerksController extends WebController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Http\Response
    {
        $perks = DonationPerk::with(['account', 'donation'])->orderBy('created_at', 'desc')->paginate(100);
        return view('front.pages.panel.donation-perks.index')->with(compact('perks'));
    }

    /**
     * Show the form for creating the specified resource.
     */
    public function create(Request $request): \Illuminate\Http\Response
    {
        return view('front.pages.panel.donation-perks.create');
    }

    /**
     * Add a specified resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\Response
    {
        // Checkbox input isn't sent to the server if not ticked by the user
        if (! $request->has('is_active')) {
            $request->request->add(['is_active' => false]);
        }
        if (! $request->has('is_lifetime_perks')) {
            $request->request->add(['is_lifetime_perks' => false]);
        }

        $validator = Validator::make($request->all(), [
            'donation_id' => 'numeric|exists:donations,donation_id',
            'account_id' => 'nullable|numeric|exists:accounts,account_id',
            'is_active' => 'boolean',
            'is_lifetime_perks' => 'boolean',
            'created_at' => 'required|date',
            'expires_at' => 'nullable|date',
        ]);

        $validator->after(function ($validator) use ($request): void {
            if ($request->get('is_lifetime_perks') === false && $request->get('expires_at') === null) {
                $validator->errors()->add('is_lifetime_perks', 'Expiry date is required if perks aren\'t lifetime');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DonationPerk::create([
            'donation_id' => $request->get('donation_id'),
            'account_id' => $request->get('account_id'),
            'is_active' => $request->get('is_active'),
            'is_lifetime_perks' => $request->get('is_lifetime_perks'),
            'expires_at' => $request->get('expires_at'),
            'created_at' => $request->get('created_at'),
            'updated_at' => $request->get('created_at'),
        ]);

        return redirect(route('front.panel.donation-perks.index'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DonationPerk $donationPerk): \Illuminate\Http\Response
    {
        return view('front.pages.panel.donation-perks.edit')->with(['perk' => $donationPerk]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DonationPerk $donationPerk): \Illuminate\Http\Response
    {
        // Checkbox input isn't sent to the server if not ticked by the user
        if (! $request->has('is_active')) {
            $request->request->add(['is_active' => false]);
        }
        if (! $request->has('is_lifetime_perks')) {
            $request->request->add(['is_lifetime_perks' => false]);
        }

        $validator = Validator::make($request->all(), [
            'donation_id' => 'numeric|exists:donations,donation_id',
            'account_id' => 'nullable|numeric|exists:accounts,account_id',
            'is_active' => 'boolean',
            'is_lifetime_perks' => 'boolean',
            'expires_at' => 'nullable|date',
            'created_at' => 'required|date',
        ]);

        $validator->after(function ($validator) use ($request): void {
            if ($request->get('is_lifetime_perks') === false && $request->get('expires_at') === null) {
                $validator->errors()->add('is_lifetime_perks', 'Expiry date is required if perks aren\'t lifetime');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $donationPerk->update($request->all());
        $donationPerk->save();

        return redirect(route('front.panel.donation-perks.index'));
    }

    /**
     * Delete the specified resource in storage.
     */
    public function destroy(Request $request, DonationPerk $donationPerk): \Illuminate\Http\Response
    {
        $donationPerk->delete();
        return redirect(route('front.panel.donation-perks.index'));
    }
}
