<?php

namespace App\Http\Requests\Superadmin;

use App\Support\Hajj\HajjTahunOptions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportHajjParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
            'tahun_haji' => ['required', 'integer', 'min:'.HajjTahunOptions::MIN, 'max:'.HajjTahunOptions::MAX],
            'duplicate_action' => ['nullable', Rule::in(['skip', 'replace'])],
        ];
    }
}
