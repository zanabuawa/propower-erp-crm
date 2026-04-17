<?php

namespace App\Livewire\Assets;

use App\Models\AssetLoan;
use App\Models\FixedAsset;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class AssetLoanForm extends Component
{
    public ?int $assetId = null;
    public string $loanedToName = '';
    public ?int $loanedToUserId = null;
    public string $loanDate = '';
    public string $expectedReturnDate = '';
    public string $conditionOnLoan = 'good';
    public string $purpose = '';
    public string $notes = '';
    public string $recipientType = 'user'; // user | external

    public function mount(?int $assetId = null): void
    {
        $this->loanDate = now()->toDateString();
        $this->assetId  = $assetId;
    }

    public function rules(): array
    {
        return [
            'assetId'            => 'required|exists:fixed_assets,id',
            'recipientType'      => 'required|in:user,external',
            'loanedToUserId'     => 'required_if:recipientType,user|nullable|exists:users,id',
            'loanedToName'       => 'required_if:recipientType,external|nullable|string|max:255',
            'loanDate'           => 'required|date',
            'expectedReturnDate' => 'nullable|date|after_or_equal:loanDate',
            'conditionOnLoan'    => 'required|in:good,fair,damaged',
            'purpose'            => 'nullable|string|max:500',
            'notes'              => 'nullable|string|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $asset = FixedAsset::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->findOrFail($this->assetId);

        AssetLoan::create([
            'company_id'          => auth()->user()->company_id,
            'fixed_asset_id'      => $this->assetId,
            'loaned_to_user_id'   => $this->recipientType === 'user' ? $this->loanedToUserId : null,
            'loaned_to_name'      => $this->recipientType === 'external' ? $this->loanedToName : null,
            'created_by'          => auth()->id(),
            'folio'               => AssetLoan::generateFolio(auth()->user()->company_id),
            'loan_date'           => $this->loanDate,
            'expected_return_date'=> $this->expectedReturnDate ?: null,
            'condition_on_loan'   => $this->conditionOnLoan,
            'purpose'             => $this->purpose ?: null,
            'notes'               => $this->notes ?: null,
            'status'              => 'active',
        ]);

        $asset->update(['status' => 'transferred']);

        session()->flash('success', 'Préstamo registrado correctamente.');
        $this->redirect(route('assets.loans.index'), navigate: true);
    }

    public function render()
    {
        $companyId = auth()->user()->company_id;

        $availableAssets = FixedAsset::where('company_id', $companyId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $users = User::where('company_id', $companyId)->orderBy('name')->get();

        return view('livewire.assets.asset-loan-form', compact('availableAssets', 'users'));
    }
}
