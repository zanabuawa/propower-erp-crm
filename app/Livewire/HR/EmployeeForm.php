<?php

namespace App\Livewire\HR;

use App\Livewire\Concerns\HasLocationFields;
use App\Models\Branch;
use App\Models\HrContract;
use App\Models\HrDepartment;
use App\Models\HrEmployee;
use App\Models\HrPosition;
use App\Models\User;
use App\Notifications\NewRoleCreatedNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Nnjeim\World\Models\City;
use Nnjeim\World\Models\Country;
use Nnjeim\World\Models\State;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
#[Title('Empleado')]
class EmployeeForm extends Component
{
    use WithFileUploads, HasLocationFields;

    public ?HrEmployee $employee = null;
    public ?int $prospect_id = null;

    // Identificación
    public string $employee_number = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $second_last_name = '';
    public string $curp = '';
    public string $rfc = '';
    public string $nss = '';
    public string $gender = '';

    // Contacto
    public string $email = '';
    public string $phone = '';
    public string $birth_date = '';
    public bool $notify_birthday = false;

    // Dirección
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = 'Mexico';
    public string $postal_code = '';
    public bool $showCountryCreator = false;
    public bool $showStateCreator = false;
    public bool $showCityCreator = false;
    public string $newCountryName = '';
    public string $newStateName = '';
    public string $newCityName = '';
    public string $locationFeedback = '';

    // Laboral
    public ?int $department_id = null;
    public ?int $position_id = null;
    public ?int $branch_id = null;
    public ?int $supervisor_id = null;
    public bool $is_external = false;
    public string $hire_date = '';
    public string $contract_type = 'indefinido';
    public string $salary = '';
    public string $salary_period = 'monthly';
    public string $work_shift = '';
    public string $status = 'active';

    // Pago
    public string $payment_method = 'transferencia';
    public string $bank = '';
    public string $bank_account = '';
    public string $clabe = '';

    // IMSS/INFONAVIT
    public string $imss_regime = '';
    public string $daily_salary_imss = '';
    public string $infonavit_credit = '';

    // Emergencia
    public string $emergency_contact_name = '';
    public string $emergency_contact_phone = '';
    public string $emergency_contact_relationship = '';

    public string $notes = '';
    public $photo_upload = null;

    public array $positions = [];

    public function mount(?HrEmployee $employee = null): void
    {
        $this->prospect_id = request()->query('prospect_id');

        if ($employee && $employee->exists) {
            $this->employee = $employee;
            $this->employee_number   = $employee->employee_number ?? '';
            $this->first_name        = $employee->first_name ?? '';
            $this->last_name         = $employee->last_name ?? '';
            $this->second_last_name  = $employee->second_last_name ?? '';
            $this->curp              = $employee->curp ?? '';
            $this->rfc               = $employee->rfc ?? '';
            $this->nss               = $employee->nss ?? '';
            $this->gender            = $employee->gender ?? '';
            $this->email             = $employee->email ?? '';
            $this->phone             = $employee->phone ?? '';
            $this->address           = $employee->address ?? '';
            $this->city              = $employee->city ?? '';
            $this->state             = $employee->state ?? '';
            $this->country           = $employee->country ?? 'Mexico';
            $this->postal_code       = $employee->postal_code ?? '';
            $this->department_id     = $employee->department_id;
            $this->position_id       = $employee->position_id;
            $this->branch_id         = $employee->branch_id;
            $this->supervisor_id     = $employee->supervisor_id;
            $this->is_external       = (bool) $employee->is_external;
            $this->contract_type     = $employee->contract_type ?? 'indefinido';
            $this->salary_period     = $employee->salary_period ?? 'monthly';
            $this->work_shift        = $employee->work_shift ?? '';
            $this->status            = $employee->status ?? 'active';
            $this->payment_method    = $employee->payment_method ?? 'transferencia';
            $this->bank              = $employee->bank ?? '';
            $this->bank_account      = $employee->bank_account ?? '';
            $this->clabe             = $employee->clabe ?? '';
            $this->imss_regime       = $employee->imss_regime ?? '';
            $this->infonavit_credit  = $employee->infonavit_credit ?? '';
            $this->emergency_contact_name         = $employee->emergency_contact_name ?? '';
            $this->emergency_contact_phone        = $employee->emergency_contact_phone ?? '';
            $this->emergency_contact_relationship = $employee->emergency_contact_relationship ?? '';
            $this->notes             = $employee->notes ?? '';
            $this->birth_date      = $employee->birth_date?->format('Y-m-d') ?? '';
            $this->notify_birthday = (bool) ($employee->notify_birthday ?? false);
            $this->hire_date       = $employee->hire_date?->format('Y-m-d') ?? '';
            $this->salary = (string) ($employee->salary ?? '');
            $this->daily_salary_imss = (string) ($employee->daily_salary_imss ?? '');
            $this->loadPositions();
        } elseif ($this->prospect_id) {
            $prospect = \App\Models\HrProspect::findOrFail($this->prospect_id);
            $this->first_name = $prospect->first_name;
            $this->last_name = $prospect->last_name;
            $this->second_last_name = $prospect->second_last_name ?? '';
            $this->email = $prospect->email ?? '';
            $this->phone = $prospect->phone ?? '';
            $this->position_id = $prospect->position_id;
            
            if ($this->position_id) {
                $pos = HrPosition::find($this->position_id);
                $this->department_id = $pos?->department_id;
                $this->loadPositions();
            }

            $this->hire_date = now()->format('Y-m-d');
        } else {
            $this->hire_date = now()->format('Y-m-d');
        }

        $this->initializeLocation();
    }

    public function updatedDepartmentId(): void
    {
        $this->position_id = null;
        $this->loadPositions();
    }

    private function loadPositions(): void
    {
        $this->positions = $this->department_id
            ? HrPosition::where('department_id', $this->department_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->toArray()
            : [];
    }

    public function openCountryCreator(): void
    {
        $this->newCountryName = '';
        $this->showCountryCreator = true;
        $this->showStateCreator = false;
        $this->showCityCreator = false;
        $this->locationFeedback = '';
    }

    public function openStateCreator(): void
    {
        $this->newStateName = '';
        $this->showCountryCreator = false;
        $this->showStateCreator = true;
        $this->showCityCreator = false;
        $this->locationFeedback = '';
    }

    public function openCityCreator(): void
    {
        $this->newCityName = '';
        $this->showCountryCreator = false;
        $this->showStateCreator = false;
        $this->showCityCreator = true;
        $this->locationFeedback = '';
    }

    public function saveCountry(): void
    {
        $this->validate([
            'newCountryName' => 'required|string|max:191',
        ]);

        $country = Country::where('name', trim($this->newCountryName))->first();

        if (! $country) {
            $iso2 = $this->makeCountryCode($this->newCountryName, 2);
            $iso3 = $this->makeCountryCode($this->newCountryName, 3);

            $country = Country::create([
                'iso2' => $iso2,
                'iso3' => $iso3,
                'name' => trim($this->newCountryName),
                'status' => 1,
                'phone_code' => '',
                'region' => '',
                'subregion' => '',
            ]);
        }

        $this->availableCountries = Country::orderBy('name')->pluck('name', 'id')->toArray();
        $this->updatedCountryId($country->id);
        $this->newCountryName = '';
        $this->showCountryCreator = false;
        $this->locationFeedback = 'País agregado correctamente.';
    }

    public function saveState(): void
    {
        $this->validate([
            'countryId' => 'required|integer|min:1',
            'newStateName' => 'required|string|max:191',
        ]);

        $country = Country::findOrFail($this->countryId);
        $state = State::firstOrCreate(
            [
                'country_id' => $country->id,
                'name' => trim($this->newStateName),
            ],
            [
                'country_code' => $country->iso2,
                'state_code' => Str::upper(Str::substr(Str::slug($this->newStateName, ''), 0, 5)),
            ]
        );

        $this->availableStates = State::where('country_id', $country->id)->orderBy('name')->pluck('name', 'id')->toArray();
        $this->updatedStateId($state->id);
        $this->newStateName = '';
        $this->showStateCreator = false;
        $this->locationFeedback = 'Estado agregado correctamente.';
    }

    public function saveCity(): void
    {
        $this->validate([
            'countryId' => 'required|integer|min:1',
            'stateId' => 'required|integer|min:1',
            'newCityName' => 'required|string|max:191',
        ]);

        $country = Country::findOrFail($this->countryId);
        $state = State::findOrFail($this->stateId);
        $city = City::firstOrCreate(
            [
                'country_id' => $country->id,
                'state_id' => $state->id,
                'name' => trim($this->newCityName),
            ],
            [
                'country_code' => $country->iso2,
                'state_code' => $state->state_code,
            ]
        );

        $this->availableCities = City::where('state_id', $state->id)->orderBy('name')->pluck('name', 'id')->toArray();
        $this->updatedCityId($city->id);
        $this->newCityName = '';
        $this->showCityCreator = false;
        $this->locationFeedback = 'Ciudad agregada correctamente.';
    }

    private function makeCountryCode(string $name, int $length): string
    {
        $base = Str::upper(Str::substr(preg_replace('/[^A-Za-z]/', '', Str::ascii($name)) ?: 'XX', 0, $length));
        return str_pad($base, $length, 'X');
    }

    public function save(): void
    {
        $this->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'hire_date'     => 'required|date',
            'contract_type' => 'required|in:' . implode(',', array_keys(HrEmployee::CONTRACT_TYPES)),
            'salary'        => 'required|numeric|min:0',
            'salary_period' => 'required|in:' . implode(',', array_keys(HrEmployee::SALARY_PERIODS)),
            'status'        => 'required|in:' . implode(',', array_keys(HrEmployee::STATUSES)),
            'gender'        => 'nullable|in:' . implode(',', array_keys(HrEmployee::GENDERS)),
            'birth_date'    => 'nullable|date|before:today',
            'rfc'           => 'nullable|string|max:13',
            'curp'          => 'nullable|string|max:18',
            'nss'           => 'nullable|string|max:11',
            'email'         => 'nullable|email|max:191',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'country'       => 'nullable|string|max:100',
            'clabe'         => 'nullable|string|size:18',
            'photo_upload'  => 'nullable|image|max:2048',
        ]);

        $data = [
            'company_id'                    => auth()->user()->company_id,
            'employee_number'               => $this->employee_number ?: null,
            'first_name'                    => $this->first_name,
            'last_name'                     => $this->last_name,
            'second_last_name'              => $this->second_last_name ?: null,
            'curp'                          => strtoupper($this->curp) ?: null,
            'rfc'                           => strtoupper($this->rfc) ?: null,
            'nss'                           => $this->nss ?: null,
            'gender'                        => $this->gender ?: null,
            'email'                         => $this->email ?: null,
            'phone'                         => $this->phone ?: null,
            'birth_date'                    => $this->birth_date ?: null,
            'notify_birthday'               => $this->notify_birthday,
            'address'                       => $this->address ?: null,
            'city'                          => $this->city ?: null,
            'state'                         => $this->state ?: null,
            'country'                       => $this->country ?: null,
            'postal_code'                   => $this->postal_code ?: null,
            'department_id'                 => $this->department_id,
            'position_id'                   => $this->position_id,
            'branch_id'                     => $this->branch_id,
            'supervisor_id'                 => $this->supervisor_id,
            'is_external'                   => $this->is_external,
            'hire_date'                     => $this->hire_date,
            'contract_type'                 => $this->contract_type,
            'salary'                        => $this->salary,
            'salary_period'                 => $this->salary_period,
            'work_shift'                    => $this->work_shift ?: null,
            'status'                        => $this->status,
            'payment_method'                => $this->payment_method,
            'bank'                          => $this->bank ?: null,
            'bank_account'                  => $this->bank_account ?: null,
            'clabe'                         => $this->clabe ?: null,
            'imss_regime'                   => $this->imss_regime ?: null,
            'daily_salary_imss'             => $this->daily_salary_imss ?: null,
            'infonavit_credit'              => $this->infonavit_credit ?: null,
            'emergency_contact_name'        => $this->emergency_contact_name ?: null,
            'emergency_contact_phone'       => $this->emergency_contact_phone ?: null,
            'emergency_contact_relationship'=> $this->emergency_contact_relationship ?: null,
            'notes'                         => $this->notes ?: null,
        ];

        if ($this->photo_upload) {
            $data['photo'] = $this->photo_upload->store('hr/photos', 'public');
        }

        if ($this->employee && $this->employee->exists) {
            $this->employee->update($data);
            $this->syncLinkedUserProfile($this->employee->refresh());
            session()->flash('success', 'Empleado actualizado correctamente.');
        } else {
            $employee = HrEmployee::create($data);

            if ($this->prospect_id) {
                $prospect = \App\Models\HrProspect::find($this->prospect_id);
                if ($prospect) {
                    $prospect->update(['employee_id' => $employee->id]);
                    \App\Models\HrEvaluationProcess::where('hr_prospect_id', $prospect->id)
                        ->whereNull('hr_employee_id')
                        ->update(['hr_employee_id' => $employee->id]);
                    $prospect->changeStatus('contratado', 'Candidato contratado. Registro de empleado creado.');
                    $this->syncJobOpeningAfterHire($prospect);
                }
            }

            // Auto-create initial contract
            HrContract::create([
                'company_id'          => $employee->company_id,
                'employee_id'         => $employee->id,
                'type'                => $this->contract_type ?? 'indefinido',
                'start_date'          => $this->hire_date,
                'salary'              => $this->salary,
                'salary_period'       => $this->salary_period,
                'work_shift'          => $this->work_shift ?: null,
                'work_hours_per_week' => 48,
                'benefits'            => [
                    'aguinaldo_days'       => 15,
                    'vacation_days'        => 6,
                    'vacation_premium_pct' => 25,
                ],
                'status'     => 'active',
                'created_by' => auth()->id(),
            ]);

            $this->createUserForEmployee($employee);
            session()->flash('success', 'Empleado registrado correctamente.');
            $this->redirect(route('hr.employees.show', $employee), navigate: true);
            return;
        }

        $this->redirect(route('hr.employees.show', $this->employee), navigate: true);
    }

    private function createUserForEmployee(HrEmployee $employee): void
    {
        if (! $employee->email) {
            return;
        }

        // Determinar nombre del rol según el puesto del empleado
        $roleName = null;
        if ($employee->position_id) {
            $position = HrPosition::find($employee->position_id);
            $roleName = $position?->name;
        }

        // Crear o reusar usuario con ese email
        $user = User::firstOrCreate(
            ['email' => $employee->email],
            [
                'name'       => $employee->full_name,
                'birth_date' => $employee->birth_date,
                'gender'     => $employee->gender,
                'password'   => Hash::make(Str::random(16)),
                'company_id' => $employee->company_id,
                'branch_id'  => $employee->branch_id,
                'is_active'  => true,
            ]
        );

        // Vincular empleado ↔ usuario
        $employee->update(['user_id' => $user->id]);
        $this->syncLinkedUserProfile($employee->refresh());

        if (! $roleName) {
            return;
        }

        // Crear rol si no existe y notificar admins
        $roleCreated = false;
        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            Role::create(['name' => $roleName]);
            $roleCreated = true;
        }

        $user->assignRole($roleName);

        if ($roleCreated) {
            $admins = User::role(['admin', 'super-admin'])->get();
            foreach ($admins as $admin) {
                $admin->notify(new NewRoleCreatedNotification($roleName, $employee->full_name));
            }
        }
    }

    private function syncLinkedUserProfile(HrEmployee $employee): void
    {
        if (! $employee->user_id) {
            return;
        }

        $employee->user?->forceFill([
            'name' => $employee->full_name,
            'birth_date' => $employee->birth_date,
            'gender' => $employee->gender,
        ])->save();
    }

    private function syncJobOpeningAfterHire(\App\Models\HrProspect $prospect): void
    {
        if (! $prospect->job_opening_id) {
            return;
        }

        $opening = \App\Models\HrJobOpening::withCount([
            'prospects as hired_prospects_count' => fn ($q) => $q->where('status', 'contratado'),
        ])->find($prospect->job_opening_id);

        if ($opening && $opening->status === 'open' && $opening->hired_prospects_count >= $opening->quantity) {
            $opening->update(['status' => 'closed']);
        }
    }

    public function render()
    {
        $departments = HrDepartment::where('is_active', true)->orderBy('name')->get();
        $branches    = Branch::where('company_id', auth()->user()->company_id)->orderBy('name')->get();
        $supervisors = HrEmployee::where('company_id', auth()->user()->company_id)
            ->where('status', 'active')
            ->when($this->employee, fn($q) => $q->where('id', '!=', $this->employee->id))
            ->orderBy('first_name')
            ->get();

        return view('livewire.hr.employee-form', compact('departments', 'branches', 'supervisors'));
    }
}
