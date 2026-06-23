<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HrContract;

class ContractTemplatePrintController extends Controller
{
    public function __invoke(HrContract $contract)
    {
        $contract->load([
            'company',
            'template',
            'employee.department',
            'employee.position',
        ]);

        abort_unless($contract->company_id === auth()->user()->company_id, 403);

        return view('print.hr-contract-template', [
            'contract' => $contract,
            'company' => $contract->company,
            'employee' => $contract->employee,
        ]);
    }
}
